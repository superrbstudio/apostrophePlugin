<?php

class apostropheMigrateTask extends sfBaseTask
{
  protected function configure()
  {

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('force', false, sfCommandOption::PARAMETER_NONE, 'No prompts'),
      // add your own options here
    ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'migrate';
    $this->briefDescription = 'Update database to match current version of Apostrophe';
    $this->detailedDescription = <<<EOF
The [apostrophe:migrate|INFO] task updates your MySQL database to work with the current version of Apostrophe.
If you are not using MySQL you'll need to manually update your schema. Note that, unless absolutely necessary, we will
not make changes requiring migrations in patch releases (that is, 1.0.12 does not require a migration from 1.0.11).
You should expect to need this task only when upgrading to a new minor or major version number (1.1 from 1.0.x,
or 2.0 from 1.x, etc).

  [php symfony apostrophe:migrate|INFO]
EOF;
  }

  protected $migrate;
  
  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    echo("
Apostrophe Database Migration Task
  
This task will make any necessary database schema changes to bring your 
MySQL database up to date with the current release of Apostrophe and any additional
Apostrophe plugins that you have installed. For other databases see the source code 
or run './symfony doctrine:build-sql' to obtain the SQL commands you may need.
  
BACK UP YOUR DATABASE BEFORE YOU RUN THIS TASK. It works fine in our tests, 
but why take chances with your data?

");
    if (!$options['force'])
    {
      if (!$this->askConfirmation(
  "Are you sure you are ready to migrate your project? [y/N]",
        'QUESTION_LARGE',
        false))
      {
        die("Operation CANCELLED. No changes made.\n");
      }
    }
    $this->migrate = new aMigrate(Doctrine_Manager::connection()->getDbh());

    // If I needed to I could look for the constraint definition like this.
    // But since we added these in the same migration I don't have to. Keep this
    // comment around as sooner or later we'll probably need to check for this
    // kind of thing
    //
    // $createTable = $data[0]['Create Table'];
    // if (!preg_match('/CONSTRAINT `a_redirect_page_id_a_page_id`/', $createTable))
    // {
    //   
    // }
    
    if (!$this->migrate->tableExists('a_redirect'))
    {
      $this->migrate->sql(array(
  "CREATE TABLE IF NOT EXISTS a_redirect (id INT AUTO_INCREMENT, page_id INT, slug VARCHAR(255) UNIQUE, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX slugindex_idx (slug), INDEX page_id_idx (page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = INNODB;",
  "ALTER TABLE a_redirect ADD CONSTRAINT a_redirect_page_id_a_page_id FOREIGN KEY (page_id) REFERENCES a_page(id) ON DELETE CASCADE;"));
    }
    if (!$this->migrate->getCommandsRun())
    {
      echo("Your database is already up to date.\n\n");
    }
    else
    {
      echo($this->migrate->getCommandsRun() . " SQL commands were run.\n\n");
    }
    echo("Done!\n");
  }
}

