<?php

class aupdateluceneTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Max pages to update on this pass', false),
      // add your own options here
    ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'update-search-index';
    $this->briefDescription = 'update search indexes for recently modified pages';
    $this->detailedDescription = <<<EOF
The [a:update-lucene|INFO] task updates the Lucene search indexes for
recently modified pages in the CMS. You should call it from cron or another
scheduled task manager on a regular basis (for instance, every
five minutes).

Call it like this:

  [php /path/to/your/project/symfony a:update-lucene|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();
    // PDO connection not so useful, get the doctrine one
    $conn = Doctrine_Manager::connection();
    $q = Doctrine::getTable('aLuceneUpdate')->createQuery('u');
    if ($options['limit'] !== false)
    {
      $q->limit($options['limit'] + 0);
    }
    $updates = $q->execute();
    $i = 0;
    foreach ($updates as $update)
    {
      $i++;
      $page = aPageTable::retrieveByIdWithSlots($update->page_id, $update->culture);
      // Careful, pages die
      if ($page)
      {
        $page->updateLuceneIndex(); 
      }
      $update->delete();
    }
  }
}
