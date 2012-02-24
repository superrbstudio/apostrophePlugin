<?php
/**
 * @package    apostrophePlugin
 * @subpackage    embedService
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aVimeo extends aEmbedService
{

  /**
   * DOCUMENT ME
   */
  public function __construct()
  {
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function configured()
  {
    $settings = sfConfig::get('app_a_vimeo');
    if (is_null($settings))
    {
      return false;
    }
    if (!isset($settings['oauthConsumerKey']))
    {
      return false;
    }
    if (!isset($settings['oauthConsumerSecret']))
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
    return 'http://trac.apostrophenow.org/wiki/EmbedVimeo';
  }
  
  protected $features = array('thumbnail', 'search', 'browseUser');

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
   * @param mixed $method
   * @param mixed $params
   * @return mixed
   */
  protected function advancedCall($method, $params)
  {
    try
    {
      $settings = sfConfig::get('app_a_vimeo');
      $apikey = '';
      if (isset($settings['oauthConsumerKey']))
      {
        $apikey = $settings['oauthConsumerKey'];
      }
      $apisecret = '';
      if (isset($settings['oauthConsumerSecret']))
      {
        $apisecret = $settings['oauthConsumerSecret'];
      }
      $vimeo = new phpVimeo($apikey, $apisecret);
      return $vimeo->call($method, $params);
    } catch (Exception $e)
    {
      // TODO: it would be nice to know more although it pretty much
      // comes down to 'no such user,' 'rate limit' or 'Internet burp'
      return false;
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $q
   * @param mixed $page
   * @param mixed $perPage
   * @return mixed
   */
  public function search($q, $page = 1, $perPage = 50)
  {
    return $this->getFeed('vimeo.videos.search', array('query' => $q, 'page' => $page, 'per_page' => $perPage));
  }

  /**
   * DOCUMENT ME
   * @param mixed $user
   * @param mixed $page
   * @param mixed $perPage
   * @return mixed
   */
  public function browseUser($user, $page = 1, $perPage = 50)
  {
    return $this->getFeed('vimeo.videos.getUploaded', array('user_id' => $user, 'page' => $page, 'per_page' => $perPage));
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
    $result = $this->advancedCall('vimeo.people.getInfo', array('user_id' => $user));
    if (!$result)
    {
      return false;
    }
    if (!isset($result->person))
    {
      return false;
    }
    $result = $result->person;
    return array('name' => $result->username . ' (' . $result->display_name . ')', 'description' => $result->bio);
  }

  /**
   * DOCUMENT ME
   * @param mixed $method
   * @param mixed $params
   * @return mixed
   */
  public function getFeed($method, $params)
  {
    $params['full_response'] = 1;
    $result = $this->advancedCall($method, $params);
    $results = array('total' => $result->videos->total);
    $output = array();
    if (isset($result->videos->video))
    {
      foreach ($result->videos->video as $entry)
      {
        $output[] = array(
          'id' => $entry->id,
          'title' => $entry->title,
          'url' => $this->getUrlFromId($entry->id));
      }
    }
    $results['results'] = $output;
    return $results;
  }

  /**
   * DOCUMENT ME
   * @param mixed $id
   * @return mixed
   */
  public function getInfo($id)
  {
    $result = $this->advancedCall('vimeo.videos.getInfo', array('video_id' => $id));
    if (!$result)
    {
      return false;
    }
    if (!isset($result->video[0]))
    {
      return false;
    }
    $result = $result->video[0];
    $info = array();
    $info['id'] = $id;
    $info['url'] = $this->getUrlFromId($id);
    $info['title'] = (string) $result->title;
    $info['description'] = (string) $result->description;
    $tags = array();
    if (isset($result->tags))
    {
      foreach ($result->tags->tag as $tag)
      {
        $tags[] = $tag->_content;
      }
    }
    $info['tags'] = implode(', ', $tags);
    $info['credit'] = (string) $result->owner->display_name;
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
    // Ignore title: we can't make an iframe any more accessible, hopefully Vimeo is offering alt attributes of its own
    $id = urlencode($id);
    // Maintain a fully secure page to avoid browser warnings (vimeo just started supporting this)
    $protocol = sfContext::getInstance()->getRequest()->isSecure() ? 'https' : 'http';
    return <<<EOM
<iframe src="$protocol://player.vimeo.com/video/$id?api=1&player_id=$id&portrait=0&title=0&byline=0&autoplay=$autoplay" id="a-vimeo-video-$id" class="a-vimeo-player-iframe" width="$width" height="$height" frameborder="0"></iframe>
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
    if (preg_match("/vimeo.com(?:\/video)?\/(\d+)/", $url, $matches))
    {
      return $matches[1];
    }
    // Another vimeo format in the wild
    elseif (preg_match("/vimeo.com\/moogaloop\.swf\?clip_id=(\d+)/", $url, $matches))
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
  public function getUrlFromId($id)
  {
    return 'http://vimeo.com/' . urlencode($id);
  }

  /**
   * DOCUMENT ME
   * @param mixed $embed
   * @return mixed
   */
  public function getIdFromEmbed($embed)
  {
    return $this->getIdFromUrl($embed);
  }

  /**
   * DOCUMENT ME
   * @param string $videoid
   * @return string
   */
  public function getThumbnail($videoid)
  {
    $feed = "http://vimeo.com/api/v2/video/$videoid.json";
    $entry = json_decode(file_get_contents($feed), true);
    if (!isset($entry[0]))
    {
      // This means we're not actually allowed access to this video
      return false;
    }
    $entry = $entry[0];
    return (string) $entry['thumbnail_large'];
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function getName()
  {
    return 'Vimeo';
  }
}

