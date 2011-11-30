<?php
/**
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaMediaTools
{

  /**
   * These are used internally. See aMediaSelect for the methods you probably want
   * @param mixed $after
   * @param mixed $multiple
   * @param mixed $selection
   * @param mixed $options
   */
  static public function setSelecting($after, $multiple, $selection, 
    $options = array())
  {
    $items = aMediaItemTable::retrieveByIds($selection);
    $ids = array();
    $imageInfo = array();
    $selection = array();
    foreach ($items as $item)
    {
      $croppingInfo = array();
      if ($item->isCrop())
      {
        $croppingInfo = $item->getCroppingInfo();
        $item = $item->getCropOriginal();
      }
      $id = $item->id;
      $selection[] = $id;
      $info = array('width' => $item->width, 'height' => $item->height);
      $info = array_merge($info, $croppingInfo);
      $imageInfo[$item->id] = $info;
    }
    
    $cropping = isset($options['cropping']) && $options['cropping'];

    aMediaTools::clearSelecting();
    aMediaTools::setAttribute("selecting", true);
    aMediaTools::setAttribute("after", $after);
    aMediaTools::setAttribute("multiple", $multiple);
    aMediaTools::setAttribute("cropping", $cropping);
    aMediaTools::setAttribute("selection", $selection);
    aMediaTools::setAttribute("imageInfo", $imageInfo);
    foreach ($options as $key => $val)
    {
      aMediaTools::setAttribute($key, $val);
    }
    $type = aMediaTools::getType();
    if (substr($type, 0, 1) === '_')
    {
      // We need to let people filter more narrowly, but also
      // be able to remember what the metatype was originally
      aMediaTools::setAttribute('metatype', $type);
    }
  }

  /**
   * DOCUMENT ME
   */
  static public function clearSelecting()
  {
    aMediaTools::removeAttributes();
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function isSelecting()
  {
    return aMediaTools::getAttribute("selecting");
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function isMultiple()
  {
    return aMediaTools::getAttribute("multiple");
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function getSelection()
  {
    return aMediaTools::getAttribute("selection", array());
  }

  /**
   * DOCUMENT ME
   * @param mixed $array
   */
  static public function setSelection($array)
  {
    aMediaTools::setAttribute("selection", $array);
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function getAfter()
  {
    return aMediaTools::getAttribute("after");
  }

  /**
   * DOCUMENT ME
   * @param mixed $item
   * @return mixed
   */
  static public function isSelected($item)
  {
    if (is_object($item))
    {
      $id = $item->id;
    }
    else
    {
      $id = $item;
    }
    $selection = aMediaTools::getSelection();
    return (array_search($id, $selection) != false);
  }

  /**
   * DOCUMENT ME
   * @param mixed $array
   */
  static public function setSearchParameters($array)
  {
    aMediaTools::setAttribute("search-parameters", $array); 
  }

  /**
   * DOCUMENT ME
   * @param mixed $default
   * @return mixed
   */
  static public function getSearchParameters($default = false)
  {
    if ($default === false)
    {
      $default = array();
    }
    return aMediaTools::getAttribute("search-parameters", $default);
  }

  /**
   * DOCUMENT ME
   * @param mixed $p
   * @param mixed $default
   * @return mixed
   */
  static public function getSearchParameter($p, $default = false)
  {
    $parameters = aMediaTools::getSearchParameters();
    if (isset($parameters[$p]))
    {
      return $parameters[$p];
    }
    return $default;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function getType()
  {
    return aMediaTools::getAttribute('type');
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function getMetatype()
  {
    return aMediaTools::getAttribute('metatype');
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function getBestTypeLabel()
  {
    $type = aMediaTools::getType();
    if ($type)
    {
      if ($type === '_downloadable')
      {
        return 'File';
      }
      elseif (substr($type, 0, 1))
      {
        return 'Media';
      }
      $typeInfo = aMediaTools::getTypeInfo($type);
      return $typeInfo['label'];
    }
    else
    {
      return 'Media';
    }
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function userHasUploadPrivilege()
  {
    $user = sfContext::getInstance()->getUser();
    if (!$user->isAuthenticated())
    {
      return false;
    }
    $uploadCredential = aMediaTools::getOption('upload_credential');
    if ($uploadCredential)
    {
      $has = $user->hasCredential($uploadCredential);
      return $has;
    }
    else
    {
      return true;
    }
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function userHasAdminPrivilege()
  {
    $user = sfContext::getInstance()->getUser();
    if (!$user->isAuthenticated())
    {
      return false;
    }
    $adminCredential = aMediaTools::getOption('admin_credential');
    if ($adminCredential)
    {
      return $user->hasCredential($adminCredential);
    }
    else
    {
      return true;
    }
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static protected function getUser()
  {
    return sfContext::getInstance()->getUser();
  }

  /**
   * DOCUMENT ME
   * @param mixed $attribute
   * @param mixed $default
   * @return mixed
   */
  static public function getAttribute($attribute, $default = null)
  {
    $attribute = "aMedia-$attribute";
    return aMediaTools::getUser()->getAttribute($attribute, $default, 'apostrophe_media');
  }

  /**
   * DOCUMENT ME
   * @param mixed $attribute
   * @param mixed $value
   */
  static public function setAttribute($attribute, $value = null)
  {
    $attribute = "aMedia-$attribute";
    aMediaTools::getUser()->setAttribute($attribute, $value, 'apostrophe_media');
  }

  /**
   * DOCUMENT ME
   */
  static public function removeAttributes()
  {
    $user = aMediaTools::getUser();
    $user->getAttributeHolder()->removeNamespace('apostrophe_media');
  }
  
  // This is a good convention for plugin options IMHO
  static protected $options = array(
    "batch_max" => 6,
    "per_page" => 20,
    'linked_accounts' => true,
    "popular_tags" => 10,
    "video_search_per_page" => 9,
    "video_search_preview_width" => 220,
    "video_search_preview_height" => 170,
    "video_account_preview_width" => 220,
    "video_account_preview_height" => 170,
    "upload_credential" => "media_upload",
    "admin_credential" => "media_admin",
    "gallery_constraints" => array(
        "width" => 340,
        "height" => false,
        "resizeType" => "s"),
    "selected_constraints" => array(
        "width" => false,
        "height" => 75,
        "resizeType" => "s"),
    "show_constraints" => array(
        "width" => 720,
        "height" => false,
        "resizeType" => "s"),
    "crop_constraints" => array(
        "width" => 679,
        "height" => 400,
        "resizeType" => "s"),
    'routes_register' => true,
    'apipublic' => false,
    'embed_codes' => false,
    'apikeys' => array(),
    'enabled_layouts' => array('one-up', 'two-up', 'four-up'),
    // All mime types that are acceptable for upload to the media repository,
    // keyed by the file extensions we save them under (regardless of the original name)
    
    'mime_types' => array(
      "gif" => "image/gif",
      "png" => "image/png",
      "jpg" => "image/jpeg",
      "pdf" => "application/pdf",
      "mp3" => "audio/mpeg",
      'xls' => 'application/vnd.ms-excel',
      'ppt' => 'application/vnd.ms-powerpoint',
      'doc' => 'application/msword',
      'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
      'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
      'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
      'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
      'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
      'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
      'txt' => 'text/plain',
      'rtf' => 'text/rtf'
      ),
      
    // You can override these to add more types to the system. These are the 
    // major types one can filter by in the media repository. Adding something here
    // doesn't necessarily mean browsers can display it or our slots are designed
    // to render it, in particular don't add new audio formats to 'audio' without
    // overriding our audio slots to play them (and keep in mind the browser probably
    // knows nothing about them)
    
    // Video has no extensions because we don't provide processing of video uploads,
    // which are in a dizzying array of formats most browsers won't play. That's why
    // YouTube exists. Videos are brought into the system via "Embed Media," not "Upload Media"

    // Typically the only section you'll override here is 'file'. You can add more
    // accepted extensions and/or break it up into 'Office' and 'Other' etc

    // Also see 'getDownloadable' and 'getEmbeddable' in aMediaItem

    // Also override the enum in schema.yml if you have configured Doctrine to use
    // database-level enums rather than strings to represent Doctrine enums
    
    'types' => array(
      // You must have an image type
      'image' => array('label' => 'Image', 'extensions' => array('gif', 'png', 'jpg'), 'embeddable' => false, 'downloadable' => true),
      'pdf' => array('label' => 'PDF', 'extensions' => array('pdf'), 'embeddable' => false, 'downloadable' => true),
      'audio' => array('label' => 'Audio', 'extensions' => array('mp3'), 'embeddable' => false, 'downloadable' => true),
      // You must have a video type
      // embedServices list is not actually consulted in 1.5, all embedServices are considered video for now
      'video' => array('label' => 'Video', 'extensions' => array(), 'embeddable' => true, 'downloadable' => false, 'embedServices' => array('YouTube', 'Vimeo', 'Viddler')),
      
      // A long whitelist of file formats that are usually benign and useful.
      // No .exe, no .zip. You can add them via app.yml if you really want them.
      // We list only the non-macro-enabled Microsoft extensions in an effort to
      // honor their good-faith attempt to label more dangerous files
      
      'office' => array('label' => 'Office', 'extensions' => array('txt', 'rtf', 'csv', 'doc', 'docx', 'xls', 'xlsx', 'xlsb', 'ppt', 'pptx', 'ppsx'), 'embeddable' => false, 'downloadable' => true)),
    'embed_services' => array(
      // media_type is not consulted yet in 1.5
      array('class' => 'aYoutube', 'media_type' => 'video'),
      array('class' => 'aVimeo', 'media_type' => 'video'),
      array('class' => 'aViddler', 'media_type' => 'video'),
      array('class' => 'aSlideShare', 'media_type' => 'video'),
      array('class' => 'aSoundCloud', 'media_type' => 'video'),
    ));

  static protected $layouts = array(
    'one-up' => array(
        "name" => "one-up",
        "image" => "/apostrophePlugin/images/a-icon-media-single.png",
        "gallery_constraints" => array(
          "width" => 340,
          "height" => false,
          "resizeType" => "s"),
        "show_constraints" => array(
            "width" => 720,
            "height" => false,
            "resizeType" => "s"),
        "columns" => 1,
        "fields" => array("controls" => 1,"thumbnail" => 1,"title" => 1, "description" => 1, 'dimensions'=> 1, "credit" => 1, "categories" => 1, "tags" => 1, 'view_is_secure' => 1, 'link' => 1, 'downloadable' => 1)
      ),
    'two-up' => array(
        "name" => "two-up",
        "image" => "/apostrophePlugin/images/a-icon-media-two-up.png",
        "gallery_constraints" => array(
          "width" => 340,
          "height" => false,
          "resizeType" => "s"),
        "show_constraints" => array(
            "width" => 720,
            "height" => false,
            "resizeType" => "s"),
        "columns" => 2,
        "fields" => array("controls" => 1,"thumbnail" => 1,"title" => 1, "description" => 1, 'dimensions' => 1, "credit" => 1, "categories" => 1, "tags" => 1, 'view_is_secure' => 1, 'link' => 1, 'downloadable' => 1)
      ),
      'four-up' => array(
        "name" => "four-up",
        "image" => "/apostrophePlugin/images/a-icon-media-grid.png",
        "gallery_constraints" => array(
          "width" => 340,
          "height" => false,
          "resizeType" => "s"),
        "show_constraints" => array(
            "width" => 720,
            "height" => false,
            "resizeType" => "s"),
        "columns" => 4,
        "fields" => array("controls" => 1,"thumbnail" => 1,'title' => 1)
      ),
      'thumbnail' => array(
        "name" => "thumbnail",
        "image" => "a-media-browse-thumbnail.png",
        "gallery_constraints" => array(
          "width" => 85,
          "height" => false,
          "resizeType" => "s"),
        "show_constraints" => array(
            "width" => 720,
            "height" => false,
            "resizeType" => "s"),
        "columns" => 8,
        "fields" => array("thumbnail" => 1)
      )
    );

  /**
   * DOCUMENT ME
   * @param mixed $name
   * @return mixed
   */
  static public function getOption($name)
  {
    if (isset(aMediaTools::$options[$name]))
    {
      $name = preg_replace("/[^\w]/", "", $name);
      $key = 'app_aMedia_'.$name;
      return sfConfig::get($key, aMediaTools::$options[$name]);
    }
    else
    {
      throw new Exception("Unknown option in apostrophePlugin: $name");
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $name
   * @return mixed
   */
  static public function getLayout($name)
  {
    return aMediaTools::$layouts[$name];
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function getEnabledLayouts()
  {
    return array_intersect_key(aMediaTools::$layouts, array_flip(aMediaTools::getOption('enabled_layouts')));
  }

  /**
   * DOCUMENT ME
   * @param mixed $name
   * @return mixed
   */
  static public function getTypeInfo($name)
  {
    $types = aMediaTools::getOption('types');
    return $types[$name];
  }

  /**
   * Returns an array of type infos, just the one if you specify a type, all if you don't.
   * Handy when filtering
   * @param mixed $type
   * @return mixed
   */
  static public function getTypeInfos($type = null)
  {
    if (preg_match('/^_(\w+)$/', $type, $matches))
    {
      $attribute = $matches[1];
      $infos = aMediaTools::getTypeInfos();
      $withAttribute = array();
      foreach ($infos as $name => $info)
      {
        if (isset($info[$attribute]) && $info[$attribute])
        {
          $withAttribute[$name] = $info;
        }
      }
      return $withAttribute;
    }
    $types = aMediaTools::getOption('types');
    if (is_null($type))
    {
      return $types;
    }
    return array($type => $types[$type]);
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function getEmbedAllowed()
  {
    foreach (aMediaTools::getTypeInfos(aMediaTools::getType()) as $typeInfo)
    {
      if ($typeInfo['embeddable'])
      {
        return true;
      }
    }
    return false;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function getUploadAllowed()
  {
    foreach (aMediaTools::getTypeInfos(aMediaTools::getType()) as $typeInfo)
    {
      if (count($typeInfo['extensions']))
      {
        return true;
      }
    }
    return false;
  }

  /**
   * Implementation conveniences shared by the engine and backend media actions classes
   * All actions using this method will accept either a slug or an id,
   * for convenience
   * @param sfActions $actions
   * @return mixed
   */
  static public function getItem(sfActions $actions)
  {
    if ($actions->hasRequestParameter('slug'))
    {
      // Not sure why we're tolerant about this, but let's stay compatible with that
      $slug = aTools::slugify($actions->getRequestParameter('slug'));
      $item = Doctrine_Query::create()->
        from('aMediaItem')->
        where('slug = ?', array($slug))->
        fetchOne();
    }
    else
    {
      $id = $actions->getRequestParameter('id');
      $item = Doctrine::getTable('aMediaItem')->find($id);
    }  
    $actions->forward404Unless($item);
    return $item;
  }

  /**
   * refactored this into this static method from executeMultipleList() because it is now needed
   * for executeUpdateMultiplePreview() for cropping slideshow items
   * @return mixed
   */
  static public function getSelectedItems()
  {
    $selection = aMediaTools::getSelection();
    if (!is_array($selection))
    {
      throw new Exception("selection is not an array");
    }
    // Work around the fact that whereIn doesn't evaluate to AND FALSE
    // when the array is empty (it just does nothing; which is an
    // interesting variation on MySQL giving you an ERROR when the 
    // list is empty, sigh)
    if (count($selection))
    {
      // Work around the unsorted results of whereIn. You can also
      // do that with a FIELD function
      $unsortedItems = Doctrine_Query::create()->
        from('aMediaItem i')->
        whereIn('i.id', $selection)->
        execute();
      $itemsById = array();
      foreach ($unsortedItems as $item)
      {
        $itemsById[$item->getId()] = $item;
      }
      $items = array();
      foreach ($selection as $id)
      {
        if (isset($itemsById[$id]))
        {
          $items[] = $itemsById[$id];
        }
      }
    }
    else
    {
      $items = array();
    }
    
    return $items;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function getAspectRatio()
  {
    if (aMediaTools::getAttribute('aspect-width') && aMediaTools::getAttribute('aspect-width'))
    {
      return aMediaTools::getAttribute('aspect-width') / aMediaTools::getAttribute('aspect-height');
    }
    return 0;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function getSelectedThumbnailHeight()
  {
    $selectedConstraints = aMediaTools::getOption('selected_constraints');
    if (false === $selectedConstraints['height'])
    {
      if ($aspectRatio = aMediaTools::getAspectRatio())
      {
        return $selectedConstraints['width'] / $aspectRatio;
      }
      return 0; // Let's not divide by zero.
    }
    return $selectedConstraints['height'];
  }

  /**
   * 
   * This mirrors the default size math in aCrop.setAspectMask() in aCrop.js
   * @param mixed $mediaItem
   */
  static public function setDefaultCropDimensions($mediaItem)
  {
    $imageInfo = aMediaTools::getAttribute('imageInfo');
    $aspectRatio = aMediaTools::getAspectRatio();
    
    $imageAspectRatio = $mediaItem->getWidth() / $mediaItem->getHeight();
    // This is a fine time to record the actual dimensions so we don't have to 
    // manipulate the imageInfo attribute twice
    $imageInfo[$mediaItem->id]['width'] = $mediaItem->getWidth();
    $imageInfo[$mediaItem->id]['height'] = $mediaItem->getHeight();
    if ($aspectRatio)
    {     
      // We have an aspect ratio constraint
      if ($aspectRatio > $imageAspectRatio)
      {
        $imageInfo[$mediaItem->id]['cropWidth'] = $mediaItem->getWidth();
        $imageInfo[$mediaItem->id]['cropHeight'] = floor($mediaItem->getWidth() / $aspectRatio);
        $imageInfo[$mediaItem->id]['cropLeft'] = 0;
        $imageInfo[$mediaItem->id]['cropTop'] = floor(($mediaItem->getHeight() - $imageInfo[$mediaItem->id]['cropHeight']) / 2);
      }
      else
      {
        $imageInfo[$mediaItem->id]['cropHeight'] = $mediaItem->getHeight();
        $imageInfo[$mediaItem->id]['cropWidth'] = floor($mediaItem->getHeight() * $aspectRatio);
        $imageInfo[$mediaItem->id]['cropLeft'] = floor(($mediaItem->getWidth() - $imageInfo[$mediaItem->id]['cropWidth']) / 2);
        $imageInfo[$mediaItem->id]['cropTop'] = 0;
      }
    }
    else
    {
      $imageInfo[$mediaItem->id]['cropLeft'] = 0;
      $imageInfo[$mediaItem->id]['cropTop'] = 0;
      $imageInfo[$mediaItem->id]['cropWidth'] = $mediaItem->getWidth();
      $imageInfo[$mediaItem->id]['cropHeight'] = $mediaItem->getHeight();
    }
            
    aMediaTools::setAttribute('imageInfo', $imageInfo);
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function getNiceTypeName()
  {
    $type = aMediaTools::getAttribute('type', 'media item');
    // The names of types are meant to be user friendly (in English), except for
    // the metatypes like _downloadable which can't be user friendly and unique at the same time.
    // I can't think of a nicer phrase for "all embeddable things" than "media item"
    $niceNames = array('_downloadable' => 'file', '_embeddable' => 'media item');
    if (isset($niceNames[$type]))
    {
      return $niceNames[$type];
    }
    return $type;
  }

  /**
   * Safe for use with the sluggable behavior (aTools::slugify() has additional arguments, which get
   * confused by the $item second parameter that we safely ignore here)
   * @param mixed $path
   * @param mixed $item
   * @return mixed
   */
  static public function slugify($path, $item)
  {
    return aTools::slugify($path);
  }
  
  static protected $embedServices = array();

  /**
   * Default is to return only services that are ready to be used.
   * If you pass boolean false, you'll get services that are NOT ready to be used.
   * If you pass null, you'll get all services
   * @param mixed $configured
   * @return mixed
   */
  static public function getEmbedServices($configured = true)
  {
    if (!isset(aMediaTools::$embedServices[$configured]))
    {
      aMediaTools::$embedServices[$configured] = array();
      $serviceInfos = aMediaTools::getOption('embed_services');
      foreach ($serviceInfos as $serviceInfo)
      {
        $class = $serviceInfo['class'];
        $service = new $class;
        $service->setType($serviceInfo['media_type']);
        if ($configured)
        {
          if (!$service->configured())
          {
            continue;
          }
        }
        elseif ($configured === false)
        {
          if ($service->configured())
          {
            continue;
          }
        }
        else
        {
          // null = all
        }
        aMediaTools::$embedServices[$configured][] = $service;
      }
    }
    return aMediaTools::$embedServices[$configured];
  }

  /**
   * DOCUMENT ME
   * @param mixed $nameUrlOrEmbed
   * @return mixed
   */
  static public function getEmbedService($nameUrlOrEmbed)
  {
    $services = aMediaTools::getEmbedServices();
    foreach ($services as $service)
    {
      if ($service->getName() === $nameUrlOrEmbed)
      {
        return $service;
      }
    }
    foreach ($services as $service)
    {
      if ($service->getIdFromUrl($nameUrlOrEmbed))
      {
        return $service;
      }
    }
    foreach ($services as $service)
    {
      if ($service->getIdFromEmbed($nameUrlOrEmbed))
      {
        return $service;
      }
    }
    return null;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function getEmbedServiceNames()
  {
    $results = array();
    $services = aMediaTools::getEmbedServices();
    foreach ($services as $service)
    {
      $results[] = $service->getName();
    }
    return $results;
  }

  /**
   * DOCUMENT ME
   * @param mixed $filename
   * @return mixed
   */
  static public function filenameToTitle($filename)
  {
    $title = preg_replace('/\.\w+$/', '', $filename);
    // *Not* aMediaTools::slugify, which is specifically for the slug of the media item
    return aTools::slugify($title, false, false, ' ');
  }
}
