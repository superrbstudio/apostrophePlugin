<?php

class aMediaTools
{
  // These are used internally. See aMediaSelect for the methods you probably want

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

    self::clearSelecting();
    self::setAttribute("selecting", true);
    self::setAttribute("after", $after);
    self::setAttribute("multiple", $multiple);
    self::setAttribute("cropping", $cropping);
    self::setAttribute("selection", $selection);
    self::setAttribute("imageInfo", $imageInfo);
    foreach ($options as $key => $val)
    {
      self::setAttribute($key, $val);
    }
  }
  static public function clearSelecting()
  {
    self::removeAttributes();
  }
  static public function isSelecting()
  {
    return self::getAttribute("selecting");
  }
  static public function isMultiple()
  {
    return self::getAttribute("multiple");
  }
  static public function getSelection()
  {
    return self::getAttribute("selection", array());
  }
  static public function setSelection($array)
  {
    self::setAttribute("selection", $array);
  }
  static public function getAfter()
  {
    return self::getAttribute("after");
  }
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
    $selection = self::getSelection();
    return (array_search($id, $selection) != false);
  }
  static public function setSearchParameters($array)
  {
    self::setAttribute("search-parameters", $array); 
  }

  static public function getSearchParameters($default = false)
  {
    if ($default === false)
    {
      $default = array();
    }
    return self::getAttribute("search-parameters", $default);
  }

  static public function getSearchParameter($p, $default = false)
  {
    $parameters = self::getSearchParameters();
    if (isset($parameters[$p]))
    {
      return $parameters[$p];
    }
    return $default;
  }

  static public function getType()
  {
    return self::getAttribute('type');
  }
  
  static public function getBestTypeLabel()
  {
    $type = aMediaTools::getType();
    if ($type)
    {
      $typeInfo = aMediaTools::getTypeInfo($type);
      return $typeInfo['label'];
    }
    else
    {
      return 'Media';
    }
  }

  static public function userHasUploadPrivilege()
  {
    $user = sfContext::getInstance()->getUser();
    if (!$user->isAuthenticated())
    {
      return false;
    }
    $uploadCredential = self::getOption('upload_credential');
    if ($uploadCredential)
    {
      return $user->hasCredential($uploadCredential);
    }
    else
    {
      return true;
    }
  }

  static private function getUser()
  {
    return sfContext::getInstance()->getUser();
  }

  static public function getAttribute($attribute, $default = null)
  {
    // If you are logged out, you should have no attributes, as
    // all attributes used in the media engine relate to selection
    if (!self::getUser()->isAuthenticated())
    {
      return $default;
    }
    $attribute = "aMedia-$attribute";
    return self::getUser()->getAttribute($attribute, $default, 'apostrophe_media');
  }
  
  static public function setAttribute($attribute, $value = null)
  {
    $attribute = "aMedia-$attribute";
    self::getUser()->setAttribute($attribute, $value, 'apostrophe_media');
  }
  
  static public function removeAttributes()
  {
    $user = self::getUser();
    $user->getAttributeHolder()->removeNamespace('apostrophe_media');
  }
  
  // This is a good convention for plugin options IMHO
  static private $options = array(
    "batch_max" => 6,
    "per_page" => 20,
    "popular_tags" => 10,
    "video_search_per_page" => 9,
    "video_search_preview_width" => 220,
    "video_search_preview_height" => 170,
    "upload_credential" => "media_upload",
    "admin_credential" => "media_admin",
    "gallery_constraints" => array(
        "width" => 340,
        "height" => false,
        "resizeType" => "s"),
    "selected_constraints" => array(
        "width" => 100,
        "height" => false,
        "resizeType" => "c",),
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
      "mp3" => "mpeg/mp3",
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

    'types' => array(
      'image' => array('label' => 'Image', 'extensions' => array('gif', 'png', 'jpg'), 'embeddable' => false),
      'pdf' => array('label' => 'PDF', 'extensions' => array('pdf'), 'embeddable' => false),
      'audio' => array('label' => 'Audio', 'extensions' => array('mp3'), 'embeddable' => false),
      'video' => array('label' => 'Video', 'extensions' => array(), 'embeddable' => true),
      
      // A long whitelist of file formats that are usually benign and useful.
      // No .exe, no .zip. You can add them via app.yml if you really want them.
      // We list only the non-macro-enabled Microsoft extensions in an effort to
      // honor their good-faith attempt to label more dangerous files
      
      'office' => array('label' => 'Office', 'extensions' => array('txt', 'rtf', 'csv', 'doc', 'docx', 'xls', 'xlsx', 'xlsb', 'ppt', 'pptx', 'ppsx'), 'embeddable' => false)));

  static private $layouts = array(
    'one-up' => array(
        "name" => "one-up",
        "image" => "/apostrophePlugin/images/a-icon-media-single.png",
        "gallery_constraints" => array(
          "width" => 340,
          "height" => false,
          "resizeType" => "s"),
        "columns" => 1,
        "fields" => array("controls" => 1,"thumbnail" => 1,"title" => 1, "description" => 1, 'dimensions'=> 1, "credit" => 1, "categories" => 1, "tags" => 1, 'view_is_secure' => 1, 'link' => 1, )
      ),
    'two-up' => array(
        "name" => "two-up",
        "image" => "/apostrophePlugin/images/a-icon-media-two-up.png",
        "gallery_constraints" => array(
          "width" => 340,
          "height" => false,
          "resizeType" => "s"),
        "columns" => 2,
        "fields" => array("controls" => 1,"thumbnail" => 1,"title" => 1, "description" => 1, 'dimensions' => 1, "credit" => 1, "categories" => 1, "tags" => 1, 'view_is_secure' => 1, 'link' => 1, )
      ),
      'four-up' => array(
        "name" => "four-up",
        "image" => "/apostrophePlugin/images/a-icon-media-grid.png",
        "gallery_constraints" => array(
          "width" => 340,
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
        "columns" => 8,
        "fields" => array("thumbnail" => 1)
      )
    );

  static public function getOption($name)
  {
    if (isset(self::$options[$name]))
    {
      $name = preg_replace("/[^\w]/", "", $name);
      $key = "app_aMedia_$name";
      return sfConfig::get($key, self::$options[$name]);
    }
    else
    {
      throw new Exception("Unknown option in apostrophePlugin: $name");
    }
  }

  static public function getLayout($name)
  {
    return self::$layouts[$name];
  }

  static public function getEnabledLayouts()
  {
    return array_intersect_key(self::$layouts, array_flip(self::getOption('enabled_layouts')));
  }
  
  static public function getTypeInfo($name)
  {
    $types = aMediaTools::getOption('types');
    return $types[$name];
  }

  // Returns an array of type infos, just the one if you specify a type, all if you don't.
  // Handy when filtering
  static public function getTypeInfos($type = null)
  {
    $types = aMediaTools::getOption('types');
    if (is_null($type))
    {
      return $types;
    }
    return array($types[$type]);
  }
  
  // Implementation conveniences shared by the engine and backend media actions classes
  
  // All actions using this method will accept either a slug or an id,
  // for convenience
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
  
  // refactored this into this static method from executeMultipleList() because it is now needed
  // for executeUpdateMultiplePreview() for cropping slideshow items
  static public function getSelectedItems()
  {
    $selection = self::getSelection();
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
  
  static public function getAspectRatio()
  {
    if (self::getAttribute('aspect-width') && self::getAttribute('aspect-width'))
    {
      return self::getAttribute('aspect-width') / self::getAttribute('aspect-height');
    }
    return 0;
  }
  
  static public function getSelectedThumbnailHeight()
  {
    $selectedConstraints = self::getOption('selected_constraints');
    if (false === $selectedConstraints['height'])
    {
      if ($aspectRatio = self::getAspectRatio())
      {
        return $selectedConstraints['width'] / $aspectRatio;
      }
      return 0; // Let's not divide by zero.
    }
    return $selectedConstraints['height'];
  }
  
  /**
   * This mirrors the default size math in aCrop.setAspectMask() in aCrop.js
   */
  static public function setDefaultCropDimensions($mediaItem)
  {
    $imageInfo = self::getAttribute('imageInfo');
    $aspectRatio = self::getAspectRatio();
    
    if ($aspectRatio)
    {    
      if ($aspectRatio > 1)
      {
        $imageInfo[$mediaItem->id]['cropWidth'] = $mediaItem->getWidth();
        $imageInfo[$mediaItem->id]['cropHeight'] = floor($mediaItem->getWidth() / $aspectRatio);
      }
      else
      {
        $imageInfo[$mediaItem->id]['cropHeight'] = $mediaItem->getHeight();
        $imageInfo[$mediaItem->id]['cropWidth'] = floor($mediaItem->getHeight() * $aspectRatio);
      }
    }
    else
    {
      $imageInfo[$mediaItem->id]['cropWidth'] = $mediaItem->getWidth();
      $imageInfo[$mediaItem->id]['cropHeight'] = $mediaItem->getHeight();
    }
    
    $imageInfo[$mediaItem->id]['cropLeft'] = 0;
    $imageInfo[$mediaItem->id]['cropTop'] = floor(($mediaItem->getHeight() - $imageInfo[$mediaItem->id]['cropHeight']) / 2);
        
    self::setAttribute('imageInfo', $imageInfo);
  }
}
