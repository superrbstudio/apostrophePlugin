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
The [apostrophe:migrate-data-from-pkcontextcms|INFO] task migrates CMS-related tables and slots
to the new Apostrophe naming convention. It also rebuilds the search index since the naming
convention used inside the Zend indexes has also changed.

Call it with:

  [php symfony apostrophe:migrate-data-from-pkcontextcms --env=staging|INFO]
  
Be certain to specify the right environment for the system you are running it on.

Note: on ONE development machine, you will run the migrate-from-pkcontextcms task. That
task will run this task as a subtask. Verify success and commit the project, then sync
or svn update your code to other servers and development machines. On those machines,
run this task (migrate-data-from-pkcontextcms) directly to migrate just your data. There 
is no need to migrate the source code more than once!
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    // We need to use PDO here because Doctrine is more than a little confused when
    // we've renamed the codebase but not the tables
    
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

  $conn = Doctrine_Manager::connection()->getDbh();

    foreach ($tables as $old => $new)
    {
      try
      {
	echo("before\n");
        $conn->query("RENAME TABLE $old TO $new");
echo("after\n");
      } catch (Exception $e)
      {
        echo("Rename of $old failed, that's normal if you don't use that table's plugin or you have run this script before.\n");
      }
    }
    echo("Renaming slots and engines in database\n");

    try
    {
      $conn->query('UPDATE a_slot SET type = REPLACE(type, "pkContextCMS", "a")');
    } catch (Exception $e)
    {
      echo("Warning: unable to reset slot types in a_slot table\n");
    }

    try
    {
      $conn->query('UPDATE a_page SET engine = REPLACE(engine, "pk", "a")');
    } catch (Exception $e)
    {
      echo("Warning: unable to rename engines in a_page table\n");
    }

    try
    {
      $conn->query('ALTER TABLE a_slot ADD variant varchar(100)');
    } catch (Exception $e)
    {
      echo("Warning: unable to add variant column to a_slot table\n");
    }
    
    echo("Rebuilding search index\n");
		$cmd = "./symfony apostrophe:rebuild-search-index --env=" . $options['env'];
    system($cmd, $result);
    if ($result != 0)
    {
      die("Unable to rebuild search indexes\n");
    }
    echo("If you have folders in data/pk_writable other than tmp and the zend search indexes 
you may want to move them to data/a_writable. Due to interactions with svn this is not
automatic. In our projects we use svn ignore rules to protect the contents of the
data/*_writable folder. This is primarily an issue on servers other than your
development machine, where you run this task separately. On your development
machine pk_writable is renamed to a_writable automatically.\n");
  }
}
