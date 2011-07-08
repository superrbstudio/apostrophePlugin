<?php
/**
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aToolkitEvents
{
  static protected $once = false;
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
    }
  }
}

