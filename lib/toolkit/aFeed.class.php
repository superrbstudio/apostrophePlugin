<?php
/**
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aFeed
{

  /**
   * 
   * Takes the url/routing rule of a feed and adds it to the request attributes to be read by
   * include_feeds() (see feedHelper.php), which is called in the layout. Allows for dynamic
   * inclusion of rel tags for RSS.
   * http:spindrop.us/2006/07/04/dynamic-linking-to-syndication-feeds-with-symfony/
   * @author Dave Dash (just this method)
   * Unrelated to aFeed slots.
   * @param mixed $request
   * @param mixed $feed
   */
  public static function addFeed($request, $feed)
  {
    $feeds = $request->getAttribute('helper/asset/auto/feed', array());
    
    $feeds[$feed] = $feed;
    
    $request->setAttribute('helper/asset/auto/feed', $feeds);
  }

  /**
   * Rock the Symfony cache to avoid fetching the same external URL over and over
   * These defaults are safe and boring and way faster than bashing on other servers.
   * But here's a tip. If you don't have APC enabled your site is probably running very,
   * very slowly, so fix that. And then do this for even better speed:
   * 
   * a:
   * feed:
   * cache_class: sfAPCCache
   * cache_options: { }
   * @param mixed $url
   * @param mixed $interval
   * @return mixed
   */
  static public function fetchCachedFeed($url, $interval = 300)
  {
    $cache = aCacheTools::get('feed');
    $key = $url;
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
        // We now always use the fopen adapter and specify a time limit, which is configurable.
        // Francois' comments about fopen being slow are probably dated, the stream wrappers are 
        // quite good in modern PHP and in any case Apostrophe uses them consistently elsewhere
        $options = array('adapter' => 'sfFopenAdapter', 'adapter_options' => array('timeout' => sfConfig::get('app_a_feed_timeout', 30)));
        $feed = sfFeedPeer::createFromWeb($url, $options);    
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
