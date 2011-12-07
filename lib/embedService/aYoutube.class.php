<?php
/**
 * @package    apostrophePlugin
 * @subpackage    embedService
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aYoutube extends aEmbedService
{
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
   * DOCUMENT ME
   * @param mixed $q
   * @param mixed $page
   * @param mixed $perPage
   * @return mixed
   */
  public function search($q, $page = 1, $perPage = 50)
  {
    return $this->getFeed('http://gdata.youtube.com/feeds/api/videos', array('q' => $q), $page, $perPage);
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
    $xml = @simplexml_load_file('http://gdata.youtube.com/feeds/api/users/' . urlencode($user));
    if (!$xml)
    {
      return false;
    }
    $info = array();
    $namespaces = $xml->getNameSpaces(true);
    $yt = $xml->children($namespaces['yt']);
    $info['name'] = (string) $xml->author->name;
    if (isset($yt['firstName']) && isset($yt['lastName']))
    {
      $info['name'] .= '(' . $yt['firstName'] . ' ' . $yt['lastName'] . ')';
    }
    if (isset($yt['company']))
    {
      $info['name'] .= ' @' . $yt['company'];
    }
    $info['description'] = (string) $yt['aboutMe'];
    return $info;
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
    return $this->getFeed('http://gdata.youtube.com/feeds/api/users/' . urlencode($user) . '/uploads', array(), $page, $perPage);
  }

  /**
   * DOCUMENT ME
   * @param mixed $id
   * @return mixed
   */
  public function getInfo($id)
  {
    $xml = @simplexml_load_file("http://gdata.youtube.com/feeds/api/videos/$id?v=2");
    if (!$xml)
    {
      return false;
    }
    // This allows us to get at the child elements that are in the media: namespace
    $namespaces = $xml->getNameSpaces(true);
    $media = $xml->children($namespaces['media']);
    $tags = array();
    if (!isset($media->group->player))
    {
      return false;
    }

		if (sfConfig::get('app_aMedia_consume_youtube_tags', true))
		{
	    foreach ($xml->category as $category)
	    {
	      // Don't bring in non-human-friendly metadata
	      if (strpos($category['scheme'], 'keywords') !== false)
	      {
	        // Don't bring in triple tag technicalese
	        if (strpos($category['term'], ':') === false)
	        {
	          $tags[] = (string) $category['term'];
	        }
	      }
	    }
		}

   	$tags = implode(', ', $tags);
    // Why is it $media->group->description? Who knows? That's what var_dump says
    $result = array('title' => (string) $xml->title, 'description' => (string) $media->group->description, 'tags' => $tags, 'id' => $id, 'url' => $this->getUrlFromId($id), 'credit' => (string) $media->group->credit);
    return $result;
  }

  /**
   * DOCUMENT ME
   * @param mixed $feed
   * @param mixed $params
   * @param mixed $page
   * @param mixed $perPage
   * @return mixed
   */
  public function getFeed($feed, $params, $page, $perPage)
  {
    $params['start-index'] = ($page - 1) * $perPage + 1;
    $params['max-results'] = $perPage;
    // YouTube will bounce our request for the last page of results if we
    // ask for nine results and there is only one left (eg page 112 of results
    // for 'cats', which has the YouTube hard limit of 1000 results)
    if ($params['start-index'] + $params['max-results'] > 1000)
    {
      $params['max-results'] = 1000 - $params['start-index'] + 1;
    }
    $feed = $feed . '?' . http_build_query($params);
    $document = @simplexml_load_file($feed);
    if (!$document)
    {
      return false;
    }
    $namespaces = $document->getNameSpaces(true);
    $openSearch = $document->children($namespaces['openSearch']);
    $entries = $document->entry;
    // "Why no more than 1,000 results?" Because if you actually try to get at, say, page 500 of the
    // many thousands of results for "cats," YouTube gives a "sorry, YouTube does not serve more than 1,000
    // results for any query" error on the site, and appears to be similarly cutting things short
    // at the API level. There is no point in claiming more pages than you can actually browse.
    $results = array('total' => min((int) $openSearch->totalResults, 1000));
    $output = array();
    foreach ($entries as $entry)
    {
      $id = $entry->id;
      $id = strrchr($id, '/');
      if ($id === false)
      {
        continue;
      }
      $id = substr($id, 1);
      $output[] = array(
        'title' => (string) $entry->title,
        'tags' => (string) $entry->tags,
        'url' => $this->getUrlFromId($id),
        'id' => $id);
    }
    $results['results'] = $output;
    return $results;
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
    // Maintain a fully secure page to avoid browser warnings
    $protocol = sfContext::getInstance()->getRequest()->isSecure() ? 'https' : 'http';
    // wmode seems to have to be in the URL to do any good at least in Chrome
    // http://stackoverflow.com/questions/4050999/youtube-iframe-wmode-issue
    $url = "$protocol://www.youtube.com/embed/$id?" . http_build_query(array('wmode' => $wmode, 'autoplay' => $autoplay));
return <<<EOM
<iframe title="$title" width="$width" height="$height" src="$url" frameborder="0" allowfullscreen wmode="$wmode"></iframe>
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
    if (preg_match("/youtube.com.*\?.*v=([\w\-\+]+)/", $url, $matches))
    {
      return $matches[1];
    }
    elseif (preg_match("/youtube.com\/v\/([\w+\-\+]+)/", $url, $matches))
    {
      return $matches[1];
    }
    elseif (preg_match("/youtube.com\/embed\/([\w+\-\+]+)/", $url, $matches))
    {
      return $matches[1];
    }
    return false;
  }

  /**
   * DOCUMENT ME
   * @param mixed $url
   * @return mixed
   */
  public function getIdFromEmbed($url)
  {
    return $this->getIdFromUrl($url);
  }

  /**
   * DOCUMENT ME
   * @param mixed $id
   * @return mixed
   */
  public function getUrlFromId($id)
  {
    return 'http://www.youtube.com/watch?v=' . urlencode($id);
  }

  /**
   * Returns biggest thumbnail available
   * @param string $videoid
   * @return string
   */
  public function getThumbnail($videoid)
  {
    $thumbnail = false;
    $feed = "http://gdata.youtube.com/feeds/api/videos/$videoid";
    $entry = @simplexml_load_file($feed);
    if (!$entry)
    {
      return false;
    }
    // get nodes in media: namespace for media information
    $media = $entry->children('http://search.yahoo.com/mrss/');
    if (!$media)
    {
      return false;
    }
    if (!isset($media->group->player))
    {
      // Probably a geographical restriction
      return false;
    }
    // get a more canonical video player URL
    $attrs = $media->group->player->attributes();
    $canonicalUrl = $attrs['url'];
    // get biggest video thumbnail
    foreach ($media->group->thumbnail as $thumbnail)
    {
      $attrs = $thumbnail->attributes();
      if ((!isset($widest)) || (($attrs['width']  + 0) >
        ($widest['width'] + 0)))
      {
        $widest = $attrs;
      }
    }
    // The YouTube API doesn't report the original width and height of
    // the video stream, so we use the largest thumbnail, which in practice
    // generally matches the default video width that playing the video will
    // give you on YouTube. (You can't force YouTube to default to a higher
    // resolution when embedding anyway)
    if (isset($widest))
    {
      $thumbnail = $widest['url'];
      // Turn them into actual numbers instead of weird XML wrapper things
      $width = $widest['width'] + 0;
      $height = $widest['height'] + 0;
    }
    return (string) $thumbnail;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function getName()
  {
    return 'YouTube';
  }
}

