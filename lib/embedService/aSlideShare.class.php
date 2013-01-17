<?php
/**
 * @package    apostrophePlugin
 * @subpackage    embedService
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aSlideShare extends aEmbedService
{
  protected $features = null;
  protected $apiUrl = null;
  protected $apiKey = null;
  protected $sharedSecret = null;
  protected $showTypes = null;

  /**
   * Constructor for aSlideShare (obtains API key and shared secret)
   */
  public function __construct()
  {
    $settings = sfConfig::get('app_a_slideshare');
    
    $this->features = array('thumbnail', 'search', 'browseUser');
    $this->apiUrl = 'https://www.slideshare.net/api/2/';
    
    /* The following SlideShare show types correspond to the given numbers (which are pulled from their API):
     *    Presentation => 0
     *    Document   => 1
     *    Portfolio  => 2
     *    Video    => 3
     *
     * Each type of show requires a different SWF player to embed it properly, so if we pass in the numerical type of the
     * show in question, we will receive the correct player to use.
     */
    $this->showPlayers = array('ssplayer2.swf', 'doc_player.swf', 'ssplayer2.swf', 'playerv.swf');
    
    if (isset($settings['apiKey']))
    {
      $this->apiKey = $settings['apiKey'];
    }
    
    if (isset($settings['sharedSecret']))
    {
      $this->sharedSecret = $settings['sharedSecret'];
    }
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function configured()
  {
    if (isset($this->apiKey) && isset($this->sharedSecret))
    {
      return true;
    }
    
    return false;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function configurationHelpUrl()
  {
    return 'http://trac.apostrophenow.org/wiki/EmbedSlideShare';
  }

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
   * DOCUMENT ME
   * @param mixed $q
   * @param mixed $page
   * @param mixed $perPage
   * @return mixed
   */
  public function search($q, $page=1, $perPage=50)
  {
    $call = 'search_slideshows';
    $params = array('q' => $q, 'page' => $page, 'items_per_page' => $perPage);
    
    return $this->searchApi($call, $params);
  }

  /**
   * DOCUMENT ME
   * @param mixed $user
   * @param mixed $page
   * @param mixed $perPage
   * @return mixed
   */
  public function browseUser($user, $page=1, $perPage=50)
  {
    $call = 'get_slideshows_by_user';
    $offset = ($page-1)*$perPage; // Search by user doesn't support page and items_per_page, so we must calculate an offset
    $params = array('username_for' => $user, 'limit' => $perPage, 'offset' => $offset);
    
    return $this->searchApi($call, $params, true);
  }

  /**
   * DOCUMENT ME
   * @param mixed $user
   * @return mixed
   */
  public function getUserInfo($user)
  {
    // SlideShare has no API call to retrieve user information (only their groups, slideshows, and contacts)
    return array('name' => $user, 'description' => '');
  }

  /**
   * DOCUMENT ME
   * @param mixed $id
   * @return mixed
   */
  public function getInfo($id)
  {
    $data = $this->getSlideInfo($id);
    
    if ($data)
    {
      return array('id' => $data['id'],
           'url' => $data['url'],
           'title' => $data['title'],
           'description' => html_entity_decode($data['description'], ENT_COMPAT, 'UTF-8'),
           'tags' => $data['tags'],
           'credit' => $data['credit']);
    }
    
    return false;
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
  public function embed($id, $width, $height, $title='', $wmode='opaque', $autoplay=false)
  {
    $slideInfo = $this->getSlideInfo($id);
    
    if ($slideInfo)
    {
      $player = $this->showPlayers[$slideInfo['showType']];

return <<<EOT
<iframe src="https://www.slideshare.net/slideshow/embed_code/$id?rel=0&wmode=$wmode" width="$width" height="$height" frameborder="0" marginwidth="0" marginheight="0" scrolling="no"></iframe> 
EOT;
    }
    
    return false;
}

  /**
   * DOCUMENT ME
   * @param mixed $url
   * @return mixed
   */
  public function getIdFromUrl($url)
  {    
    // Apostrophe calls both this and the getIdFromEmbed method,
    // let's make sure it happens in the cheapest order, avoiding
    // unnecessary API calls and ruling out situations where we
    // would have a false positive on an embed code as a URL
    $id = $this->getIdFromEmbed($url);
    
    if ($id)
    {
      return $id;
    }
    
    if (strpos($url, 'slideshare.net') !== false)
    {
      /* We must strip the '?from=ss_embed' suffix off the SlideShare URL if it exists (this suffix shows up
       * when a user gets to the SlideShare page by clicking the 'View on SlideShare' button within a slideshow) */
      if (strpos($url, '?from=ss_embed') !== false)
      {
        $url = substr($url, 0, strpos($url, '?from=ss_embed'));
      }
      
      $slideInfo = $this->getSlideInfo($url);
      
      if ($slideInfo)
      {
        return $slideInfo['id'];
      }
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
    $slideInfo = $this->getSlideInfo($id);
    
    if ($slideInfo)
    {
      return $slideInfo['url'];
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
    // New style
    if (preg_match('/slideshare.net.*?\/embed_code\/(\w+)/', $embed, $matches))
    {
      return $matches[1];
    }
    // Old style
    if (preg_match('/__sse(\d+)/', $embed, $matches))
    {
      return $matches[1];
    }
    
    return false;
  }

  /**
   * DOCUMENT ME
   * @param mixed $id
   * @return mixed
   */
  public function getThumbnail($id)
  {
    $slideInfo = $this->getSlideInfo($id);
    if (isset($slideInfo['thumbnail']) && (strlen($slideInfo['thumbnail']) > 0))
    {
      return $this->addProtocol($slideInfo['thumbnail']);
    }
    
    return false;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function getName()
  {
    return 'SlideShare';
  }

  /**
   * This is the only function that actually talks to the SlideShare API
   * @param mixed $call
   * @param mixed $params
   * @return mixed
   */
  protected function getData($call, $params=array())
  {
    $timeStamp = time();
    $hash = sha1($this->sharedSecret.$timeStamp);
    
    try
    {
      $params['api_key'] = $this->apiKey;
      $params['ts'] = $timeStamp;
      $params['hash'] = $hash;
      $url = $this->apiUrl . "$call?" . http_build_query($params);
      $result = file_get_contents($url);
    }
    catch (Exception $e)
    {
      return false;
    }
    
    if ($result)
    {
      return utf8_encode($result);
    }
    
    return false;
  }

  /**
   * #1238 Slideshare recently stopped specifying http or https in an effort to
   * give you a choice, of course this is a bc break in a live API which was
   * a silly thing for them to do
   */
  protected function addProtocol($url)
  {
    if (!preg_match('/^https?:/', $url))
    {
      $url = 'https:' . $url;
    }
    return $url;
  }

  /**
   * DOCUMENT ME
   * @param mixed $call
   * @param mixed $params
   * @param mixed $browseUser
   * @return mixed
   */
  protected function searchApi($call, $params, $browseUser=false)
  {
    $slideshowInfo = array();
    
    $data = $this->getData($call, $params);
    
    // If our API call fails, return false so we don't error on our foreach() call
    if (!$data)
    {
      return false;
    }
    
    $data = new SimpleXMLElement($data);
    
    foreach ($data->Slideshow as $show)
    {
      $slideshowInfo[] = array('id' => (int) $show->ID,
                     'url' => (string) $show->URL,
                     'title' => (string) $show->Title,
                     'description' => (string) $show->Description);
    }
    
    if ($browseUser)
    {
      return array('total' => (int) $data->User->Count, 'results' => $slideshowInfo);
    }
    else
    {
      return array('total' => (int) $data->Meta->TotalResults, 'results' => $slideshowInfo);
    }
  }

  /**
   * Will retrieve slideshow from given ID or URL (intelligently decides which to use)
   * @param mixed $id
   * @return mixed
   */
  protected function getSlideInfo($id)
  {
    // Check if we have the media cached before hitting the API
    $cacheKey = "get-slideinfo:$id";
    $slideInfo = $this->getCached($cacheKey);
    
    if (!is_null($slideInfo))
    {
      return $slideInfo;
    }  
  
    $call = 'get_slideshow';
    $tags = '';
    $params = (strpos($id, 'http://') !== false) ? array('slideshow_url' => $id, 'detailed' => 1) : array('slideshow_id' => $id, 'detailed' => 1);

    $data = $this->getData($call, $params);
    
    // If our API call fails, return false so we don't error on our foreach() call
    if (!$data)
    {
      return false;
    }
    
    $data = new SimpleXMLElement($data);

    if (!isset($data->ID))
    {
      // Probably gone
      return false;
    }

    // Convert tags into comma-separated list
    if (isset($data->Tags->Tag))
    {
      foreach ($data->Tags->Tag as $tag)
      {
        $tags .= $tag . ', ';
      }
    }
    
    if (strlen($tags) > 0)
    {
      $tags = substr($tags, 0, -2); // Remove the trailing comma
    }
    
    $slideInfo = array(
         'id' => (int) $data->ID,
           'url' => (string) $data->URL,
           'title' => (string) $data->Title,
           'description' => (string) $data->Description,
           'tags' => $tags,
           'credit' => (string) $data->Username,
           'thumbnail' => (string) $data->ThumbnailURL,
           'embedUrl' => (string) $data->PPTLocation,
           'showType' => (int) $data->SlideshowType
         );
    
    // Cache this media for a day to reduce our API hits
    $this->setCached($cacheKey, $slideInfo, aEmbedService::SECONDS_IN_DAY);
    
    return $slideInfo;
  }
}

?>
