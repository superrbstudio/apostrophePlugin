<?php
/**
 * @package    apostrophePlugin
 * @subpackage    embedService
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aSoundCloud extends aEmbedService
{
  protected $features = null;
  protected $apiUrl = null;
  protected $consumerKey = null;
  protected $showTypes = null;
  protected $maxPlaylistHeight = 300;
  protected $maxTrackHeight = 160;

  /**
   * Constructor for aSlideShare (obtains API key and shared secret)
   */
  public function __construct()
  {
    $settings = sfConfig::get('app_a_soundcloud');
    
    $this->features = array('thumbnail', 'search', 'browseUser');
    $this->apiUrl = 'http://api.soundcloud.com/';
        
    if (isset($settings['consumerKey']))
    {
      $this->consumerKey = $settings['consumerKey'];
    }
    if (isset($settings['maxPlaylistHeight']))
    {
      $this->maxPlaylistHeight = $settings['maxPlaylistHeight'];
    }
    if (isset($settings['maxTrackHeight']))
    {
      $this->maxTrackHeight = $settings['maxTrackHeight'];
    }
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function configured()
  {
    if (isset($this->consumerKey))
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
    return 'http://trac.apostrophenow.org/wiki/EmbedSoundCloud';
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
    /* SoundCloud returns a maximum of 50 items per set. If $perPage > 50,
     * their API will still return only 50 items. */
    $call = '';
    $offset = ($page-1)*$perPage; // Search doesn't support pages, so we must calculate an offset
    $params = array('q' => $q, 'offset' => $offset, 'limit' => 50, 'order' => 'hotness');
    
    return $this->searchApi($call, $params, $perPage);
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
    $user_id = $this->getIdFromName($user);
    $call = "$user_id/";
    $offset = ($page-1)*$perPage; // Search by user doesn't support page and items_per_page, so we must calculate an offset
    $params = array('limit' => 50, 'offset' => $offset);
    
    return $this->searchApi($call, $params, $perPage, true);
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
    // Compact treatment when the desired width is small
    if ($width < 300)
    {
      $height = 81;
    }
    else
    {
      // Newer SoundCloud makes good use of more space, Force suitable heights for
      // playlists and single tracks
      if (strpos($id, 'playlist') !== false)
      {
        $height = 163 + $this->getTrackCount($id) * 24 + 70;
      }
      else
      {
        $height = 163;
      }
    }
return <<<EOT
<iframe width="$width" height="$height" scrolling="no" frameborder="no" wmode="$wmode" src="http://w.soundcloud.com/player/?url=http%3A%2F%2Fapi.soundcloud.com%2F$id&show_artwork=true"></iframe>
EOT;
  }
// Old style embed
//<object height="81" width="$width">
//    <param name="wmode" value="$wmode"></param>
//    <param name="movie" value="http://player.soundcloud.com/player.swf?url=http%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F$id"></param>
//    <param name="allowscriptaccess" value="always"></param>
//    <embed allowscriptaccess="always" height="81" src="http://player.soundcloud.com/player.swf?url=http%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F$id" type="application/x-shockwave-flash" width="$width" wmode="$wmode"></embed>
//</object>

  public function getTrackCount($id)
  {
    $info = $this->getTrackInfo($id);
    if ($info)
    {
      return $info['trackCount'];
    }
    return false;
  }

  /**
   * DOCUMENT ME
   * @param mixed $id
   * @return mixed
   */
  public function getInfo($id)
  {
    $data = $this->getTrackInfo($id);
    
    if ($data)
    {
      return array('id' => (string) $data['id'],
           'url' => (string) $data['url'],
           'title' => (string) $data['title'],
           'description' => html_entity_decode($data['description'], ENT_COMPAT, 'UTF-8'),
           'tags' => (string) $data['tags'],
           'credit' => (string) $data['credit']);
    }
    
    return false;
  }

  /**
   * DOCUMENT ME
   * @param mixed $user
   * @return mixed
   */
  public function getUserInfo($user)
  {
    $user_id = $this->getIdFromName($user);
    $call = "$user_id";
    $data = $this->getData($call);
    
    if (!$data)
    {
      return array('name' => $user, 'description' => '');
    }
    
    $data = new SimpleXMLElement($data);
    
    return array('name' => $user, 'description' => (string) $data->user->description);
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
    
    if (strpos($url, 'soundcloud.com') !== false)
    {
      $call = 'resolve';
      $params = array('url' => $url);
            
      $data = $this->getData($call, $params);
      
      if (!$data)
      {
        return false;
      }
      
      $data = new SimpleXMLElement($data);
      
      $id = $this->getIdFromCanonicalUrl($data->uri);
      return $id;
    }
    
    return false;
  }

  /**
   * DOCUMENT ME
   * @param string $uri
   * @return string
   */
  protected function getIdFromCanonicalUrl($uri, $pass = 1)
  {
    if (preg_match('/http:\/\/api.soundcloud.com\/((?:playlists|tracks|users)\/\d+)/', $uri, $matches))
    {
      return $matches[1];
    }
    else
    {
      if ($pass === 1)
      {
        // The canonical URL might be urlencoded in an embed string, try again
        return $this->getIdFromCanonicalUrl(urldecode($uri), $pass + 1);
      }
      return false;
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $id
   * @return mixed
   */
  public function getUrlFromId($id)
  {
    $trackInfo = $this->getTrackInfo($id);
    
    if ($trackInfo)
    {
      return $trackInfo['url'];
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
    $id = $this->getIdFromCanonicalUrl($embed);
    return $id;
  }

  /**
   * DOCUMENT ME
   * @param mixed $id
   * @return mixed
   */
  public function getThumbnail($id)
  {
    $trackInfo = $this->getTrackInfo($id);
    
    if (isset($trackInfo['thumbnail']) && (strlen($trackInfo['thumbnail']) > 0))
    {
      return (string) $trackInfo['thumbnail'];
    } 
    else
    {
      
    }
    
    //var_dump($trackInfo);
    return false;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function getName()
  {
    return 'SoundCloud';
  }

  /**
   * This is the only function that actually talks to the SoundCloud API.
   * If we succeed in talking to SoundCloud we cache the response for a day,
   * regardless of what that response is. This is done to obey SoundCloud's
   * requirement that we not slam their API. You can set the 'cacheFor' option
   * to limit the caching time to a shorter number of seconds, as may be
   * appropriate when browsing content belonging to a user who is actively
   * uploading new content
   *
   * @param mixed $call
   * @param mixed $params
   * @return mixed
   */
  protected function getData($call, $params=array(), $options = array())
  {
    $params['consumer_key'] = $this->consumerKey;
    $url = $this->apiUrl . "$call?" . http_build_query($params);
    $result = $this->getCached($url);
    if ($result !== null)
    {
      return $result;
    }
    try
    {
      $result = file_get_contents($url);
    }
    catch (Exception $e)
    {
      return false;
    }
    
    if ($result)
    {
      $result = utf8_encode($result);
      $this->setCached($url, $result, isset($options['cacheFor']) ? $options['cacheFor'] : aEmbedService::SECONDS_IN_DAY);
      return $result;
    }
    
    return false;
  }

  /**
   * DOCUMENT ME
   * @param mixed $call
   * @param mixed $params
   * @param mixed $limit
   * @param mixed $browseUser
   * @return mixed
   */
  protected function searchApi($callPrefix, $params, $limit, $browseUser = false)
  {
    $soundTracks = array();
    
    $singularMap = array(
              'playlists' => 'playlist',
              'tracks' => 'track'
    );

    foreach (array('playlists', 'tracks') as $type)
    {
      $call = $callPrefix . $type;

      // Cache most searches for 5 minutes, user browsing for just 30 seconds. This is done
      // to ensure we can see it quickly if the user uploads new stuff to their own account
      $data = $this->getData($call, $params, array('cacheFor' => $browseUser ? 30 : 300));
      
      // If our API call fails, return false so we don't error on our foreach() call
      if (!$data)
      {
        return false;
      }
      
      $data = new SimpleXMLElement($data);
    
      $trackProperty = $singularMap[$type];

      foreach ($data->$trackProperty as $track)
      {
        $soundTracks[] = array(
                     'id' => $type . '/' . (int) $track->id,
                         'url' => (string) $track->{'permalink-url'},
                         'title' => (string) $track->title,
                         'description' => (string) $track->description
                         );
      }
    }
    
    // Since SoundCloud doesn't provide a total number of search results, we must paginate ourselves
    $tracks = array_slice($soundTracks, $params['offset'], $limit);
    
    return array('total' => count($soundTracks), 'results' => $tracks);
  }

  /**
   * 
   * Returns the appropriate ID for a given user or track name
   * Input can be either 'username' or 'username/trackname'
   * @param mixed $name
   * @return mixed
   */
  private function getIdFromName($name) {
    return $this->getIdFromUrl("http://soundcloud.com/$name");
  }

  /**
   * Will retrieve sound track from given ID or URL (intelligently decides which to use)
   * @param mixed $id
   * @return mixed
   */
  protected function getTrackInfo($id)
  {
    $trackInfo = null;
    
    if (!is_null($trackInfo))
    {
      return $trackInfo;
    }
    
    // If a URL was passed in, obtain the correct ID
    if (strpos($id, 'http://') !== false && preg_match('/soundcloud.com\/(\s+)/', $id, $matches))
    {
      $id = $this->getIdFromName($matches[1]);
    }
    
    if (!$id)
    {
      return false;
    }

    // id should begin with tracks/ or playlists/
    $call = "$id";
    $data = $this->getData($call);
    
    if (!$data)
    {
      return false;
    }
    
    $data = new SimpleXMLElement($data);
    
    if ($data->{'waveform-url'})
    {
      $thumbnail = $data->{'waveform-url'};
    }
    else
    {
      $thumbnail = $data->tracks->track[0]->{'waveform-url'};
    }

    $trackInfo = array(
           'id' => (int) $data->id,
           'url' => (string) $data->{'permalink-url'},
           'title' => (string) $data->title,
           'description' => (string) $data->description,
           'tags' => str_replace(' ', ',', $data->{'tag-list'}),
           'credit' => (string) $data->user->username,
           'thumbnail' => (string) $thumbnail,
           'trackCount' => count($data->tracks->track)
         );
    
    return $trackInfo;
  }

  /**
   * Return true if it is best to maintain a 16x9 aspect ratio
   * when stretching this type of embedded media
   */
  public function is16x9()
  {
    // For SoundCloud the best height depends on whether it's a 
    // playlist; for single tracks 160 pixels is about as tall as
    // you'd ever want to get
    return false;
  }
}

?>
