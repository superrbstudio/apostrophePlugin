<?php
/**
 * @package    apostrophePlugin
 * @subpackage    task
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aupdateluceneTask extends sfBaseTask
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
      // We need a default here so app.yml works
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Max pages to update on this pass', false),
      new sfCommandOption('table', null, sfCommandOption::PARAMETER_REQUIRED, 'Table to update', 'aPage'),
      new sfCommandOption('verbose', null, sfCommandOption::PARAMETER_NONE, 'Output more info during the update (ignored)', null),
      
      // add your own options here
    ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'update-search-index';
    $this->briefDescription = 'update search indexes for recently modified objects';
    $this->detailedDescription = <<<EOF
The [apostrophe:update-lucene|INFO] task updates the Lucene search indexes for
recently modified pages in the CMS. You should call it from cron or another
scheduled task manager on a regular basis (for instance, every
five minutes).

Call it like this:

  [php /path/to/your/project/symfony apostrophe:update-lucene|INFO]

The task is also called for other object types like media items as an internal
part of the rebuild-search-index task. The --table=aMediaItem option is used
to trigger this. You don't need to schedule cron jobs for that as media items are
normally updated at save time (as are pages, by default, but that can be disabled).
EOF;
  }

  /**
   * DOCUMENT ME
   * @param mixed $arguments
   * @param mixed $options
   */
  protected function execute($arguments = array(), $options = array())
  {
    $context = sfContext::createInstance($this->configuration);
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();
    // PDO connection not so useful, get the doctrine one
    $conn = Doctrine_Manager::connection();
    
    if ($options['table'] === 'aPage')
    {
      $q = Doctrine::getTable('aLuceneUpdate')->createQuery('u');
    }
    else
    {
      $q = Doctrine::getTable($options['table'])->createQuery('o')->where('o.lucene_dirty IS TRUE');
    }
    if ($options['limit'] !== false)
    {
      $q->limit($options['limit'] + 0);
    }
    $updates = $q->execute();
    $i = 0;
    foreach ($updates as $update)
    {
      $i++;
      if ($options['table'] === 'aPage')
      {
        $page = aPageTable::retrieveByIdWithSlots($update->page_id, $update->culture);
        // Careful, pages die
        if ($page)
        {
          $page->updateLuceneIndex();
        }
        $update->delete();
      }
      else
      {
        // The actual object
        $update->updateLuceneIndex();
        $update->lucene_dirty = false;
        $update->save();
      }
    }
  }
}
