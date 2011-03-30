<?php
/**
 * @package    apostrophePlugin
 * @subpackage    task
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class apostropheCascadeEditPermissionsTask extends sfBaseTask
{

  /**
   * DOCUMENT ME
   */
  protected function configure()
  {

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'cascade-edit-permissions';
    $this->briefDescription = 'Copy edit permissions to descendant pages';
    $this->detailedDescription = <<<EOF
The [apostrophe:cascade-edit-permissions|INFO] task copies the edit permissions of parent pages 
to their children. This provides backwards compatibility with Apostrophe 1.4's "permissions 
are inherited" policy. This task is primarily used to migrate permissions when upgrading to 
1.5, but you can use it on a scheduled basis if you wish. We recommend that you instead use 
the generous provisions for applying individual permissions settings to all descendant pages 
that can be found in the Apostrophe page settings.

  [php symfony apostrophe:cascade-edit-permissions-task|INFO]
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
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();
    // Cope with large datasets via PDO
    $migrate = new aMigrate(Doctrine_Manager::connection()->getDbh());

    $postTasks = array();
    
    echo("Copying individual edit permissions to child pages to keep your 1.4-style policies in place. 
We suggest running this task only once, which allows you to be more flexible in future and take permissions
away as needed on child pages. There are generous provisions for applying permissions settings to
child pages on an as-needed basis in the user interface.
");
  
    $accesses = Doctrine::getTable('aAccess')->findAll(Doctrine::HYDRATE_ARRAY);
    $total = count($accesses);
    $step = 0;
    foreach ($accesses as $a)
    {
      $step++;
      echo("Applying $step of $total...\n");
      $page = Doctrine::getTable('aPage')->find($a['page_id'], Doctrine::HYDRATE_ARRAY);
      if (!$page)
      {
        // Don't panic if there's a bad access
        continue;
      }
      $subpages = Doctrine::getTable('aPage')->createQuery('p')->where('p.lft > ? AND p.rgt < ?', array($page['lft'], $page['rgt']))->execute(array(), Doctrine::HYDRATE_ARRAY);
      foreach ($subpages as $subpage)
      {
        // Doctrine runs out of memory when we make a lot of new objects, so go a little more low-level
        $na = array('page_id' => $subpage['id'], 'user_id' => $a['user_id'], 'privilege' => $a['privilege']);
        if (!count($migrate->query('SELECT id FROM a_access WHERE page_id = :page_id AND user_id = :user_id AND privilege = :privilege', $na)))
        {
          $migrate->query('INSERT INTO a_access (page_id, user_id, privilege) VALUES (:page_id, :user_id, :privilege)', $na);
        }
      }
    }
    
    echo("Copying group permissions (most 1.4 sites won't have these)...\n");
    $accesses = Doctrine::getTable('aGroupAccess')->findAll(Doctrine::HYDRATE_ARRAY);
    $total = count($accesses);
    $step = 0;
    foreach ($accesses as $a)
    {
      $step++;
      echo("Applying $step of $total...\n");
      $page = Doctrine::getTable('aPage')->find($a['page_id'], Doctrine::HYDRATE_ARRAY);
      if (!$page)
      {
        // Don't panic if there's a bad access
        continue;
      }
      $subpages = Doctrine::getTable('aPage')->createQuery('p')->where('p.lft > ? AND p.rgt < ?', array($page['lft'], $page['rgt']))->execute(array(), Doctrine::HYDRATE_ARRAY);
      foreach ($subpages as $subpage)
      {
        // Doctrine runs out of memory when we make a lot of new objects, so go a little more low-level
        $na = array('page_id' => $subpage['id'], 'group_id' => $a['group_id'], 'privilege' => $a['privilege']);
        if (!count($migrate->query('SELECT id FROM a_group_access WHERE page_id = :page_id AND group_id = :group_id AND privilege = :privilege', $na)))
        {
          $migrate->query('INSERT INTO a_group_access (page_id, group_id, privilege) VALUES (:page_id, :group_id, :privilege)', $na);
        }
      }
    }
    echo("Permissions copied.\n");
  }
}

