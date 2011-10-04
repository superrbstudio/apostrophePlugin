<?php

/**
 * Provides tools to obtain an sfCache-compatible object to cache information in a
 * particular category, such as the embed service, page, RSS feed, image hint and 
 * LESS compilation caches. 
 *
 * @class aCacheTools
 * @author tom@punkave.com
 *
 */
class aCacheTools
{
  static protected $caches;
  
  /**
   * If you call aCacheTools::getCache('feed') and app_a_feed_cache_class and app_a_feed_cache_options 
   * have been set, you get that specific class constructed with those options. If just 
   * app_a_feed_cache_options is set the class defaults to sfFileCache for backwards compatibility. 
   *
   * If there is no explicit configuration for the cache name and app_a_cache_default_class is
   * set to the name of a subclass of sfCache, an object of that class is created created with 
   * the prefix option set to the name to distinguish the objects in this cache from others 
   * stored in the same backend. This is the recommended approach going forward, and in the
   * sandbox project app_a_cache_default_class is set to aMysqlCache. 
   * 
   * However for backwards compatibility if app_a_cache_default_class is not set at all we default 
   * to an sfFileCache in data/a_writable/a_$name_cache.
   *
   * Repeated calls with the same name during a single request will return the same cache object.
   *
   */
  static public function get($name)
  {
    if (isset(aCacheTools::$caches[$name]))
    {
      return aCacheTools::$caches[$name];
    }
    // Explicit cache configuration always wins
    if (sfConfig::get('app_a_' . $name . '_cache_class') || sfConfig::get('app_a_' . $name . '_cache_options'))
    {
      $class = sfConfig::get('app_a_' . $name . '_cache_class', 'sfFileCache');
      $options = sfConfig::get('app_a_' . $name . '_cache_options');
      $cache = new $class($options);
    }
    // In the sandbox this is set to aMysqlCache, but we don't retroactively force that
    // on old projects that won't have the a_cache_item table
    elseif (sfConfig::get('app_a_cache_default_class', false))
    {
      $class = sfConfig::get('app_a_cache_default_class');
      $cache = new $class(array('prefix' => $name));
    }
    else
    {
      // The folder location cache cannot be located with getWritableDataFolder (infinite recursion),
      // but in situations where a file cache is acceptable there is no benefit in having
      // a folder location cache anyway. sfNoCache implements the cache APIs but never
      // turns out to contain anything (:
      if ($name === 'folder')
      {
        $cache = new sfNoCache();
      }
      else
      {
        // Old school behavior
        $cache = new sfFileCache(array('cache_dir' => aFiles::getWritableDataFolder(array('a_' . $name . '_cache'))));
      }
    }
    aCacheTools::$caches[$name] = $cache;
    return $cache;
  }
  
  /**
   * Clears all caches listed in app_a_cache_clear_list, or a default list. Uses get() so that
   * your custom settings are respected. We also notify the a.afterClearCache event which you
   * may find useful if you are not following this pattern for some reason
   *
   */
  
  static public function clearAll()
  {
    $cacheClearList = sfConfig::get('app_a_cache_clear_list', array('feed', 'embed', 'media', 'page', 'folder', 'hint', 'less', 'assetStat'));
    foreach ($cacheClearList as $cacheName)
    {
      $cache = aCacheTools::get($cacheName);
      $cache->clean();
    }
  }
}
