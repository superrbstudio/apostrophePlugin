<?php

class aVimeo extends aEmbedService
{
  public function __construct()
  {
  }
  
  protected $features = array('thumbnail', 'search', 'browseUser');
  
  public function supports($feature)
  {
    return in_array($feature, $this->features);
  }

  protected function advancedCall($method, $params)
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
  }
  
  public function search($q, $page = 1, $perPage = 50)
  {
    return $this->getFeed('vimeo.videos.search', array('query' => $q, 'page' => $page, 'per_page' => $perPage));
  }
  
  public function browseUser($user, $page = 1, $perPage = 50)
  {
    return $this->getFeed('vimeo.videos.getUploaded', array('user_id' => $user, 'page' => $page, 'per_page' => $perPage));
  }

  // Returns just enough information to verify you found the right user. This is not meant to be
  // a fancy presentation that end users see, it's for admins adding a linked account. Please don't
  // introduce English into the result here as we'd have to i18n it
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
    $info['title'] = $result->title;
    $info['description'] = $result->description;
    $tags = array();
    foreach ($result->tags->tag as $tag)
    {
      $tags[] = $tag->_content;
    }
    $info['tags'] = implode(', ', $tags);
    $info['credit'] = $result->owner->display_name;
    return $info;
  }
  
  public function embed($id, $width, $height, $title = '')
  {
    // Ignore title: we can't make an iframe any more accessible, hopefully Vimeo is offering alt attributes of its own
    $id = urlencode($id);
    return <<<EOM
<iframe src="http://player.vimeo.com/video/$id?portrait=0&title=0&byline=0" width="$width" height="$height" frameborder="0"></iframe>
EOM
;
  }
  
  public function getIdFromUrl($url)
  {
    if (!preg_match("/vimeo.com\/(\d+)/", 
      $url, $matches))
    {
      return false;
    }
    return $matches[1];
  }
  
  public function getUrlFromId($id)
  {
    return 'http://vimeo.com/' . urlencode($id);
  }
  
  public function getIdFromEmbed($embed)
  {
    return $this->getIdFromUrl($embed);
  }
  
  public function getThumbnail($videoid)
  {
    // TODO: validation here would be good
    $feed = "http://vimeo.com/api/v2/video/$videoid.json";
    $entry = json_decode(file_get_contents($feed), true);
    $entry = $entry[0];
    return $entry['thumbnail_large'];
  }
  
  public function getName()
  {
    return 'Vimeo';
  }
}

