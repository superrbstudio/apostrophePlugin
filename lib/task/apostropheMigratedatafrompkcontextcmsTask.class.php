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
    foreach ($tables as $old => $new)
    {
      $conn->query("RENAME TABLE $old TO $new");
    }
    
    echo("Rebuilding search indexes\n");
    system("./symfony apostrophe:rebuild-search-indexes", $result);
    if ($result != 0)
    {
      die("Unable to rebuild search indexes\n");
    }
    echo("If you have other folders in data/pk_writable you may want to move them to
data/a_writable. Due to interactions with svn this is not automatic. In 
our projects we use svn ignore rules to protect the contents of the
data/*_writable folder.n");
  }
}
