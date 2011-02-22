<?php

class toolkitRebuildIndex extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('table', null, sfCommandOption::PARAMETER_OPTIONAL, 'The table name', null),
      // add your own options here
    ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'rebuild-search-index';
    $this->briefDescription = 'Rebuild all Lucene search indexes defined in app.yml';
    $this->detailedDescription = <<<EOF
The [apostrophe:rebuild-search-index|INFO] task rebuilds the search indexes defined in app.yml.
Call it with:

  [php symfony apostrophe:rebuild-search-index|INFO]
  
You can optionally specify a table parameter (--table=aPage) to rebuild just that table.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // Memory usage is a bit high here because we look at every page, and the Rackspace Cloud
    // environment has a very low default memory limit for their ersatz "cron jobs."
    // TODO: prioritize a low-memory solution for rebuild-search-index, which will be
    // necessary for large sites anyway
    
    ini_set('memory_limit', '256M');
    
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();
    // Initialize the context, which loading use of helpers, notably url_for
    // First set config vars so that reasonable siteless-but-rooted URLs can be generated
    // TODO: think about ways to make this work for people who like frontend_dev.php etc., although
    // we're doing rather well with an index.php that suits each environment
    sfConfig::set('sf_no_script_name', true); 
    $_SERVER['PHP_SELF'] = '';
    $_SERVER['SCRIPT_NAME'] = '';
     
    $context = sfContext::createInstance($this->configuration);
    if (isset($options['table']))
    {
      $indexes = array($options['table']);
    }
    else
    {
      $indexes = sfConfig::get('app_aToolkit_indexes', array());
    }
    $count = 0;
    foreach ($indexes as $index)
    {
      $table = Doctrine::getTable($index);
      if ($index === 'aPage')
      {
        aZendSearch::purgeLuceneIndex($table);
        $pages = Doctrine::getTable('aPage')->createQuery('p')->innerJoin('p.Areas a')->execute(array(), Doctrine::HYDRATE_ARRAY);
        foreach ($pages as $page)
        {
          $cultures = array();
          foreach ($page['Areas'] as $area)
          {
            $cultures[$area['culture']] = true; 
          }
          $cultures = array_keys($cultures);
          foreach ($cultures as $culture)
          {
            $this->query('INSERT INTO a_lucene_update (page_id, culture) VALUES (:page_id, :culture)', array('page_id' => $page['id'], 'culture' => $culture));
          }
        }
        while (true)
        {
          $result = $this->query('SELECT COUNT(id) AS total FROM a_lucene_update');
          $count = $result[0]['total'];
          if ($count == 0)
          {
            break;
          }
          $this->logSection('toolkit', "$count pages remain to be indexed, starting another update pass...");
          $this->update();
        }
      }
      else
      {
        // We don't have a deferred update feature for other tables,
        // so we'll have to get them done in the memory available
        $table->rebuildLuceneIndex();
      }
      $this->logSection('toolkit', sprintf('Index for "%s" rebuilt', $index));
    }
  }
  
  protected function update()
  {
    $this->logSection('toolkit', "Executing an update pass on aPage table...");
    
    // task->run is really nice, but doesn't help us with the PHP 5.2 + Doctrine out of memory issue
    
    $args = $_SERVER['argv'];
    $taskIndex = array_search('apostrophe:rebuild-search-index', $args);
    if ($taskIndex === false)
    {
      throw new sfException("Can't find apostrophe:rebuild-search-index in the command line in order to replace it. Giving up.");
    }
    $args[$taskIndex] = 'apostrophe:update-search-index';
    $args[] = '--limit=100';
    aProcesses::systemArray($args);
    
    // $task = new aupdateluceneTask($this->dispatcher, $this->formatter);
    // $task->run(array(), array('env' => $options['env']));
  }
  
  protected function getPDO()
  {
    $connection = Doctrine_Manager::connection();
    $pdo = $connection->getDbh();
    return $pdo;
  }
  
  protected function query($s, $params = array())
  {
    $pdo = $this->getPDO();
    $nparams = array();
    // I like to use this with toArray() while not always setting everything,
    // so I tolerate extra stuff. Also I don't like having to put a : in front 
    // of everything
    foreach ($params as $key => $value)
    {
      if (strpos($s, ":$key") !== false)
      {
        $nparams[":$key"] = $value;
      }
    }
    $statement = $pdo->prepare($s);
    try
    {
      $statement->execute($nparams);
    }
    catch (Exception $e)
    {
      echo($e);
      echo("Statement: $s\n");
      echo("Parameters:\n");
      var_dump($params);
      exit(1);
    }
    $result = true;
    try
    {
      $result = $statement->fetchAll();
    } catch (Exception $e)
    {
      // Oh no, we tried to fetchAll on a DELETE statement, everybody panic!
      // Seriously PDO, you need to relax
    }
    return $result;
  }
}
