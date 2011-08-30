<?php
/**
 * @package    apostrophePlugin
 * @subpackage    task
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class toolkitRebuildIndex extends sfBaseTask
{

  /**
   * DOCUMENT ME
   */
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
      new sfCommandOption('verbose', null, sfCommandOption::PARAMETER_NONE, 'Output more info during the rebuild', null),
      
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

  /**
   * DOCUMENT ME
   * @param mixed $arguments
   * @param mixed $options
   */
  protected function execute($arguments = array(), $options = array())
  {
    // We've come a long way in reducing memory usage here, but it's still an expensive job
    
    ini_set('memory_limit', '256M');
    
    $context = sfContext::createInstance($this->configuration);
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
        // We're about to request updates of all page/culture combinations. Don't
        // add that to an existing workload which could result in a huge pileup of
        // repeat requests if someone starts interrupting this task and trying again, etc.
        $this->query('DELETE FROM a_lucene_update');
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
          if ($options['verbose'])
          {
            $this->logSection('toolkit', "$count pages remain to be indexed, starting another update pass...");
          }
          $this->update('aPage', $options);
        }
      }
      else
      {
        if ($table->hasField('lucene_dirty'))
        {
          aZendSearch::purgeLuceneIndex($table);
          $tableSqlName = $table->getTableName();
          // Use Doctrine update and count queries to get the performance while
          // retaining compatibility with aggregate inheritance "tables" like
          // dukeTubesArticle and dukeTubesEvent. With raw SQL we get confused
          // because we run out of objects that Doctrine recognizes as being of the
          // relevant type but we marked everything in the table as "dirty"
          Doctrine_Query::create()->update($index)->set('lucene_dirty', true)->execute();
          while (true)
          {
            $count = $table->createQuery('q')->where('q.lucene_dirty IS TRUE')->count();
            if ($count == 0)
            {
              break;
            }
            if ($options['verbose'])
            {
              $this->logSection('toolkit', "$count $index objects remain to be indexed, starting another update pass...");
            }
            $this->update($index, $options);
          }
        }
        else
        {
          // We don't have a deferred update feature for other tables,
          // so we'll have to get them done in the memory available
          $table->rebuildLuceneIndex();
        }
      }
      if ($options['verbose'])
      {
        $this->logSection('toolkit', sprintf('Index for "%s" rebuilt', $index));
      }
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $index
   * @param mixed $options
   */
  protected function update($index, $options)
  {
    if ($options['verbose'])
    {
      $this->logSection('toolkit', "Executing an update pass on $index...");
    }
    // task->run is really nice, but doesn't help us with the PHP 5.2 + Doctrine out of memory issue
    
    $args = $_SERVER['argv'];
    $taskIndex = array_search('apostrophe:rebuild-search-index', $args);
    if ($taskIndex === false)
    {
      throw new sfException("Can't find apostrophe:rebuild-search-index in the command line in order to replace it. Giving up.");
    }
    $args[$taskIndex] = 'apostrophe:update-search-index';
    $args[] = '--limit=100';
    $args[] = escapeshellarg("--table=$index");
    aProcesses::systemArray($args);
    
    // $task = new aupdateluceneTask($this->dispatcher, $this->formatter);
    // $task->run(array(), array('env' => $options['env']));
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  protected function getPDO()
  {
    $connection = Doctrine_Manager::connection();
    $pdo = $connection->getDbh();
    return $pdo;
  }

  /**
   * DOCUMENT ME
   * @param mixed $s
   * @param mixed $params
   * @return mixed
   */
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
