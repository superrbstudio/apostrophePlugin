<?php
/**
 * Methods of this class serve static, permanent URLs that are not part of the
 * CMS address space. That would be the dynamic rendering of images that haven't
 * been cached yet, access to originals, and access to the REST API.
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaMediaBackendActions extends sfActions
{

  /**
   * DOCUMENT ME
   * @param sfRequest $request
   */
  public function executeOriginal(sfRequest $request)
  {
    $item = $this->getItem();
    $format = $request->getParameter('format');
    $mimeTypes = aMediaTools::getOption('mime_types');
    $this->forward404Unless(isset($mimeTypes[$format]));
    $path = $item->getOriginalPath($format);
    if (!file_exists($path))
    {
      // Make an "original" in the other format (conversion but no scaling)
      aImageConverter::convertFormat($item->getOriginalPath(),
        $item->getOriginalPath($format));
    }
    header("Content-length: " . filesize($item->getOriginalPath($format)));
    header("Content-type: " . $mimeTypes[$format]);
    readfile($item->getOriginalPath($format));
    // Don't let the binary get decorated with crap
    exit(0);
  }

  /**
   * Generate an image based on URL parameters taking advantage of Symfony's support
   * for . as a separator. If the image already exists an Apache mod_rewrite rule will
   * deliver it automatically and we never get this far
   * @param sfRequest $request
   */
  public function executeImage(sfRequest $request)
  {
    $item = $this->getItem();
    $slug = $item->getSlug();
    $width = $request->getParameter('width');
    $height = $request->getParameter('height');
    $resizeType = $request->getParameter('resizeType');
    $format = $request->getParameter('format');
    
    $result = $item->render(array('width' => $width, 'height' => $height, 'resizeType' => $resizeType, 'format' => $format,
      'cropLeft' => $request->getParameter('cropLeft'), 'cropTop' => $request->getParameter('cropTop'),
      'cropWidth' => $request->getParameter('cropWidth'), 'cropHeight' => $request->getParameter('cropHeight')));
    
    if (is_array($result))
    {
      // If the URL is local, deliver the image directly the first time. Then Apache
      // mod_rewrite rules should kick in to deliver it in the future without a PHP 
      // interpreter hit
      if (substr($result['url'], 0, 1) === '/')
      {
        header("Content-length: " . $result['size']);
        header("Content-type: " . $result['contentType']);
        readfile($result['path']);
        // If I don't bail out manually here I get PHP warnings,
        // even if I return sfView::NONE
        exit(0);
      }
      else
      {
        // The URL is nonlocal - the image has been kicked into S3 or similar
        // and we're ready to redirect the browser there (not the whole browser window,
        // just the img src in question). Yes you can do that these days!
        return $this->redirect($result['url']);
      }
    }
    $this->forward404(); 
  }
  
  protected $validAPIKey = false;
  // TODO: beef this up to challenge/response
  protected $user = false;

  /**
   * DOCUMENT ME
   * @return mixed
   */
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

  /**
   * DOCUMENT ME
   */
  protected function unauthorized()
  {
    header("HTTP/1.1 401 Unauthorization Required");
    exit(0);
  }

  /**
   * DOCUMENT ME
   * @param sfRequest $request
   */
  public function executeTags(sfRequest $request)
  {
    $this->validateAPIKey();
    $tags = PluginTagTable::getAllTagName();  
    $this->jsonResponse('ok', $tags);
  }

  /**
   * API to get information about an image. This has suffered some code rot since we don't
   * ever use the API anymore
   * @param sfRequest $request
   */
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
      // Absolute URL option. We no longer allow str_replace on the client side, the API
      // will need some rethinking but is not documented or used here
      $info['embed'] = $item->getEmbedCode(600, false, 's', $item->format, $absolute);
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
            "width" => "600", 
            "height" => "400", 
            "format" => "jpg", 
            "resizeType" => "c")), 
          $absolute);
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

  /**
   * DOCUMENT ME
   * @return mixed
   */
  protected function getDirectory()
  {
    return aMediaItemTable::getDirectory();
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  protected function getItem()
  {
    return aMediaTools::getItem($this);
  }

  /**
   * DOCUMENT ME
   * @param mixed $status
   * @param mixed $result
   */
  static protected function jsonResponse($status, $result)
  {
    header("Content-type: text/plain");
    echo(json_encode(array("status" => $status, "result" => $result)));
    // Don't let debug controllers etc decorate it with crap
    exit(0);
  }
}
