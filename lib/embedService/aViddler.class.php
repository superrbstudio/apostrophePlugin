<?php

require dirname(__FILE__) . '/phpviddler/phpviddler.php';/**
 * @package    apostrophePlugin
 * @subpackage    embedService
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aViddler extends aEmbedService
{
  protected $api = null;

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function configured()
  {
    $settings = sfConfig::get('app_a_viddler');
    if (is_null($settings))
    {
      return false;
    }
    if (!isset($settings['apiKey']))
    {
      return false;
    }
    return true;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function configurationHelpUrl()
  {
    return 'http://trac.apostrophenow.org/wiki/EmbedViddler';
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  protected function getApi()
  {
    if (!is_null($this->api))
    {
      return $this->api;
    }
    $viddler = sfConfig::get('app_a_viddler', array());
    if (isset($viddler['apiKey']))
    {
      $this->api = new Viddler_V2($viddler['apiKey']);
    }
    return $this->api;
  }
  
  protected $features = array('search', 'thumbnail', 'browseUser');

  /**
   * DOCUMENT ME
   * @param mixed $feature
   * @return mixed
   */
  public function supports($feature)
  {
    return in_array($feature, $this->features);
  }

  /**
   * Fetch 100, we do our own pagination because Viddler doesn't return total items
   * @param mixed $q
   * @param mixed $page
   * @param mixed $perPage
   * @return mixed
   */
  public function search($q, $page = 1, $perPage = 50)
  {
    $results = $this->getApi()->viddler_videos_search(array('type' => 'allvideos', 'query' => $q, 'per_page' => 100, 'page' => 1));
    return $this->parseFeed($results, $page, $perPage);
  }

  /**
   * Parses results from viddler_videos_search, viddler_videos_getByUser, etc.
   * Note that we always get feeds of 100 items and then implement our own pagination
   * with array_slice. This is a workaround for the fact that Viddler doesn't offer
   * a way to get the total # of items that would match the feed if you paged far enough
   * @param mixed $results
   * @param mixed $page
   * @param mixed $perPage
   * @return mixed
   */
  protected function parseFeed($results, $page, $perPage)
  {
    if (!$results)
    {
      return false;
    }
    $infos = array();
    // Fault tolerance is important
    if (!isset($results['list_result']['video_list']))
    {
      return false;
    }
    $videos = $results['list_result']['video_list'];
    $pagedVideos = array_slice($videos, ($page - 1) * $perPage, $perPage);
    foreach ($pagedVideos as $video)
    {
      $infos[] = array('id' => (string) $video['id'], 'title' => (string) $video['title'], 'url' => (string) $video['url']);
    }
    return array('total' => count($videos), 'results' => $infos);
  }

  /**
   * Returns just enough information to verify you found the right user. This is not meant to be
   * a fancy presentation that end users see, it's for admins adding a linked account. Please don't
   * introduce English into the result here as we'd have to i18n it
   * @param mixed $user
   * @return mixed
   */
  public function getUserInfo($user)
  {
    $result = $this->getApi()->viddler_users_getProfile(array('user' => $user));
    if (!isset($result['user']))
    {
      return false;
    }
    $result = $result['user'];
    return array('name' => $result['username'] . '(' . $result['first_name'] . ' ' . $result['last_name'] . ')', 'description' => (string) $result['about_me']);
  }

  /**
   * Fetch 100, we do our own pagination because Viddler doesn't return total items
   * @param mixed $user
   * @param mixed $page
   * @param mixed $perPage
   * @return mixed
   */
  public function browseUser($user, $page = 1, $perPage = 50)
  {
    $results = $this->getApi()->viddler_videos_getByUser(array('type' => 'allvideos', 'user' => $user, 'per_page' => 100, 'page' => 1));
    return $this->parseFeed($results, $page, $perPage);
  }

  /**
   * DOCUMENT ME
   * @param mixed $id
   * @return mixed
   */
  public function getInfo($id)
  {
    $result = $this->getApi()->viddler_videos_getDetails(array('video_id' => $id));
    if (!$result)
    {
      return false;
    }
    $info = array();
    // Sometimes the viddler API decides it's fun to return something other than false
    // on failure. Just make sure there's a video in there
    if (!isset($result['video']))
    {
      return false;
    }
    $result = $result['video'];
    $info['id'] = (string) $result['id'];
    $info['url'] = (string) $result['url'];
    $info['title'] = (string) $result['title'];
    $info['description'] = (string) $result['description'];
    $info['credit'] = (string) $result['author'];
    $tags = array();
    if (isset($result['tags']))
    {
      foreach ($result['tags'] as $tag)
      {
        if ($tag['type'] === 'global')
        {
          $tags[] = $tag['text'];
        }
      }
    }
    $info['tags'] = implode(',', $tags);
    return $info;
  }

  /**
   * DOCUMENT ME
   * @param mixed $id
   * @param mixed $width
   * @param mixed $height
   * @param mixed $title
   * @param mixed $wmode
   * @param mixed $autoplay
   * @return mixed
   */
  public function embed($id, $width, $height, $title = '', $wmode = 'opaque', $autoplay = false)
  {
    $title = htmlentities($title, ENT_COMPAT, 'UTF-8');
return <<<EOM
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" alt="$title" width="$width" height="$height">
  <param name="movie" value="http://www.viddler.com/player/$id/" />
  <param name="allowScriptAccess" value="always" />
  <param name="allowFullScreen" value="true" />
  <param name="wmode" value="$wmode"></param>
  <param name="flashvars" value="fake=1"/>
  <embed src="http://www.viddler.com/player/$id/" width="$width" height="$height" type="application/x-shockwave-flash" allowScriptAccess="always" allowFullScreen="true" flashvars="fake=1" name="viddler" wmode="$wmode"></embed></object>
EOM
;
  }

  /**
   * DOCUMENT ME
   * @param mixed $url
   * @return mixed
   */
  public function getIdFromUrl($url)
  {
    // Newfangled viddler URLs have ids in them
    if (preg_match("/viddler.com\/v\/(\w+)$/", $url, $matches))
    {
      $id = $matches[1];
      return $id;
    }
    
    // OLD STYLE: Viddler is atypical in that you cannot determine the id from the URL,
    // so let's ask them
    if (preg_match("/viddler.com\/explore\//", $url))
    {
      $key = "id-from-url:$url";
      $id = $this->getCached($key);
      
      if (!is_null($id))
      {
        return $id;
      }
      
      $result = $this->getApi()->viddler_videos_getDetails(array('url' => $url));
      if (isset($result['video']['id']))
      {
        $id = $result['video']['id'];
        $this->setCached($key, $id, aEmbedService::SECONDS_IN_DAY); // Cache the information for a day
        
        return $id;
      }
    }
    
    return false;
  }

  /**
   * DOCUMENT ME
   * @param mixed $embed
   * @return mixed
   */
  public function getIdFromEmbed($embed)
  {
    if (preg_match('/viddler.com\/player\/(\w+)/', $embed, $matches))
    {
      $id = $matches[1];
      return $id;
    }
    return false;
  }

  /**
   * DOCUMENT ME
   * @param mixed $id
   * @return mixed
   */
  public function getUrlFromId($id)
  {
    $key = "url-from-id:$id";
    $url = $this->getCached($key);
    if (!is_null($url))
    {
      return $url;
    }
    $info = $this->getInfo($id);
    if (isset($info['url']))
    {
      $url = $info['url'];
      // Cache the information for a day
      $this->setCached($key, $url, aEmbedService::SECONDS_IN_DAY);
      return $url;
    }
    return false;
  }

  /**
   * DOCUMENT ME
   * @param mixed $videoid
   * @return mixed
   */
  public function getThumbnail($videoid)
  {
    $result = $this->getApi()->viddler_videos_getDetails(array('video_id' => $videoid));
    if (isset($result['video']['thumbnail_url']))
    {
      return (string) $result['video']['thumbnail_url'];
    }
    return false;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function getName()
  {
    return 'Viddler';
  }
}

