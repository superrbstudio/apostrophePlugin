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
    $task = $event->getSubject();
    if ($task->getFullName() === 'cache:clear')
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
      sfContext::createInstance($appConfiguration);
    }
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
        aCacheTools::clearAll();
        // Not a normal sfCache-derived thing, this is a folder of compiled CSS files
        aAssets::clearAssetCache($task->getFilesystem());
      } catch (Exception $e)
      {
        echo("WARNING: the following exception occurred while clearing caches. If you do not have\n");
        echo("a database yet this may be OK:\n\n");
        echo($e);
      }
      
      // Help out any custom cache code the developer may have by
      // posting a simple a.afterClearCache event
      $event = new sfEvent(null, 'a.afterClearCache', array());
      sfContext::getInstance()->getEventDispatcher()->notify($event);
    }
  }
}

