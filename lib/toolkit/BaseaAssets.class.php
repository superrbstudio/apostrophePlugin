<?php

/**
 * We are gradually refactoring the moving parts of LESS and Minify support into
 * this class from aHelper where they are too hard to reuse in different contexts
 */
 
class BaseaAssets
{
	static $lessc = null;
	
	static protected $cache;

  /**
   * Access to an sfCache used to leverage the excellent dependency caching capabilities of lessphp
   * @param string $key
   * @return mixed
   */
  static public function getCached($key)
  {
    $cache = aAssets::getCache();
    $value = $cache->get($key, null);
    if ($value === null)
    {
      return null;
    }
    return unserialize($value);
  }

  /**
   * Interval (lifetime) is in seconds, a full ten days is fine here,
   * it could be indefinite really
   * @param string $key
   * @param mixed $value
   * @param mixed $interval
   */
  static public function setCached($key, $value, $interval=864000)
  {
    $cache = aAssets::getCache();
    $cache->set($key, serialize($value), $interval);
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function getCache()
  {
    if (aAssets::$cache)
    {
      return aAssets::$cache;
    }
    $cacheClass = sfConfig::get('app_a_less_cache_class', 'sfFileCache');
    aAssets::$cache = new $cacheClass(sfConfig::get('app_a_less_cache_options', array('cache_dir' => aFiles::getWritableDataFolder(array('a_less_cache')))));
    return aAssets::$cache;
  }
  
  /**
   * Compiles the less source file $path to the CSS file $compiled unless it is up to date
   * according to the high quality dependency checking provided by lessphp
   */
  static public function compileLessIfNeeded($path, $compiled, $options = array())
  {
	  // Leverage the considerable caching abilities of lessphp directly.
	  // it's smart enough to pay attention to whether imported files
	  // were cached or not, and we're not that smart
	  
    if (!isset(aAssets::$lessc))
    {
      // We do it like factories.yml does it, defaulting to the built in lessc.
      // this is a nice injection point because it's common to subclass lessc
      // The regular lessc class constructor doesn't take useful constructor parameters
      // as far as we're concerned, it doesn't even use its second argument '$opts'.
      // But you can write subclasses that take a useful options array as their
      // first argument
      $factory = sfConfig::get('app_a_lessc', array('class' => 'lessc', 'param' => null));
      $class = $factory['class'];
      $param = $factory['param'];
      aAssets::$lessc = new $class($param);
  		// set a new import directory in app.yml if you want to change our imported files, like a-helpers.less
      aAssets::$lessc->importDir = sfConfig::get('app_a_less_import_directory', dirname($path) . '/');
    }
    $info = aAssets::getCached($path);
    if (is_null($info))
    {
      $info = $path;
    }
    $lastUpdated = is_array($info) ? $info['updated'] : 0;
    $info = aAssets::$lessc->cexecute($info, false);
    aAssets::setCached($path, $info);
    if ($info['updated'] !== $lastUpdated)
    {
      // Copy it to our asset cache folder since it has changed
      file_put_contents($compiled, $info['compiled']);
    }
  }
  
  public static function clearAssetCache(sfFilesystem $fileSystem)
  {
    $assetDir = aFiles::getUploadFolder(array('asset-cache'));
    $fileSystem->remove(sfFinder::type('file')->in($assetDir));
    $cache = aAssets::getCache();
    $cache->clean();
  }
}
