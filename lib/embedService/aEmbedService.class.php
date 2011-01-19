<?php

abstract class aEmbedService
{
  // If the service is properly configured (any necessary api keys are in app.yml etc),
  // return true, otherwise false. By default we return true (a service that requires no
  // configuration, like the YouTube service)
  public function configured()
  {
    return true;
  }

  // If configured() returns false, this must return the URL of a page that provides help
  // on correctly configuring the Apostrophe site to support your service
  // example: http://trac.apostrophenow.org/wiki/EmbedVimeo
  
  public function configurationHelpUrl()
  {
    return null;
  }
  
  // Return true or false depending on the value of $feature, which can be
  // browseUser, search or thumbnail
  abstract public function supports($feature);

  // WARNING: most APIs limit items per page. Don't go over 50

  // search and browseUser return results in the same format:
  //
  // array('total' => 500, 'results' => array(array('id' => 5555, 'url' => 'http://www.example.com/videos/5555', 'title' => 'title of video', 'description' => 'longer description')))
  // 'total' should be the OVERALL total, not just the total shown on this page of results
  // return false only if search is not available
  
  // Optional
  public function search($q, $page = 1, $perPage = 50)
  {
    return array('total' => 0, 'results' => array());
  }

  // Optional
  // You really should implement this otherwise linked accounts are not possible for this service
  // See search() above for return value
  
  public function browseUser($user, $page = 1, $perPage = 50)
  {
    return array('total' => 0, 'results' => array());
  }

  // Return format:
  // array('name' => 'bobsmith (Bob Smith) @fancyco inc', 'description' => 'long description of user')
  // Both parameters are required. description may be empty. The name can be as simple as returning
  // $user if you can't get more information
  // Do not introduce English phrases unless you I18N them.
  
  abstract public function getUserInfo($user);

  // Return format:
  // array('id' => 5555, 'url' => 'http://www.example.com/videos/5555', 'title' => 'title of video', 'description' => 'descripton of video', 'tags' => 'comma, separated, tags', 'credit' => 'bobsmith')

  abstract public function getInfo($id);

  // Returns markup to embed the media in question. The title should be an alt attribute, not visible unless
  // the media cannot be rendered. If you can't present it that way, just don't
  
  abstract public function embed($id, $width, $height, $title = '', $wmode = 'opaque', $autoplay = false );

  // Converts a service URL to a service ID. MUST return false if the service URL
  // is not for this service
  abstract public function getIdFromUrl($url);

  // Returns a service URL for a service ID
  abstract public function getUrlFromId($id);

  // Returns a service ID for an embed code. MUST return false if the embed code
  // is not for this service
  abstract public function getIdFromEmbed($embed);

  // Returns a URL for the largest available thumbnail of the item. If there
  // is no thumbnail available it should return false
  public function getThumbnail($id)
  {
    return false;
  }

  // Returns the name of the service, suitable for use in a menu of services -
  // this should just be 'YouTube' or "Vimeo," not something long and annoying
  abstract public function getName();

  // Used to hold the Apostrophe type under which newly added items
  // of this type should be filed. Shouldn't be hardcoded, it's configurable
  // in app.yml, so just let these stand
  public function setType($type)
  {
    $this->type = $type;
  }

  public function getType()
  {
    return $this->type;
  }
  
  // Rock the Symfony cache to avoid fetching the same external data over and over.
  // This helps avoid beating up on external APIs while still allowing you to remain
  // current by not having an overly long (or permanent) cache lifetime. 
  
  // The default setup is safe and boring and way faster than bashing on other servers.
  // But here's a tip. If you don't have APC enabled your site is probably running very, 
  // very slowly, so fix that. And then do this for even better speed:
  //
  // a:
  //   embed:
  //     cache_class: sfAPCCache
  //     cache_options: { }
  //
  // Returns null if the cache does not have the named item.
  // That works well for our API since we generally use false to mean
  // something is actually not valid
  
  const SECONDS_IN_DAY = 86400;
  protected $cache;
  
  public function getCached($key)
  {
    $cache = $this->getCache();
    $key = $this->getName() . ':' . $key;
    $value = $cache->get($key, null);
    if ($value === null)
    {
      return null;
    }
    return unserialize($value);
  }

  // Interval (lifetime) is in seconds
  public function setCached($key, $value, $interval=3600)
  {
    $cache = $this->getCache();
    $key = $this->getName() . ':' . $key;
    $cache->set($key, serialize($value), $interval);
  }
  
  private function getCache()
  {
    if ($this->cache)
    {
      return $this->cache;
    }
    $cacheClass = sfConfig::get('app_a_embed_cache_class', 'sfFileCache');
    $this->cache = new $cacheClass(sfConfig::get('app_a_embed_cache_options', array('cache_dir' => aFiles::getWritableDataFolder(array('a_embed_cache')))));
    return $this->cache;
  }
}
