<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginaMediaItem extends BaseaMediaItem
{
  public function save(Doctrine_Connection $conn = null)
  {
    if (!$this->getOwnerId())
    {
      if (sfContext::hasInstance())
      {
        $user = sfContext::getInstance()->getUser();
        if ($user->getGuardUser())
        {
          $this->setOwnerId($user->getGuardUser()->getId());
        }
      }
    }
    // Let the culture be the user's culture
    $result = aZendSearch::saveInDoctrineAndLucene($this, null, $conn);
    $crops = $this->getCrops();
    foreach ($crops as $crop)
    {
      $crop->setTitle($this->getTitle());
      $crop->setDescription($this->getDescription());
      $crop->setCredit($this->getCredit());
      $crop->save();
    }
    return $result;
  }

  public function doctrineSave($conn)
  {
    $result = parent::save($conn);
    return $result;
  }

  public function delete(Doctrine_Connection $conn = null)
  {
    $ret = aZendSearch::deleteFromDoctrineAndLucene($this, null, $conn);
    $this->clearImageCache();
    
    $this->deleteCrops();
    
    // Don't even think about trashing the original until we know
    // it's gone from the db and so forth
    unlink($this->getOriginalPath());
    return $ret;
  }

  public function doctrineDelete($conn)
  {
    return parent::delete($conn);
  }
  
  public function updateLuceneIndex()
  {
    aZendSearch::updateLuceneIndex($this, array(
      'type' => $this->getType(),
      'title' => $this->getTitle(),
      'description' => $this->getDescription(),
      'credit' => $this->getCredit(),
      'categories' => implode(", ", $this->getMediaCategoryNames()),
      'tags' => implode(", ", $this->getTags())
    ));
  }
  
  public function getMediaCategoryNames()
  {
    $categories = $this->getMediaCategories();
    $result = array();
    foreach ($categories as $category)
    {
      $result[] = $category->getName();
    }
    return $result;
  }
  
  public function getOriginalPath($format = false)
  {
    if ($format === false)
    {
      $format = $this->getFormat();
    }
    return aMediaItemTable::getDirectory() . 
      DIRECTORY_SEPARATOR . $this->getSlug() . ".original.$format";
  }
  public function clearImageCache($deleteOriginals = false)
  {
    if (!$this->getId())
    {
      return;
    }
    $cached = glob(aMediaItemTable::getDirectory() . DIRECTORY_SEPARATOR . $this->getSlug() . ".*");
    foreach ($cached as $file)
    {
      if (!$deleteOriginals)
      {
        if (strpos($file, ".original.") !== false)
        {
          continue;
        }
      }
      unlink($file); 
    }
  }
  
  public function preSaveFile($file)
  {
    // Refactored into aImageConverter for easier reuse of this should-be-in-PHP functionality
    $info = aImageConverter::getInfo($file);
    if ($info)
    {
      // Sometimes we store formats we can't get dimensions for on this particular platform
      if (isset($info['width']))
      {
        $this->width = $info['width'];
      }
      if (isset($info['height']))
      {
        $this->height = $info['height'];
      }
      // Don't force this, but it's useful when we're not
      // coming from a normal upload form
      if (!isset($file->format))
      {
        $this->format = $info['format'];
      }
      $this->clearImageCache(true);
    }
  }

  public function saveFile($file)
  {
    if (!$this->width)
    {
      if (!$this->preSaveFile($file))
      {
        return false;
      }
    }
    $path = $this->getOriginalPath($this->getFormat());
    $result = copy($file, $path);
    // Crops are invalid if you replace the original image
    $this->deleteCrops();
    return $result;
  }

  public function getEmbedCode($width, $height, $resizeType, $format = 'jpg', $absolute = false, $wmode = 'opaque')
  {
    if ($height === false)
    {
      // Scale the height. I had this backwards
      $height = floor(($width * $this->height / $this->width) + 0.5); 
    }

    // Accessible alt title
    $title = htmlspecialchars($this->getTitle());
    // It would be nice if partials could be used for this.
    // Think about whether that's possible.
    if ($this->getType() === 'video')
    {
      if ($this->embed)
      {
        // Solution for non-YouTube videos based on a manually
        // provided thumbnail and embed code
        return str_replace(array('_TITLE_', '_WIDTH_', '_HEIGHT_'),
          array($title, $width, $height), $this->embed);
      }
      // TODO: less YouTube-specific
      $serviceUrl = $this->getServiceUrl();
      $embeddedUrl = $this->youtubeUrlToEmbeddedUrl($serviceUrl);
      return <<<EOM
			<object alt="$title" width="$width" height="$height">
				<param name="movie" value="$embeddedUrl"></param>
				<param name="allowFullScreen" value="true"></param>
				<param name="allowscriptaccess" value="always"></param>
				<param name="wmode" value="$wmode"></param>
				<embed alt="$title" src="$embeddedUrl" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="$width" height="$height" wmode="$wmode"></embed>
			</object>
EOM
      ;
    }
    elseif (($this->getType() == 'image') || ($this->getType() == 'pdf'))
    {
      // Use named routing rule to ensure the desired result (and for speed)
      return "<img alt=\"$title\" width=\"$width\" height=\"$height\" src='" . htmlspecialchars($this->getImgSrcUrl($width, $height, $resizeType, $format, $absolute)) . "' />";
    }
    else
    {
      throw new Exception("Unknown media type in getEmbedCode: " . $this->getType() . " id is " . $this->id . " is new? " . $this->isNew());
    }
  }
  
  // This is currently allowed for all types, although a PDF will give you a plain white box if you
  // don't have ghostscript available
  
  public function getImgSrcUrl($width, $height, $resizeType, $format = 'jpg', $absolute = false)
  {
    if ($height === false)
    {
      // Scale the height. I had this backwards
      $height = floor(($width * $this->height / $this->width) + 0.5); 
    }

    $controller = sfContext::getInstance()->getController();
    $slug = $this->getSlug();
    // Use named routing rule to ensure the desired result (and for speed)
    return $controller->genUrl("@a_media_image?" . 
      http_build_query(
        array("slug" => $slug, 
          "width" => $width, 
          "height" => $height, 
          "resizeType" => $resizeType,
          "format" => $format)), $absolute);
  }
  
  protected function youtubeUrlToEmbeddedUrl($url)
  {
    $url = str_replace("/watch?v=", "/v/", $url);
    $url .= "&fs=1";
    return $url;
  }
  public function userHasPrivilege($privilege, $user = false)
  {
    if ($user === false)
    {
      $user = sfContext::getInstance()->getUser();
    }
    if ($privilege === 'view')
    {
      if ($this->getViewIsSecure())
      {
        if (!$user->isAuthenticated())
        {
          return false;
        }
      }
      return true;
    }
    if ($user->hasCredential(aMediaTools::getOption('admin_credential')))
    {
      return true;
    }
    $guardUser = $user->getGuardUser();
    if (!$guardUser)
    {
      return false;
    }
    if ($this->getOwnerId() === $guardUser->getId())
    {
      return true;
    }
    return false;
  }
  
  // Returns a Symfony action URL. Call url_for or use sfController for final routing.
  
  public function getScaledUrl($options)
  {
    $options = aDimensions::constrain($this->getWidth(), $this->getHeight(), $this->getFormat(), $options);

    $params = array("slug" => $this->slug, "width" => $options['width'], "height" => $options['height'], 
      "resizeType" => $options['resizeType'], "format" => $options['format']);

    // check for null because 0 is valid
    if (!is_null($options['cropLeft']) && !is_null($options['cropTop']) && !is_null($options['cropWidth']) && !is_null($options['cropHeight']))
    {      
      $params = array_merge(
        $params,
        array("cropLeft" => $options['cropLeft'], "cropTop" => $options['cropTop'],
          "cropWidth" => $options['cropWidth'], "cropHeight" => $options['cropHeight'])
      );
    }
    return "aMediaBackend/image?" . http_build_query($params);
  }
  
  public function getCropThumbnailUrl()
  {    
    $selectedConstraints = aMediaTools::getOption('selected_constraints');
    
    if ($aspectRatio = aMediaTools::getAspectRatio()) // this returns 0 if aspect-width and aspect-height were not set
    {
      $selectedConstraints = array_merge(
        $selectedConstraints, 
        array('height' => floor($selectedConstraints['width'] / $aspectRatio))
      );
    }
    
    
    $imageInfo = aMediaTools::getAttribute('imageInfo');
    if (isset($imageInfo[$this->id]['cropLeft']) &&
        isset($imageInfo[$this->id]['cropTop']) && isset($imageInfo[$this->id]['cropWidth']) && isset($imageInfo[$this->id]['cropHeight']))
    {
      $selectedConstraints = array_merge(
        $selectedConstraints, 
        array(
          'cropLeft' => $imageInfo[$this->id]['cropLeft'],
          'cropTop' => $imageInfo[$this->id]['cropTop'],
          'cropWidth' => $imageInfo[$this->id]['cropWidth'],
          'cropHeight' => $imageInfo[$this->id]['cropHeight']
        )
      );
    }
      
    return $this->getScaledUrl($selectedConstraints);
  }
  
  // Crops of other images have periods in the slug. Real slugs are always [\w_]+ (well, the i18n equivalent)
  public function isCrop()
  {
    return (strpos($this->slug, '.') !== false);
  }
  
  public function getCrops()
  {
    // This should perform well because there is an index on the slug and
    // indexes are great with prefix queries
    return $this->getTable()->createQuery('m')->where('m.slug LIKE ?', array($this->slug . '.%'))->execute();
    
  }
  
  public function deleteCrops()
  {
    $crops = $this->getCrops();
    // Let's make darn sure the PHP stuff gets called rather than using a delete all trick of some sort
    foreach ($crops as $crop)
    {
      $crop->delete();
    }
  }
  
  public function findOrCreateCrop($info)
  {
    $slug = $this->slug . '.' . $info['cropLeft'] . '.' . $info['cropTop'] . '.' . $info['cropWidth'] . '.' . $info['cropHeight'];
    $crop = $this->getTable()->findOneBySlug($slug);
    if (!$crop)
    {
      $crop = $this->copy(false);
      $crop->slug = $slug;
      $crop->width = $info['cropWidth'];
      $crop->height = $info['cropHeight'];
    }
    return $crop;
  }
  
  public function getCroppingInfo()
  {
    $p = preg_split('/\./', $this->slug);
    if (count($p) == 5)
    {
      return array('cropLeft' => $p[1], 'cropTop' => $p[2], 'cropWidth' => $p[3], 'cropHeight' => $p[4]);
    }
    else
    {
      return array();
    }
  }
  
  public function getCropOriginal()
  {
    if (!$this->isCrop())
    {
      return $this;
    }
    $p = preg_split('/\./', $this->slug);
    return $this->getTable()->findOneBySlug($p[0]);
  }
  
  public function getDownloadable()
  {
    // Right now videos are always embedded and nothing else is
    return ($this->type !== 'video');
  }
  
  public function getEmbeddable()
  {
    // Right now videos are always embedded and nothing else is
    return ($this->type === 'video');
  }
}
