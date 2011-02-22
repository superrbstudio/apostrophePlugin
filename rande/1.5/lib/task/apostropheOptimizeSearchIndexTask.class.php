<?php

class apostropheOptimizeSearchIndexTask extends sfBaseTask
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
    $this->name             = 'optimize-search-index';
    $this->briefDescription = 'optimize search indexes';
    $this->detailedDescription = <<<EOF
The [apostrophe:optimize-search-index|INFO] task optimizes the Lucene search indexes in the CMS. When not optimized, the search engine gradually becomes slower as edits accumulate. So this task should be called on a regular basis at a convenient time (for instance, 3am daily, or 3am Sunday).

Call it like this:

  [php /path/to/your/project/symfony apostrophe:optimize-search-index|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();
    // PDO connection not so useful, get the doctrine one
    $conn = Doctrine_Manager::connection();

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
      echo("Optimizing $index\n");
      aZendSearch::optimizeLuceneIndex($table);
    }
    echo("Success!\n");
  }
}
