<?php

class aFeed
{
	/**
	 * Takes the url/routing rule of a feed and adds it to the request attributes to be read by
	 * include_feeds() (see feedHelper.php), which is called in the layout. Allows for dynamic
	 * inclusion of rel tags for RSS. 
	 * http://spindrop.us/2006/07/04/dynamic-linking-to-syndication-feeds-with-symfony/
	 *
	 * @author Dave Dash (just this method)
	 *
	 * Unrelated to aFeed slots.
	 */
	public static function addFeed($request, $feed)
	{
		$feeds = $request->getAttribute('helper/asset/auto/feed', array());
		
		$feeds[$feed] = $feed;
		
		$request->setAttribute('helper/asset/auto/feed', $feeds);
	}
	
	// Rock the Symfony cache to avoid fetching the same external URL over and over
  
  // These defaults are safe and boring and way faster than bashing on other servers.
  // But here's a tip. If you don't have APC enabled your site is probably running very, 
  // very slowly, so fix that. And then do this for even better speed:
  //
  // a:
  //   feed:
  //     cache_class: sfAPCCache
  //     cache_options: { }

  static public function fetchCachedFeed($url, $interval = 300)
  {
    $cacheClass = sfConfig::get('app_a_feed_cache_class', 'sfFileCache');
    $cache = new $cacheClass(sfConfig::get('app_a_feed_cache_options', array('cache_dir' => aFiles::getWritableDataFolder(array('a_feed_cache')))));
    $key = 'apostrophe:feed:' . $url;
    $feed = $cache->get($key, false);
    if ($feed === 'invalid')
    {
      return false;
    }
    else
    {
      if ($feed !== false)
      {
        // sfFeed is designed to serialize well
        $feed = unserialize($feed);
      }
    }
    if (!$feed)
    {
      try
      {
        $feed = sfFeedPeer::createFromWeb($url);    
        $cache->set($key, serialize($feed), $interval);
      }
      catch (Exception $e)
      {
        // Cache the fact that the feed is invalid for 60 seconds so we don't
        // beat the daylights out of a dead feed
        $cache->set($key, 'invalid', 60);
        return false;
      }
    }
    return $feed;
  }
}
