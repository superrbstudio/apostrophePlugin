<?php
/**
 * @package    apostrophePlugin
 * @subpackage    task
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class apostropheMigratedatafrompkcontextcmsTask extends sfBaseTask
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
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('skip-search', null, sfCommandOption::PARAMETER_NONE, 'Whether to skip the search index rebuild'),
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

This task brings your site up to Apostrophe 1.0's schema. After completing this task
successfully you must run apostrophe:migrate to complete the transition to the current
release of Apostrophe.

Call it with:

  [php symfony apostrophe:migrate-data-from-pkcontextcms --application=frontend --env=staging|INFO]
  
Be certain to specify the right environment for the system you are running it on.

Note: on ONE development machine, you will run the migrate-from-pkcontextcms task. That
task will run this task as a subtask. Verify success and commit the project, then sync
or svn update your code to other servers and development machines. On those machines,
run this task (migrate-data-from-pkcontextcms) directly to migrate just your data. There 
is no need to migrate the source code more than once!
EOF;
  }

  /**
   * DOCUMENT ME
   * @param mixed $arguments
   * @param mixed $options
   */
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
      $conn->query('ALTER TABLE a_page ADD admin tinyint(1)');
    } catch (Exception $e)
    {
      echo("Warning: unable to add admin column to a_page table\n");
    }

    try
    {
      $conn->query('ALTER TABLE a_slot ADD variant varchar(100)');
    } catch (Exception $e)
    {
      echo("Warning: unable to add variant column to a_slot table\n");
    }
    
    try
    {
      $conn->query("CREATE TABLE `a_slot_media_item` (
        `media_item_id` int(11) NOT NULL DEFAULT '0',
        `slot_id` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`media_item_id`,`slot_id`),
        KEY `a_slot_media_item_slot_id_a_slot_id` (`slot_id`),
        CONSTRAINT `a_slot_media_item_media_item_id_a_media_item_id` FOREIGN KEY (`media_item_id`) REFERENCES `a_media_item` (`id`) ON DELETE CASCADE,
        CONSTRAINT `a_slot_media_item_slot_id_a_slot_id` FOREIGN KEY (`slot_id`) REFERENCES `a_slot` (`id`) ON DELETE CASCADE
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
    } catch (Exception $e)
    {
      echo("Warning: couldn't create a_slot_media_item table\n");
    }
    
    echo("Adding critical columns (apostrophe:migrate will add the rest of the new stuff)\n");
    try
    {
      $conn->query("ALTER TABLE a_page ADD COLUMN engine VARCHAR(255) default NULL");
    } catch (Exception $e)
    {
      echo("Warning: couldn't add engine column to a_page table");
    }

    try
    {
      $conn->query("ALTER TABLE a_page ADD COLUMN view_guest TINYINT(1) default 1");
    } catch (Exception $e)
    {
      echo("Warning: couldn't add view_guest column to a_page table");
    }

    try
    {
      $conn->query("ALTER TABLE a_page ADD COLUMN edit_admin_lock TINYINT(1) default 0");
    } catch (Exception $e)
    {
      echo("Warning: couldn't add edit_admin_lock column to a_page table");
    }

    try
    {
      $conn->query("ALTER TABLE a_page ADD COLUMN view_admin_lock TINYINT(1) default 0");
    } catch (Exception $e)
    {
      echo("Warning: couldn't add view_admin_lock column to a_page table");
    }

    try
    {
      $conn->query("ALTER TABLE a_page ADD COLUMN published_at datetime default NULL");
    } catch (Exception $e)
    {
      echo("Warning: couldn't add published_at column to a_page table");
    }

    try
    {
      $conn->query("UPDATE a_page SET published_at = created_at WHERE published_at IS NULL");
    } catch (Exception $e)
    {
      echo("Warning: couldn't set published_at on a_page table");
    }

    try
    {
      $conn->query("ALTER TABLE a_media_item ADD COLUMN lucene_dirty TINYINT(1) default 0");
    } catch (Exception $e)
    {
      echo("Warning: couldn't add lucene_dirty column to a_media_item table");
    }

    try
    {
      $conn->query('UPDATE tagging SET taggable_model = "aMediaItem" WHERE taggable_model = "pkMediaItem"');
      $conn->query('UPDATE tagging SET taggable_model = "aBlogItem" WHERE taggable_model = "pkBlogItem"');
    } catch (Exception $e)
    {
      echo("Warning: unable to update taggable model names");
    }

    echo("Migrating media slots\n");
    
    try
    {
      $conn->query("CREATE TABLE `a_category` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) DEFAULT NULL,
        `description` text,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        `slug` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`),
        UNIQUE KEY `a_category_sluggable_idx` (`slug`)
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1");
    } catch (Exception $e)
    {
      echo("Warning: couldn't create a_category table\n");
    }

    try
    {
      $conn->query("CREATE TABLE `a_media_item_to_category` (
        `media_item_id` int(11) NOT NULL DEFAULT '0',
        `category_id` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`media_item_id`,`category_id`),
        KEY `a_media_item_to_category_category_id_a_category_id` (`category_id`),
        CONSTRAINT `a_media_item_to_category_category_id_a_category_id` FOREIGN KEY (`category_id`) REFERENCES `a_category` (`id`) ON DELETE CASCADE,
        CONSTRAINT `a_media_item_to_category_media_item_id_a_media_item_id` FOREIGN KEY (`media_item_id`) REFERENCES `a_media_item` (`id`) ON DELETE CASCADE
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1");
    } catch (Exception $e)
    {
      echo("Warning: couldn't create a_media_item_to_category table\n");
    }
    
    $count = 0;
    // Avoid a wildcard select here because we could be missing columns that plugins
    // are going to add at this stage of play. Get the columns we need only
    $mediaSlots = Doctrine::getTable('aSlot')->createQuery('s')->select('s.id, s.type, s.value')->whereIn('s.type', array('aImage', 'aPDF', 'aButton', 'aSlideshow', 'aVideo'))->execute();
    $total = count($mediaSlots);
    foreach ($mediaSlots as $mediaSlot)
    {
      $count++;
      echo("Migrating slot $count of $total\n");
      if ($mediaSlot->type === 'aSlideshow')
      {
        $items = $mediaSlot->getArrayValue();
        if (isset($items[0]) && isset($items[0]->id))
        {
          $order = array();
          foreach ($items as $item)
          {
            // aArray::getids has trouble with StdClass objects for some reason
            $order[] = $item->id;
          }
          $mediaSlot->unlink('MediaItems');
          $mediaSlot->link('MediaItems', $order);
          $mediaSlot->setArrayValue(array('order' => $order));
          $mediaSlot->save();
        }
      }
      else
      {
        if (strlen($mediaSlot->value))
        {
          $item = unserialize($mediaSlot->value);
          if (isset($item->id))
          {
            $mediaSlot->unlink('MediaItems');
            $mediaSlot->link('MediaItems', array($item->id));
            $mediaSlot->setValue(null);
            $mediaSlot->save();
          }
        }
      }
    }
          
    try
    {
      $conn->query("CREATE TABLE `a_media_category` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) DEFAULT NULL,
        `description` text,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        `slug` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
      ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1");
    } catch (Exception $e)
    {
      echo("Warning: couldn't create a_media_category table\n");
    }
    
    try
    {
      $conn->query("CREATE TABLE `a_media_item_category` (
        `media_item_id` int(11) NOT NULL DEFAULT '0',
        `media_category_id` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`media_item_id`,`media_category_id`),
        KEY `a_media_item_category_media_category_id_a_media_category_id` (`media_category_id`),
        CONSTRAINT `a_media_item_category_media_category_id_a_media_category_id` FOREIGN KEY (`media_category_id`) REFERENCES `a_media_category` (`id`) ON DELETE CASCADE,
        CONSTRAINT `a_media_item_category_media_item_id_a_media_item_id` FOREIGN KEY (`media_item_id`) REFERENCES `a_media_item` (`id`) ON DELETE CASCADE
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1");
    } catch (Exception $e)
    {
      echo("Warning: couldn't create a_media_item_category table\n");
    }

    echo("Running apostrophe:migrate\n");
    $cmd = "./symfony apostrophe:migrate --env=" . $options['env'];
    system($cmd, $result);
    if ($result != 0)
    {
      die("Unable to run apostrophe:migrate to complete the transition from pkContextCMS\n");
    }

    // Let apostrophe:migrate take care of this one
    
    // try
    // {
    //   $conn->query("CREATE TABLE `a_media_page_category` (
    //     `page_id` int(11) NOT NULL DEFAULT '0',
    //     `media_category_id` int(11) NOT NULL DEFAULT '0',
    //     PRIMARY KEY (`page_id`,`media_category_id`),
    //     KEY `a_media_page_category_media_category_id_a_media_category_id` (`media_category_id`),
    //     CONSTRAINT `a_media_page_category_media_category_id_a_media_category_id` FOREIGN KEY (`media_category_id`) REFERENCES `a_media_category` (`id`) ON DELETE CASCADE,
    //     CONSTRAINT `a_media_page_category_page_id_a_page_id` FOREIGN KEY (`page_id`) REFERENCES `a_page` (`id`) ON DELETE CASCADE
    //   ) ENGINE=InnoDB DEFAULT CHARSET=latin1");
    // } catch (Exception $e)
    // {
    //   echo("Warning: couldn't create a_media_page_category table\n");
    // }
  
    echo("Rebuilding search index\n");
    if (!$options['skip-search'])
    {
  		$cmd = "./symfony apostrophe:rebuild-search-index --verbose --env=" . $options['env'];
      system($cmd, $result);
      if ($result != 0)
      {
        die("Unable to rebuild search indexes\n");
      }
    }
    echo("If you have folders in data/pk_writable other than tmp and the zend search indexes 
you may want to move them to data/a_writable. Due to interactions with svn this is not
automatic. In our projects we use svn ignore rules to protect the contents of the
data/*_writable folder. This is primarily an issue on servers other than your
development machine, where you run this task separately. On your development
machine pk_writable is renamed to a_writable automatically.\n");

    // We need to be an admin user so the model layer sees the current user has
    // permissions to do what follows. We can't do this any earlier because 
    // the routing table fires up and routes the home page which requires looking
    // at some engine routes, so the a_page table has to be ready
    
    aTaskTools::signinAsTaskUser($this->createConfiguration($options['application'], $options['env']));


    // Create the admin pages
    
    $home = aPageTable::retrieveBySlug('/');
    
    $admin = aPageTable::retrieveBySlug('/admin');

    if (!$admin)
    {
      $admin = new aPage();
      $admin->setSlug('/admin');
      $admin->setAdmin(true);
      $admin->getNode()->insertAsFirstChildOf($home);
      $admin->setEngine('aAdmin');
      $admin->save();
      // Must save the page BEFORE we call setTitle, which has the side effect of
      // refreshing the page object
      $admin->setTitle('Admin');
    }
    
    $page = aPageTable::retrieveBySlug('/admin/media');
    if (!$page)
    {
      $page = new aPage();
      $page->setSlug('/admin/media');
      $page->setAdmin(true);
      $page->getNode()->insertAsLastChildOf($admin);
      $page->setEngine('aMedia');
      $page->save();
      // Must save the page BEFORE we call setTitle, which has the side effect of
      // refreshing the page object
      $page->setTitle('Media');  
    }
  }
}
