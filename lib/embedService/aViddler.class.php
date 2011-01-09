<?php

require dirname(__FILE__) . '/phpviddler/phpviddler.php';

class aViddler extends aEmbedService
{
  protected $api = null;
  
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

  public function configurationHelpUrl()
  {
    return 'http://trac.apostrophenow.org/wiki/EmbedViddler';
  }
  
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
  
  public function supports($feature)
  {
    return in_array($feature, $this->features);
  }
  
  public function search($q, $page = 1, $perPage = 50)
  {
    $results = $this->getApi()->viddler_videos_search(array('type' => 'allvideos', 'query' => $q, 'per_page' => $perPage, 'page' => $page));
    return $this->parseFeed($results, $page);
  }
  
  // Parses results from viddler_videos_search, viddler_videos_getByUser, etc.
  protected function parseFeed($results, $page)
  {
    if (!$results)
    {
      return false;
    }
    if ($results['list_result']['page'] != $page)
    {
      // Viddler gives you the last page if you ask for something beyond the last page.
      // Work around it
      return array('total' => 0, 'results' => array());
    }
    $infos = array();
    $videos = $results['list_result']['video_list'];
    foreach ($videos as $video)
    {
      $infos[] = array('id' => $video['id'], 'title' => $video['title'], 'url' => $video['url']);
    }
    // TODO find out how to get a real total of all available pages, not just the number we just asked for!
    // Right now Viddler seems not to support this
    return array('total' => count($videos), 'results' => $infos);
  }
  
  // Returns just enough information to verify you found the right user. This is not meant to be
  // a fancy presentation that end users see, it's for admins adding a linked account. Please don't
  // introduce English into the result here as we'd have to i18n it
  public function getUserInfo($user)
  {
    $result = $this->getApi()->viddler_users_getProfile(array('user' => $user));
    if (!isset($result['user']))
    {
      return false;
    }
    $result = $result['user'];
    return array('name' => $result['username'] . '(' . $result['first_name'] . ' ' . $result['last_name'] . ')', 'description' => $result['about_me']);
  }
  
  public function browseUser($user, $page = 1, $perPage = 50)
  {
    $results = $this->getApi()->viddler_videos_getByUser(array('type' => 'allvideos', 'user' => $user, 'per_page' => $perPage, $page));
    return $this->parseFeed($results, $page);
  }
  
  public function getInfo($id)
  {
    $result = $this->getApi()->viddler_videos_getDetails(array('video_id' => $id));
    if (!$result)
    {
      return false;
    }
    $info = array();
    $result = $result['video'];
    $info['id'] = $result['id'];
    $info['url'] = $result['url'];
    $info['title'] = $result['title'];
    $info['description'] = $result['description'];
    $info['credit'] = $result['author'];
    $tags = array();
    foreach ($result['tags'] as $tag)
    {
      if ($tag['type'] === 'global')
      {
        $tags[] = $tag['text'];
      }
    }
    $info['tags'] = implode(',', $tags);
    return $info;
  }

  public function embed($id, $width, $height, $title = '', $wmode = 'opaque', $autoplay = false)
  {
    $title = htmlentities($title, ENT_COMPAT, 'UTF-8');
return <<<EOM
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" alt="$title" width="$width" height="$height">
  <param name="movie" value="http://www.viddler.com/player/$id/" />
  <param name="allowScriptAccess" value="always" />
  <param name="allowFullScreen" value="true" />
  <param name="flashvars" value="fake=1"/>
  <embed src="http://www.viddler.com/player/$id/" width="$width" height="$height" type="application/x-shockwave-flash" allowScriptAccess="always" allowFullScreen="true" flashvars="fake=1" name="viddler" ></embed></object>
EOM
;
  }
  
  public function getIdFromUrl($url)
  {
    // Viddler is atypical in that you cannot determine the id from the URL,
    // so let's ask them
    if (preg_match("/viddler.com.*/", $url, $matches))
    {
      $result = $this->getApi()->viddler_videos_getDetails(array('url' => $url));
      if (isset($result['video']['id']))
      {
        return $result['video']['id'];
      }
    }
    return false;
  }

  public function getIdFromEmbed($url)
  {
    if (preg_match('/viddler.com\/player\/(\w+)/', $url, $matches))
    {
      return $matches[1];
    }
    return false;
  }
  
  public function getUrlFromId($id)
  {
    $info = $this->getInfo($id);
    if (isset($info['url']))
    {
      return $info['url'];
    }
    return false;
  }
  
  public function getThumbnail($videoid)
  {
    $result = $this->getApi()->viddler_videos_getDetails(array('video_id' => $videoid));
    if (isset($result['video']['thumbnail_url']))
    {
      return $result['video']['thumbnail_url'];
    }
    return false;
  }
  
  public function getName()
  {
    return 'Viddler';
  }
}

