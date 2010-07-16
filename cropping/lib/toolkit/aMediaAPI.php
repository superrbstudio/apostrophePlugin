<?php

// DEPRECATED: based on the old REST API, which we do not enable by default and found
// to be a serious maintenance hassle as our client projects simply don't call for
// shared media repositories.

// Conveniences for Symfony code that uses the REST API.

class aMediaAPI
{
  // These two are now conveniences built on top of the 
  // new aMediaAPI object methods. The key argument has
  // been removed in favor of the simplified client key
  // discovery mechanism
  
  static public function getSelectedItem(sfRequest $request, $type = false)
  {
    $result = self::getSelectedItems($request, true, $type);
    if (is_array($result))
    {
      if (count($result))
      {
        return $result[0];
      }
    }
    return false;
  }
  
  static public function getSelectedItems(sfRequest $request, $singular = false, $type = false)
  {
    if ($singular)
    {
      if (!$request->hasParameter('aMediaId'))
      {
        return false;
      }
      $id = $request->getParameter('aMediaId');
      if (!preg_match("/^\d+$/", $id))
      {
        return false;
      }
      $ids = $id; 
    }
    else
    {
      if (!$request->hasParameter('aMediaIds'))
      {
        // User cancelled the operation in the media plugin
        return false;
      }
      $ids = $request->getParameter('aMediaIds');
      if (!preg_match("/^(\d+\,?)*$/", $ids))
      {
        // Bad input, possibly a hack attempt
        return false;
      }
    }
    $ids = explode(",", $ids);
    if ($ids === false)
    {
      // Empty list, nothing to ask for
      return array();
    }
    $api = new aMediaAPI();
    $results = $api->getItems($ids);
    if ($type !== false)
    {
      // This is intended to filter out user attempts to jam video into the list
      // of ids before we ever got to the API stage
      $nresults = array();
      foreach ($results as $result)
      {
        if ($result->type === $type)
        {
          $nresults[] = $result;
        }
      }
      $results = $nresults;
    }
    return $results;
  }
  
  public function __construct($apikey = false, $site = false)
  {
    if ($apikey === false)
    {
      $apikey = sfConfig::get('app_aMedia_client_apikey');
    }
    $this->apikey = $apikey;
    if ($site === false)
    {
      $site = sfConfig::get('app_aMedia_client_site', sfContext::getInstance()->getRequest()->getUriPrefix());
    }
    $this->site = $site;
    if ($this->site === 'http://')
    {
      throw new sfException('You are probably running a task that utilizes aMediaAPI without calling aTaskTools::setCliHost(), or you are calling aTaskTools::setCliHost() but app_cli_host is not set in app.yml. It should be set to the fully qualified domain name of your site. Alternatively you can also set app_aMedia_client_site to specify a media plugin server running on a separate site.');
    }
  }
  
  // List all media tags (returns an array of strings)
  public function getTags()
  {
    return $this->query('tags');
  }
        
  // Returns a query matching media items satisfying the specified parameters, all of which
  // are optional:
  //
  // tag
  // search
  // type (video, image or pdf)
  // user (a username, to determine access rights)
  // aspect-width and aspect-height (returns only images with the specified aspect ratio)
  // minimum-width
  // minimum-height
  // width
  // height 
  // offset (zero-based offset into complete set of results)
  // limit (max items to return, often used with offset to implement pagination)
  //
  // All parameters are optional. The server may impose a ceiling on the 
  // number of results returned even if limit is not given, but will also indicate
  // the true number of total matching items (see below).
  //
  // Matching items are returned in newest-first order unless a search parameter is present,
  // in which case they are returned in descending order by match quality.
  //
  // The response will consist of an object with two members,
  // total and items. total contains the total # of items matching the browse criteria
  // (regardless of offset and limit). items contains an array of item info in exactly the same format
  // returned by the getItems() method.
  
  public function browseItems($parameters)
  {
    return $this->query('info', $parameters);
  }
  
  public function getItems($ids)
  {
    $result = $this->query('info', array('ids' => implode(',', $ids)));
    if ($result !== false)
    {
      return $result->items;
    }
    return false;
  }

  protected $apikey;
  protected $site;
  
  protected function getUrl($action)
  {
    return $this->site . "/media/$action";
  }
  
  protected function completeParams(&$params)
  {
    $params['apikey'] = $this->apikey;
    $user = sfContext::getInstance()->getUser();
    // Send the user's username so the media plugin can decide if they are worthy of
    // performing a particular action... unless this is disabled via app.yml or
    // there is no sfGuardUser to get a username from.
    if (sfConfig::get('app_aMedia_client_send_user', true))
    {
      if ($user->isAuthenticated() && method_exists($user, 'getGuardUser'))
      {
        $params['user'] = $user->getGuardUser()->getUsername();
      }
    }
    // If the server site is explicitly specified, ask for
    // absolute URLs for images, video, etc. Otherwise relative
    // URLs are more convenient and compact
    if (sfConfig::get('app_aMedia_client_site'))
    {
      $params['absolute'] = true;
    }
  }

  protected function query($action, $params = array())
  {
    $this->completeParams($params);
    $context = stream_context_create(array(
       'http' => array(
         'method'  => 'POST',
         'header'  => "Content-type: application/x-www-form-urlencoded",
         'content' => http_build_query($params),
         'timeout' => 30,
       ),
     ));
    $url = $this->site . "/media/$action";
    $content = file_get_contents($url, false, $context);  
    sfContext::getInstance()->getLogger()->info('ZZ action: ' . $url . ' params: ' . http_build_query($params) . ' tags: ' . $content);
    
    $response = json_decode($content);
    if (!is_object($response))
    {
      return false;
    }
    if ($response->status !== 'ok')
    {
      return false;
    }
    return $response->result;    
  }
}
