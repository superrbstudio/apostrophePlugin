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
    if (!$this->migrate->columnExists('a_media_item', 'lucene_dirty'))
    {
      $this->migrate->sql(array(
        "ALTER TABLE a_media_item ADD COLUMN lucene_dirty BOOLEAN DEFAULT false;"));
    }
    if (!$this->migrate->getCommandsRun())
    {
      echo("Your database is already up to date.\n\n");
    }
    else
    {
      echo($this->migrate->getCommandsRun() . " SQL commands were run.\n\n");
    }
    
    if (!$this->migrate->tableExists('a_group_access'))
    {
      // They don't have a group access table yet. In theory, they don't have an editor permission
      // to grant to groups yet either. However that is a likely name for them to invent on their
      // own, so make sure we don't panic if there is already a permission called eidtor
      $this->migrate->sql(array(
        'CREATE TABLE a_group_access (id BIGINT AUTO_INCREMENT, page_id INT, privilege VARCHAR(100), group_id INT, INDEX pageindex_idx (page_id), INDEX group_id_idx (group_id), PRIMARY KEY(id)) ENGINE = INNODB;',
        'ALTER TABLE a_group_access ADD CONSTRAINT a_group_access_page_id_a_page_id FOREIGN KEY (page_id) REFERENCES a_page(id) ON DELETE CASCADE;',
        'ALTER TABLE a_group_access ADD CONSTRAINT a_group_access_group_id_sf_guard_group_id FOREIGN KEY (group_id) REFERENCES sf_guard_group(id) ON DELETE CASCADE;',
        'INSERT INTO sf_guard_permission (name, description) VALUES ("editor", "For groups that will be granted editing privileges at some point in the site") ON DUPLICATE KEY UPDATE id = id;'));
    }
    if (!$this->migrate->tableExists('a_category'))
    {
      $this->migrate->sql(array(
        "CREATE TABLE a_category (id INT AUTO_INCREMENT, name VARCHAR(255) UNIQUE, media_items TINYINT(1) DEFAULT '0', description TEXT, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, slug VARCHAR(255), UNIQUE INDEX a_category_sluggable_idx (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = INNODB;",
        "CREATE TABLE a_category_group (category_id INT, group_id INT, PRIMARY KEY(category_id, group_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = INNODB;",
        "CREATE TABLE a_category_user (category_id INT, user_id INT, PRIMARY KEY(category_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = INNODB;",
        "CREATE TABLE a_media_item_to_category (media_item_id INT, category_id INT, PRIMARY KEY(media_item_id, category_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = INNODB;",
        "ALTER TABLE a_category_group ADD CONSTRAINT a_category_group_group_id_sf_guard_group_id FOREIGN KEY (group_id) REFERENCES sf_guard_group(id) ON DELETE CASCADE;",
        "ALTER TABLE a_category_group ADD CONSTRAINT a_category_group_category_id_a_category_id FOREIGN KEY (category_id) REFERENCES a_category(id) ON DELETE CASCADE;",
        "ALTER TABLE a_category_user ADD CONSTRAINT a_category_user_user_id_sf_guard_user_id FOREIGN KEY (user_id) REFERENCES sf_guard_user(id) ON DELETE CASCADE;",
        "ALTER TABLE a_category_user ADD CONSTRAINT a_category_user_category_id_a_category_id FOREIGN KEY (category_id) REFERENCES a_category(id) ON DELETE CASCADE;"
        ));
      $oldCategories = $this->migrate->query('SELECT name, description, slug FROM a_media_category');
      $newCategories = $this->migrate->query('SELECT name, description, slug FROM a_category');
      $nc = array();
      foreach ($newCategories as $newCategory)
      {
        $nc[$newCategory['slug']] = $newCategory;
      }
      $oldIdToNewId = array();
      
      foreach ($oldCategories as $category)
      {
        if (isset($nc[$category['slug']]))
        {
          $this->migrate->query('UPDATE a_category SET media_items = true WHERE slug = :slug', $category);
          $oldIdtoNewId[$category['id']] = $nc[$category['slug']]['id'];
          
        }
        else
        {
          $this->migrate->query('INSERT INTO a_category (name, description, slug, media_items) VALUES (:name, :description, :slug)', $category);
          $oldIdtoNewId[$category['id']] = $this->migrate->lastInsertId();
        }
      }
      $oldMappings = $migrate->query('SELECT * FROM a_media_item_category');
      foreach ($oldMappings as $info)
      {
        $info['category_id'] = $oldIdToNewId[$info['media_category_id']];
        $migrate->query('INSERT INTO a_media_item_to_category (media_item_id, category_id) VALUES (:media_item_id, :category_id)', $info);
      }
    }
    echo("Done!\n");
  }
}

