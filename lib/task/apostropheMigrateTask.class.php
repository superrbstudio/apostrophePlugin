<?php
/**
 * @package    apostrophePlugin
 * @subpackage    task
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class apostropheMigrateTask extends sfBaseTask
{

  /**
   * DOCUMENT ME
   */
  protected function configure()
  {

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
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

  /**
   * DOCUMENT ME
   * @param mixed $arguments
   * @param mixed $options
   */
  protected function execute($arguments = array(), $options = array())
  {
    // We need a basic context so we can notify events
    $context = sfContext::createInstance($this->configuration);
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    $postTasks = array();
    
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
        'INSERT INTO sf_guard_permission (name, description) VALUES ("editor", "For groups that will be granted editing privileges at some point in the site") ON DUPLICATE KEY UPDATE id = id;',
        ));
    }
    $viewLocked = sfConfig::get('app_a_view_locked_sufficient_credentials', 'view_locked');
    // If they haven't customized it make sure it exists. Some pkContextCMS sites might not have it
    if ($viewLocked === 'view_locked')
    {
      $permission = Doctrine::getTable('sfGuardPermission')->findOneByName($viewLocked);
      if (!$permission)
      {
        $permission = new sfGuardPermission();
        $permission->setName('view_locked');
        $permission->save();
        $groups = array('editor', 'admin');
        foreach ($groups as $group)
        {
          $g = Doctrine::getTable('sfGuardGroup')->findOneByName($group);
          if ($g)
          {
            $pg = new sfGuardGroupPermission();
            $pg->setGroupId($g->id);
            $pg->setPermissionId($permission->id);
            $pg->save();
          }
        }
      }
    }
    if ((!$this->migrate->tableExists('a_category')) || (!$this->migrate->tableExists('a_category_group')))
    {
      $this->migrate->sql(array(
        "CREATE TABLE IF NOT EXISTS a_category (id INT AUTO_INCREMENT, name VARCHAR(255) UNIQUE, description TEXT, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, slug VARCHAR(255), UNIQUE INDEX a_category_sluggable_idx (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = INNODB;",
        "CREATE TABLE IF NOT EXISTS a_category_group (category_id INT, group_id INT, PRIMARY KEY(category_id, group_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = INNODB;",
        "CREATE TABLE IF NOT EXISTS a_category_user (category_id INT, user_id INT, PRIMARY KEY(category_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = INNODB;",
        "CREATE TABLE `a_page_to_category` (
          `page_id` INT NOT NULL DEFAULT '0',
          `category_id` INT NOT NULL DEFAULT '0',
          PRIMARY KEY (`page_id`,`category_id`),
          KEY `a_page_to_category_category_id_a_category_id` (`category_id`),
          CONSTRAINT `a_page_to_category_category_id_a_category_id` FOREIGN KEY (`category_id`) REFERENCES `a_category` (`id`) ON DELETE CASCADE,
          CONSTRAINT `a_page_to_category_page_id_a_page_id` FOREIGN KEY (`page_id`) REFERENCES `a_page` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
        "CREATE TABLE IF NOT EXISTS `a_media_item_to_category` (
          `media_item_id` INT NOT NULL DEFAULT '0',
          `category_id` INT NOT NULL DEFAULT '0',
          PRIMARY KEY (`media_item_id`,`category_id`),
          KEY `a_media_item_to_category_category_id_a_category_id` (`category_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8"));
      // These constraints might already be present, be tolerant
      $constraints = array(
        "ALTER TABLE a_media_item_to_category ADD CONSTRAINT `a_media_item_to_category_category_id_a_category_id` FOREIGN KEY (`category_id`) REFERENCES `a_category` (`id`) ON DELETE CASCADE",
        "ALTER TABLE a_media_item_to_category ADD CONSTRAINT `a_media_item_to_category_media_item_id_a_media_item_id` FOREIGN KEY (`media_item_id`) REFERENCES `a_media_item` (`id`) ON DELETE CASCADE",
        "ALTER TABLE a_category_group ADD CONSTRAINT a_category_group_group_id_sf_guard_group_id FOREIGN KEY (group_id) REFERENCES sf_guard_group(id) ON DELETE CASCADE;",
        "ALTER TABLE a_category_group ADD CONSTRAINT a_category_group_category_id_a_category_id FOREIGN KEY (category_id) REFERENCES a_category(id) ON DELETE CASCADE;",
        "ALTER TABLE a_category_user ADD CONSTRAINT a_category_user_user_id_sf_guard_user_id FOREIGN KEY (user_id) REFERENCES sf_guard_user(id) ON DELETE CASCADE;",
        "ALTER TABLE a_category_user ADD CONSTRAINT a_category_user_category_id_a_category_id FOREIGN KEY (category_id) REFERENCES a_category(id) ON DELETE CASCADE;"
        );
      foreach ($constraints as $c)
      {
        try
        {
          $this->migrate->sql(array($c));
        } catch (Exception $e)
        {
          echo("Error creating constraint, most likely already exists, which is OK $c\n");
        }
      }
      if ($this->migrate->tableExists('a_media_category'))
      {
        $oldCategories = $this->migrate->query('SELECT * FROM a_media_category');
      }
      else
      {
        $oldCategories = array();
      }
      $newCategories = $this->migrate->query('SELECT * FROM a_category');
      $nc = array();
      foreach ($newCategories as $newCategory)
      {
        $nc[$newCategory['slug']] = $newCategory;
      }
      $oldIdToNewId = array();
      
      echo("Migrating media categories to Apostrophe categories...\n");
      foreach ($oldCategories as $category)
      {
        if (isset($nc[$category['slug']]))
        {
          $oldIdToNewId[$category['id']] = $nc[$category['slug']]['id'];
        }
        else
        {
          $this->migrate->query('INSERT INTO a_category (name, description, slug) VALUES (:name, :description, :slug)', $category);
          $oldIdToNewId[$category['id']] = $this->migrate->lastInsertId();
        }
      }
      echo("Migrating from aMediaItemCategory to aMediaItemToCategory...\n");
      
      $oldMappings = $this->migrate->query('SELECT * FROM a_media_item_category');
      foreach ($oldMappings as $info)
      {
        $info['category_id'] = $oldIdToNewId[$info['media_category_id']];
        $this->migrate->query('INSERT INTO a_media_item_to_category (media_item_id, category_id) VALUES (:media_item_id, :category_id)', $info);
      }
    }
    if (!$this->migrate->tableExists('a_embed_media_account'))
    {
      $this->migrate->sql(array(
        'CREATE TABLE a_embed_media_account (id INT AUTO_INCREMENT, service VARCHAR(100) NOT NULL, username VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = INNODB;'));
    }
    if (!$this->migrate->columnExists('a_page', 'edit_admin_lock'))
    {
      $this->migrate->sql(array(
        'ALTER TABLE a_page ADD COLUMN edit_admin_lock TINYINT(1) DEFAULT "0"',
        'ALTER TABLE a_page ADD COLUMN view_admin_lock TINYINT(1) DEFAULT "0"'
        ));
      $options = array('application' => $options['application'], 'env' => $options['env'], 'connection' => $options['connection']);
      $postTasks[] = array('task' => new apostropheCascadeEditPermissionsTask($this->dispatcher, $this->formatter), 'arguments' => array(), 'options' => $options);
    }
    if (!$this->migrate->columnExists('a_page', 'view_guest'))
    {
      $this->migrate->sql(array(
        'ALTER TABLE a_page ADD COLUMN view_guest TINYINT(1) DEFAULT "1"'
        ));
      $options = array('application' => $options['application'], 'env' => $options['env'], 'connection' => $options['connection']);
      $postTasks[] = array('task' => new apostropheCascadeEditPermissionsTask($this->dispatcher, $this->formatter), 'arguments' => array(), 'options' => $options);
    }
    
    // Migrate all IDs to BIGINT (the default in Doctrine 1.2) for compatibility with the
    // new version of sfDoctrineGuardPlugin. NOTE: we continue to use INT in create table
    // statements BEFORE this point because we need to set up relations with what they already
    // have - this call will clean that up
    
    $this->migrate->upgradeIds();
    
    // Upgrade all charsets to UTF-8 otherwise we can't store a lot of what comes back from embed services
    $this->migrate->upgradeCharsets();
    
    
    // We can add these constraints now that we have IDs of the right size
    if (!$this->migrate->constraintExists('a_media_item_to_category', 'a_media_item_to_category_category_id_a_category_id'))
    {
      $this->migrate->sql(array(
        // IDs of a_media_item_to_category might still be too small because we forgot the constraints at first
        'ALTER TABLE a_media_item_to_category MODIFY COLUMN category_id BIGINT',          
        'ALTER TABLE a_media_item_to_category MODIFY COLUMN media_item_id BIGINT',
        "ALTER TABLE a_media_item_to_category ADD CONSTRAINT `a_media_item_to_category_category_id_a_category_id` FOREIGN KEY (`category_id`) REFERENCES `a_category` (`id`) ON DELETE CASCADE",
        "ALTER TABLE a_media_item_to_category ADD CONSTRAINT `a_media_item_to_category_media_item_id_a_media_item_id` FOREIGN KEY (`media_item_id`) REFERENCES `a_media_item` (`id`) ON DELETE CASCADE"));
    }
    
    // sfDoctrineGuardPlugin 5.0.x requires this
    if (!$this->migrate->columnExists('sf_guard_user', 'email_address'))
    {
      $this->migrate->sql(array(
        'ALTER TABLE sf_guard_user ADD COLUMN first_name varchar(255) DEFAULT NULL',
        'ALTER TABLE sf_guard_user ADD COLUMN last_name varchar(255) DEFAULT NULL',
        'ALTER TABLE sf_guard_user ADD COLUMN email_address varchar(255) DEFAULT \'\''
      ));
      // Email addresses are mandatory and can't be null. We can't start guessing whether
      // you have them in some other table or not. So the best we can do is stub in 
      // the username for uniqueness for now
      $this->migrate->sql(array(
        'UPDATE sf_guard_user SET email_address = concat(username, \'@notavalidaddress\')',
        'ALTER TABLE sf_guard_user ADD UNIQUE KEY `email_address` (`email_address`);'
      ));
    }
    if (!$this->migrate->tableExists('sf_guard_forgot_password'))
    {
      $this->migrate->sql(array('
        CREATE TABLE `sf_guard_forgot_password` (
          `id` bigint(20) NOT NULL AUTO_INCREMENT,
          `user_id` bigint(20) NOT NULL,
          `unique_key` varchar(255) DEFAULT NULL,
          `expires_at` datetime NOT NULL,
          `created_at` datetime NOT NULL,
          `updated_at` datetime NOT NULL,
          PRIMARY KEY (`id`),
          KEY `user_id_idx` (`user_id`),
          CONSTRAINT `sf_guard_forgot_password_user_id_sf_guard_user_id` FOREIGN KEY (`user_id`) REFERENCES `sf_guard_user` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8'));
    }
    
    if (!$this->migrate->columnExists('a_page', 'published_at'))
    {
      $this->migrate->sql(array(
        'ALTER TABLE a_page ADD COLUMN published_at DATETIME DEFAULT NULL', 
        'UPDATE a_page SET published_at = created_at WHERE published_at IS NULL'));
    }

    // Remove any orphaned media items created by insufficiently carefully written embed services,
    // these can break the media repository
    $this->migrate->sql(array(
      'DELETE FROM a_media_item WHERE type="video" AND embed IS NULL AND service_url IS NULL'
    ));
    
    // Rename any tags with slashes in them to avoid breaking routes in Symfony
    $this->migrate->sql(array(
      'UPDATE tag SET name = replace(name, "/", "-")'
    ));
  
    // A chance to make plugin- and project-specific additions to the schema before Doctrine queries fail to see them
    sfContext::getInstance()->getEventDispatcher()->notify(new sfEvent($this->migrate, "a.migrateSchemaAdditions"));
    
    $mediaEnginePage = Doctrine::getTable('aPage')->createQuery('p')->where('p.admin IS TRUE AND p.engine = "aMedia"')->fetchOne();
    if (!$mediaEnginePage)
    {
      $mediaEnginePage = new aPage();
      $root = aPageTable::retrieveBySlug('/');
      $mediaEnginePage->getNode()->insertAsFirstChildOf($root);
    }
    $mediaEnginePage->slug = '/admin/media';
    $mediaEnginePage->engine = 'aMedia';
    $mediaEnginePage->setAdmin(true);
    $mediaEnginePage->setPublishedAt(aDate::mysql());
    $new = $mediaEnginePage->isNew();
    $mediaEnginePage->save();
    if ($new)
    {
      $mediaEnginePage->setTitle('Media');
    }
    echo("Ensured there is an admin media engine\n");
    
    if (!$this->migrate->tableExists('a_cache_item'))
    {
      $this->migrate->sql(array('CREATE TABLE a_cache_item (k VARCHAR(255), value LONGBLOB, timeout BIGINT, last_mod BIGINT, PRIMARY KEY(k)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = INNODB;'));
    }
    else
    {
      $data = $this->migrate->query("SHOW CREATE TABLE a_cache_item");
      $desc = $data[0]['Create Table'];
      // longchar was a big mistake here, it can't store binary data
      if (strpos($desc, 'longtext') !== false)
      {
        $this->migrate->sql(array('ALTER TABLE a_cache_item MODIFY COLUMN value LONGBLOB', 'DELETE FROM a_cache_item'));
      }
    }
    if ($this->migrate->tableExists('a_cache_item')) 
    echo("Finished updating tables.\n");
    if (count($postTasks))
    {
      echo("Invoking post-migration tasks...\n");
      foreach ($postTasks as $taskInfo)
      {
        $taskInfo['task']->run($taskInfo['arguments'], $taskInfo['options']);
      }
    }
    echo("Done!\n");
  }
}

