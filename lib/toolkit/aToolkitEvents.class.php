<?php
/**
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aToolkitEvents
{
  static protected $once = false;
  static protected $options = array();
  /**
   * command.post_command
   * @param sfEvent $event
   */
  static public function listenToCommandPreCommandEvent(sfEvent $event)
  {
    aToolkitEvents::$options = $event['options'];
  }
  
  /**
   * command.post_command
   * @param sfEvent $event
   */
  static public function listenToCommandPostCommandEvent(sfEvent $event)
  {
    if (aToolkitEvents::$once)
    {
      return;
    }
    aToolkitEvents::$once = true;
    $task = $event->getSubject();
    if ($task->getFullName() === 'project:permissions')
    {
      $writable = aFiles::getWritableDataFolder();
      $task->getFilesystem()->chmod($writable, 0777);
      $dirFinder = sfFinder::type('dir');
      $fileFinder = sfFinder::type('file');
      $task->getFilesystem()->chmod($dirFinder->in($writable), 0777);
      $task->getFilesystem()->chmod($fileFinder->in($writable), 0666);
    }
    if ($task->getFullName() === 'cache:clear')
    {
      try
      {
        // symfony cc does not fire up the database, which aMysqlCache needs
        $options = aToolkitEvents::$options;
        if (!isset($options['app']))
        {
          $options['app'] = 'frontend';
        } 
        if (!isset($options['env']))
        {
          $options['env'] = 'dev';
        }
        $appConfiguration = ProjectConfiguration::getApplicationConfiguration($options['app'], $options['env'], true);
        $databaseManager = new sfDatabaseManager($appConfiguration);
        $connections = $databaseManager->getNames();
        if (count($connections))
        {
          $databaseManager->getDatabase($connections[0])->getConnection();
        }
        aAssets::clearAssetCache($task->getFilesystem());
      
        // Clear the page cache on symfony cc
        if (sfConfig::get('app_a_page_cache_enabled', false))
        {
          echo("Clearing Apostrophe page cache\n");
          $cache = aCacheFilter::getCache();
          $cache->clean();
        }
        else
        {
          // Cache not enabled for this environment. Too many tasks
          // invoke symfony cc with no environment, so let's not print
          // anything needlessly worrying here
        }
      } catch (Exception $e)
      {
        echo("WARNING: the following exception occurred while clearing caches. If you do not have\n");
        echo("a database yet this may be OK otherwise specify --env and --application correctly:\n\n");
        echo($e);
      }
    }
  }
}

