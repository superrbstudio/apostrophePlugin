<?php

class apostropheMigratedatafrompkcontextcmsTask extends sfBaseTask
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
      // add your own options here
    ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'migrate-data-from-pkcontextcms';
    $this->briefDescription = 'migrate pkContextCMS data to Apostrophe';
    $this->detailedDescription = <<<EOF
The [apostrophe:migrate-data-from-pkcontextcms|INFO] task does things.
Call it with:

  [php symfony apostrophe:migrate-data-from-pkcontextcms|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    // We need to use PDO here because Doctrine is more than a little confused when
    // we've renamed the codebase but not the tables
    
    echo("Renaming slots in database\n");
    $conn = Doctrine_Manager::connection()->getDbh();
    $conn->query('UPDATE pk_context_cms_slot SET type = REPLACE(type, "pkContextCMS", "a")');
    
    echo("Renaming tables in database\n");

    $tables = array(
      'pk_context_cms_slot' => 'a_slot',
      'pk_context_cms_area_version_slot' => 'a_area_version_slot',
      'pk_context_cms_area_version' => 'a_area_version',
      'pk_context_cms_area' => 'a_area',
      'pk_context_cms_page' => 'a_page',
      'pk_blog_category' => 'a_blog_category',
      'pk_blog_event_version' => 'a_blog_event_version',
      'pk_blog_item' => 'a_blog_item',
      'pk_blog_item_version' => 'a_blog_item_version',
      'pk_blog_post_version' => 'a_blog_post_version',
      'pk_context_cms_access' => 'a_access',
      'pk_context_cms_lucene_update' => 'a_lucene_update',
      'pk_media_item' => 'a_media_item'
    );

    foreach ($tables as $old => $new)
    {
      $conn->query("RENAME TABLE $old TO $new");
    }
    
    echo("Rebuilding search indexes\n");
    system("./symfony apostrophe:rebuild-search-index", $result);
    if ($result != 0)
    {
      die("Unable to rebuild search indexes\n");
    }
    echo("If you have other folders in data/pk_writable you may want to move them to
data/a_writable. Due to interactions with svn this is not automatic. In 
our projects we use svn ignore rules to protect the contents of the
data/*_writable folder. This is primarily an issue on servers other than your
development machine, where you run this task separately. On your development
machine pk_writable is renamed to a_writable automatically.\n");
  }
}
