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
    foreach ($indexes as $index)
    {
      $table = Doctrine::getTable($index);
      Doctrine::getTable($index)->rebuildLuceneIndex();
      $this->logSection('toolkit', sprintf('Index for "%s" rebuilt', $index));
    }
  }
}
