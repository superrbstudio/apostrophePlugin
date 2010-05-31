<?php

// Methods of this class serve static, permanent URLs that are not part of the 
// CMS address space. That would be the dynamic rendering of images that haven't
// been cached yet, access to originals, and access to the REST API.

class BaseaMediaBackendActions extends sfActions
{
  public function executeOriginal(sfRequest $request)
  {
    $item = $this->getItem();
    $format = $request->getParameter('format');
    $this->forward404Unless(
      in_array($format, 
      array_keys(aMediaItemTable::$mimeTypes)));
    $path = $item->getOriginalPath($format);
    if (!file_exists($path))
    {
      // Make an "original" in the other format (conversion but no scaling)
      aImageConverter::convertFormat($item->getOriginalPath(),
        $item->getOriginalPath($format));
    }
    header("Content-type: " . aMediaItemTable::$mimeTypes[$format]);
    readfile($item->getOriginalPath($format));
    // Don't let the binary get decorated with crap
    exit(0);
  }

  public function executeImage(sfRequest $request)
  {
    $item = $this->getItem();
    $slug = $item->getSlug();
    $width = ceil($request->getParameter('width') + 0);
    $height = ceil($request->getParameter('height') + 0);
    $resizeType = $request->getParameter('resizeType');
    $format = $request->getParameter('format');
    $this->forward404Unless(
      in_array($format, 
      array_keys(aMediaItemTable::$mimeTypes)));
    $this->forward404Unless(($resizeType !== 'c') || ($resizeType !== 's'));
    $output = $this->getDirectory() . 
      DIRECTORY_SEPARATOR . "$slug.$width.$height.$resizeType.$format";
    // If .htaccess has not been set up, or we are not running
    // from the default front controller, then we may get here
    // even though the file already exists. Tolerate that situation 
    // with reasonable efficiency by just outputting it.
    
    if (!file_exists($output))
    {
      $originalFormat = $item->getFormat();
      if ($resizeType === 'c')
      {
        $method = 'cropOriginal';
      }
      else
      {
        $method = 'scaleToFit';
      }
      $quality = sfConfig::get('app_aMedia_jpeg_quality', 75);
      aImageConverter::$method(
        aMediaItemTable::getDirectory() .
          DIRECTORY_SEPARATOR .
          "$slug.original.$originalFormat", 
        $output,
        $width,
        $height,
        sfConfig::get('app_aMedia_jpeg_quality', 75));
    }
    // The FIRST time, we output this here. Later it
    // can come directly from the file if Apache is
    // configured with our recommended directives and
    // we're in the default controller. If we're in another
    // controller, this is still pretty efficient because
    // we don't generate the image again, but there is the
    // PHP interpreter hit to consider, so use those directives!
    header("Content-length: " . filesize($output));
    header("Content-type: " . aMediaItemTable::$mimeTypes[$format]);
    readfile($output);
      // If I don't bail out manually here I get PHP warnings,
    // even if I return sfView::NONE
    exit(0);
  }
  
  protected $validAPIKey = false;
  // TODO: beef this up to challenge/response
  protected $user = false;
  protected function validateAPIKey()
  {
    // Media API is no longer used internally and defaults to off in apostrophePlugin
    $this->forward404Unless(sfConfig::get('app_a_media_apienabled', false));
    if (!$this->hasRequestParameter('apikey'))
    {
      if (!aMediaTools::getOption("apipublic"))
      {
        $this->logMessage('info', 'flunking because no apikey');
        $this->unauthorized();
      }
      return;
    }
    $apikey = $this->getRequestParameter('apikey');
    $apikeys = array_flip(aMediaTools::getOption('apikeys'));
    if (!isset($apikeys[$apikey]))
    {
      $this->logMessage('info', 'ZZ flunking because bad apikey');      
    }
    $this->forward404Unless(isset($apikeys[$apikey]));
    $this->validAPIKey = true;
    $this->user = false;
    if ($this->validAPIKey)
    {
      // With a valid API key you can request media info on behalf of any user
      $this->user = $this->getRequestParameter('user');
    }
    if (!$this->user)
    {
      // Use of the API from javascript as an already authenticated user
      // is permitted
      if ($this->getUser()->isAuthenticated())
      {
        $guardUser = $this->getUser()->getGuardUser();
        if ($guardUser)
        {
          $this->user = $guardUser->getUsername();
        }
      }
    }
  }
  
  protected function unauthorized()
  {
    header("HTTP/1.1 401 Unauthorization Required");
    exit(0);
  }
  
  public function executeTags(sfRequest $request)
  {
    $this->validateAPIKey();
    $tags = PluginTagTable::getAllTagName();  
    $this->jsonResponse('ok', $tags);
  }
  
  public function executeInfo(sfRequest $request)
  {
    $params = array();
    $this->validateAPIKey();
    
    if ($request->hasParameter('ids'))
    {
			$ids = $request->getParameter('ids');
      if (!preg_match("/^(\d+\,?)*$/", $ids))
      {
        // Malformed request
        $this->jsonResponse('malformed');
      }
      $ids = explode(",", $ids);
      if ($ids === false)
      {
        $ids = array();
      }
      $params['ids'] = $ids;
    }
    
    $numbers = array(
      "width", "height", "minimum-width", "minimum-height", "aspect-width", "aspect-height"
    );
    foreach ($numbers as $number)
    {
      if ($request->hasParameter($number))
      {
        $n = $request->getParameter($number) + 0;
        if ($number < 0)
        {
          $n = 0;
        }
        $params[$number] = $n;
      }
    }
    $strings = array(
      "tag", "search", "type", "user"
    );
    foreach ($strings as $string)
    {
      if ($request->hasParameter($string))
      {
        $params[$string] = $request->getParameter($string);
      }
    }    
    if (isset($params['tag']))
    {
      $this->logMessage("ZZZZZ got tag: " . $params['tag'], "info");
    }
    $query = aMediaItemTable::getBrowseQuery($params);
    $countQuery = clone $query;
    $countQuery->offset(0);
    $countQuery->limit(0);
    $result = new StdClass();
    $result->total = $countQuery->count();
    
    if ($request->hasParameter('offset'))
    {
      $offset = max($request->getParameter('offset') + 0, 0);
      $query->offset($offset);
    }
    if ($request->hasParameter('limit'))
    {
      $limit = max($request->getParameter('limit') + 0, 0);
      $query->limit($limit);
    }
    $absolute = !!$request->getParameter('absolute', false);
    $items = $query->execute();
    $nitems = array();
    foreach ($items as $item)
    {
      $info = array();
      $info['type'] = $item->getType();
      $info['id'] = $item->getId();
      $info['slug'] = $item->getSlug();
      $info['width'] = $item->getWidth();
      $info['height'] = $item->getHeight();
      $info['format'] = $item->getFormat();
      $info['title'] = $item->getTitle();
      $info['description'] = $item->getDescription();
      $info['credit'] = $item->getCredit();
      $info['tags'] = array_keys($item->getTags());
      // The embed HTML we suggest is a template in which they can
      // replace _WIDTH_ and _HEIGHT_ and _c-OR-s_ with
      // whatever they please
      
      // Absolute URL option
      $info['embed'] = $item->getEmbedCode('_WIDTH_', '_HEIGHT_', '_c-OR-s_', '_FORMAT_', $absolute);
      // The image URL we suggest is a template in which they can
      // replace _WIDTH_, _HEIGHT_, _c-OR-s_ and _FORMAT_ with
      // whatever they please
      $controller = sfContext::getInstance()->getController();
      
      // Must use keys that will be acceptable as property names, no hyphens!
      
      // original refers to the original file, if we ever had it
      // (images and PDFs). If you ask for the original of a video, you
      // currently get the media plugin's copy of the best available still. 
      
      $info['original'] = $controller->genUrl("@a_media_original?" .
        http_build_query(
          array(
            "slug" => $item->getSlug(),
            "format" => $item->getFormat()), $absolute));

      $info['image'] = $controller->genUrl("a_media_image?" .
        http_build_query(
          array(
            "slug" => $item->getSlug(),
            "width" => "1000001", 
            "height" => "1000002", 
            "format" => "jpg", 
            "resizeType" => "c")), 
          $absolute);
      $info['image'] = str_replace(array("1000001", "1000002", ".c."),
        array("_WIDTH_", "_HEIGHT_", "._c-OR-s_."), $info['image']);
      $info['image'] = preg_replace("/\.jpg$/", "._FORMAT_", $info['image']);
      if ($info['type'] === 'video')
      {
        $info['serviceUrl'] = $item->getServiceUrl();
      }
      $nitems[] = $info;
    }
    $result->items = $nitems;
    $this->jsonResponse('ok', $result);
  }
  
  protected function getDirectory()
  {
    return aMediaItemTable::getDirectory();
  }
  
  protected function getItem()
  {
    return aMediaTools::getItem($this);
  }
  
  static protected function jsonResponse($status, $result)
  {
    header("Content-type: text/plain");
    echo(json_encode(array("status" => $status, "result" => $result)));
    // Don't let debug controllers etc decorate it with crap
    exit(0);
  }
}
