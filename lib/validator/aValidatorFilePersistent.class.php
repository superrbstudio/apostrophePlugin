<?php
/**
 * Copyright 2009, P'unk Ave LLC. Released under the MIT license.
 * 
 * aValidatorFilePersistent validates an uploaded file, or
 * revalidates the existing file of the same browser-side name
 * uploaded on a previous submission by the same user in the case where
 * no new file has been specified.
 * The file should come from the aWidgetFormInputFilePersistent widget.
 * Should behave like the parent class in all other respects.
 *
 * @see sfValidatorFile
 * @package    apostrophePlugin
 * @subpackage    validator
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aValidatorFilePersistent extends sfValidatorFile
{
  // Make the original name available to guessers. It's a nice thought to
  // avoid this but with Microsoft Office formats there are no reliable
  // magic numbers, and those that do exist can be misleading because
  // Word can contain Excel and vice versa
  protected $originalName;
  protected $validatedType;

  protected $mustBeImage = false;
  
  /**
   * DOCUMENT ME
   * @param mixed $options
   * @param mixed $messages
   */
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);
    // If any of these options are not null, the uploaded file must 
    // be a GIF, JPEG or PNG file
    $this->addOption('minimum-width', null);
    $this->addOption('minimum-height', null);
    $this->addOption('maximum-width', null);
    $this->addOption('maximum-height', null);
    $this->addMessage('not-an-image', 'The file %value% is not an image. Please upload an image in GIF, JPEG or PNG format.');
    $this->addMessage('minimum-width', 'Please upload an image at least %minimum-width% pixels wide.');
    $this->addMessage('maximum-width', 'Please upload an image less than %maximum-width% pixels wide.');
    $this->addMessage('minimum-height', 'Please upload an image at least %minimum-height% pixels tall.');
    $this->addMessage('maximum-height', 'Please upload an image less than %maximum-height% pixels tall.');
    $this->addMessage('minimum-dimensions', 'Please upload an image at least %minimum-width%x%minimum-height% pixels in size.');
    $this->addMessage('maximum-dimensions', 'Please upload an image no more than %maximum-width%x%maximum-height% pixels in size.');
  }

  /**
   * 
   * The input value must be an array potentially containing two
   * keys, newfile and persistid. newfile must contain an array of
   * the following subkeys, if it is present:
   * * tmp_name: The absolute temporary path to the newly uploaded file
   * name:     The browser-submitted file name (optional, but necessary to distinguish amongst Microsoft Office formats)
   * type:     The browser-submitted file content type (required although our guessers never trust it)
   * error:    The error code (optional)
   * size:     The file size in bytes (optional)
   * The persistid key allows lookup of a previously uploaded file
   * when no new file has been submitted.
   * A RARE BUT USEFUL CASE: if you need to prefill this cache before
   * invoking the form for the first time, you can instantiate this
   * validator yourself:
   * $vfp = new aValidatorFilePersistent();
   * $guid = aGuid::generate();
   * $vfp->clean(
   * array(
   * 'newfile' =>
   * array('tmp_name' => $myexistingfile),
   * 'persistid' => $guid));
   * Then set array('persistid' => $guid) as the default value
   * for the file widget. This logic is most easily encapsulated in
   * the configure() method of your form class.
   * @see sfValidatorFile
   * @see sfValidatorBase
   * @param mixed $value
   * @return mixed
   */
  public function clean($value)
  {
    if ($this->getOption('minimum-width') || $this->getOption('minimum-height') || $this->getOption('maximum-width') || $this->getOption('maximum-height'))
    {
      $this->mustBeImage = true;
    }
    $persistid = false;
    if (isset($value['persistid']))
    {
      $persistid = $value['persistid'];      
    }
    $newFile = false;
    if (!self::validPersistId($persistid))
    {
      $persistid = false;
    }
    $cvalue = false;
    // Why do we tolerate the newfile fork being entirely absent?
    // Because with persistent file upload widgets, it's safe to
    // redirect a form submission to another action via the GET method
    // after validation... which is extremely useful if you want to
    // split something into an iframed initial upload action and
    // a non-iframed annotation action and you need to be able to
    // stuff the state of the form into a URL and do window.parent.location =.
    // As long as we tolerate the absence of the newfile button, we can
    // rebuild the submission from what's in 
    // getRequest()->getParameterHolder()->getAll(), and that is useful.
    if ((!isset($value['newfile']) || ($this->isEmpty($value['newfile']))))
    {
      if ($persistid !== false)
      {
        $data = aValidatorFilePersistent::getFileInfo($persistid);
        if ($data)
        {
          // Keep it considered current. A little blunt but effective
          aValidatorFilePersistent::putFileInfo($persistid, $data);
        }
        if ($data)
        {
          $cvalue = $data;
        }
      }
    }
    else
    {
      $newFile = true;
      $cvalue = $value['newfile'];
    }
    if (isset($cvalue['name']))
    {
      $this->originalName = $cvalue['name'];
    }
    else
    {
      $this->originalName = '';
    }
    if ($newFile)
    {
      // Expiration of abandoned stuff has to happen somewhere
      aValidatorFilePersistent::removeOldFiles($this->getPersistentDir());
      // We are always interested in getting image dimensions and
      // format information about the file (the widget might want it
      // in the cache for preview purposes, even if we don't have any
      // width and height restrictions to check). We also want to know
      // the mime type. We do everything we can to avoid hitting the
      // standard cascade of guessers, which are super slow over S3
      $imageInfo = aImageConverter::getInfo($cvalue['tmp_name']);
      $type = null;
      if ($imageInfo)
      {
        $type = $this->guessFromImageconverter($cvalue['tmp_name'], $imageInfo);
      }
      if (is_null($type))
      {
        $type = $this->guessFromMicrosoft($cvalue['tmp_name']);
      }
      if (is_null($type))
      {
        $type = $this->guessFromID3($cvalue['tmp_name']);
      }
      if (is_null($type))
      {
        $type = $this->guessFromRTF($cvalue['tmp_name']);
      }
      if (!is_null($type))
      {
        $this->validatedType = $type;
      }
      else
      {
        $this->validatedType = null;
      }
      if ($this->mustBeImage)
      {
        // Check whether the dimensions of the image are acceptable.
        // If not build a validator error message
        if (!$imageInfo)
        {
          throw new sfValidatorError($this, 'not-an-image', array('value' => (string) $value));
        }
        $messageArgs = array('minimum-width' => $this->getOption('minimum-width'), 'width' => $imageInfo['width'], 'minimum-height' => $this->getOption('minimum-height'), 'height' => $imageInfo['height'], 'maximum-width' => $this->getOption('maximum-width'), 'maximum-height' => $this->getOption('maximum-height'));
        $msg = false;
        if ($this->getOption('minimum-width'))
        {
          if ($imageInfo['width'] < $this->getOption('minimum-width'))
          {
            // If there is also a minimum-width don't be a tease, tell
            // them about both limits to save them grief
            if ($this->getOption('minimum-height'))
            {
              $msg = 'minimum-dimensions';
            }
            else
            {
              $msg = 'minimum-width';
            }
          }
        }
        if ($this->getOption('maximum-width'))
        {
          if ($imageInfo['width'] > $this->getOption('maximum-width'))
          {
            if ($this->getOption('maximum-height'))
            {
              $msg = 'maximum-dimensions';
            }
            else
            {
              $msg = 'maximum-width';
            }
          }
        }
        if ($this->getOption('minimum-height'))
        {
          if ($imageInfo['height'] < $this->getOption('minimum-height'))
          {
            if ($this->getOption('minimum-width'))
            {
              $msg = 'minimum-dimensions';
            }
            else
            {
              $msg = 'minimum-height';
            }
          }
        }
        if ($this->getOption('maximum-height'))
        {
          if ($imageInfo['height'] > $this->getOption('maximum-height'))
          {
            if ($this->getOption('maximum-width'))
            {
              $msg = 'maximum-dimensions';
            }
            else
            {
              $msg = 'maximum-height';
            }
          }
        }
        if ($msg)
        {
          $error = new sfValidatorError($this, $msg, $messageArgs);
          if ($persistid !== false)
          {
            $this->discard($persistid);
          }
          throw $error;
        }
      }
      if ($persistid !== false)
      {
        $persistentDir = $this->getPersistentDir();
        $filePath = "$persistentDir/$persistid.file";
        copy($cvalue['tmp_name'], $filePath);
        $data = $cvalue;
        if (isset($this->validatedType))
        {
          $data['validatedType'] = $this->validatedType;
        }
        $data['imageInfo'] = $imageInfo;
        $data['newfile'] = true;
        $data['tmp_name'] = $filePath;
        
        // It's useful to know the mime type and true extension for 
        // supplying previews and icons
        $extensionsByMimeType = array_flip(aMediaTools::getOption('mime_types'));
        if (!isset($cvalue['type']))
        {
          // It's not sensible to trust a browser-submitted mime type anyway,
          // so don't force non-web invocations of this code to supply one
          $cvalue['type'] = 'application/octet-stream';
        }
        $data['mime_type'] = $this->getMimeType($filePath, $cvalue['type']);
        if (isset($extensionsByMimeType[$data['mime_type']]))
        {
          $data['extension'] = $extensionsByMimeType[$data['mime_type']];
        }
        
        self::putFileInfo($persistid, $data);
      }
    } elseif ($persistid !== false)
    {
      $data = self::getFileInfo($persistid);
      if ($data !== false)
      {
        $data['newfile'] = false;
        self::putFileInfo($persistid, $data);
      }
      if (isset($data['validatedType']))
      {
        $this->validatedType = $data['validatedType'];
      }
    }
    try
    {
      $result = parent::clean($cvalue);
    } catch (Exception $e)
    {
      // If there is a validation error stop keeping this
      // file around and don't display the reassuring
      // "you don't have to upload again" message side by side
      // with the validation error.
      if ($persistid !== false)
      {
        $this->discard($persistid);
      }
      throw $e;
    }
    return $result;
  }

  /**
   * Returns the location where persistent file uploads are kept between
   * validation passes.
   * @return string
   */
  static protected function getPersistentDir()
  {
    return aFiles::getWritableDataFolder(array("persistent_uploads"));
  }

  /**
   * The cache of information about persistent file uploads is self-cleaning, but the
   * files themselves are too large for sfCache derivatives (the MySQL 1MB limit, the
   * practical limits of memcache, etc. are not great for large originals). So we 
   * must use S3 for these. Occasional cleanup is necessary
   */
  static public function removeOldFiles($dir)
  {
    // Age off any stale uploads in the cache
    
    // glob is busted for stream wrappers
    $files = aFiles::ls($dir, array('fullPath' => true));
    
    $now = time();
    foreach ($files as $file)
    {
      $mtime = filemtime($file);
      // Don't fuss about it if someone else happens to clear these first
      if ($now - filemtime($file) > 
        sfConfig::get('sf_persistent_upload_lifetime', 60) * 60)
      {
        unlink($file); 
      }
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $value
   * @return mixed
   */
  static public function previewAvailable($value)
  {
    if (isset($value['persistid']))
    {
      $persistid = $value['persistid'];
      $info = self::getFileInfo($persistid);
      // Only web friendly image formats are reasonable for preview. Make sure
      // there's a persistent file and a width for that image in the cache.
      return $info['tmp_name'] && isset($info['imageInfo']['width']);
    }
    return false;
  }

  /**
   * Determines whether the specified form field value refers to a
   * file that is already persisting in the widget, as in the case of
   * a second validation pass
   */
  static public function alreadyPersisting($value)
  {
    if (isset($value['persistid']))
    {
      // I should really do a 'has' on the cache here
      $persistid = $value['persistid'];
      return !!self::getFileInfo($persistid);
    }
    return false;
  }

  /**
   * DOCUMENT ME
   * @param mixed $persistid
   * @return mixed
   */
  static public function getFileInfo($persistid)
  {
    if (!self::validPersistId($persistid))
    {
      // Roll our eyes at the hackers
      return false;
    }
    $cache = aCacheTools::get('persistentFiles');
    $raw = $cache->get($persistid);
    if (is_null($raw))
    {
      return false;
    }
    return unserialize($raw);
  }

  /**
   * DOCUMENT ME
   * @param mixed $persistid
   * @param mixed $data
   */
  static public function putFileInfo($persistid, $data)
  {
    $cache = aCacheTools::get('persistentFiles');
    $cache->set($persistid, serialize($data), 3600);
  }

  /**
   * DOCUMENT ME
   * @param mixed $persistid
   * @return mixed
   */
  static public function validPersistId($persistid)
  {
    return preg_match("/^[a-fA-F0-9]+$/", $persistid);
  }

  /**
   * 
   * Guess the file mime type with aImageConverter's getInfo method, which uses imagesize and
   * magic numbers to be more robust than relying on a lot of badly configured external tools
   * @param  string $file  The absolute path of a file
   * @param  array $info   Previously fetched aImageConverter::getInfo() result, for performance
   * @return string The mime type of the file (null if not guessable)
   */
  protected function guessFromImageconverter($file, $info = null)
  {
    if (!$info)
    {
      $info = aImageConverter::getInfo($file);
    }
    if (!$info)
    {
      return null;
    }
    $formats = array('jpg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'pdf' => 'application/pdf');
    if (isset($formats[$info['format']]))
    {
      return $formats[$info['format']];
    }
    return null;
  }

  /**
   * 
   * Guess the file mime type of MP3 audio files based on the ID3 tag at the beginning, more robust
   * than the file command's buggy support for MP3s that seems to dislike VBR files
   * @param  string $file  The absolute path of a file
   * @return string The mime type of the file (null if not guessable)
   */
  protected function guessFromID3($file)
  {
    $in = fopen($file, 'rb');
    $magic = fread($in, 3);
    fclose($in);
    if ($magic !== 'ID3')
    {
      return null;
    }
    return 'audio/mpeg';
  }

  /**
   * DOCUMENT ME
   * @param mixed $file
   * @return mixed
   */
  protected function guessFromRTF($file)
  {
    $in = fopen($file, 'rb');
    $magic = fread($in, 5);
    fclose($in);
    if ($magic !== '{\\rtf')
    {
      return null;
    }
    return 'text/rtf';
  }

  /**
   * Microsoft extensions validated by guessFromMicrosoft. It's useful to be able to
   * check this list from elsewhere, do not hide this property please
   */
  static public $msExtensions = array(
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
    'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template'
  );
  
  /**
   * DOCUMENT ME
   * @param mixed $file
   * @return mixed
   */
  protected function guessFromMicrosoft($file)
  {
    // We look at the original name to get the rest.
    // Sorry, but there are no reliable magic numbers
    // that don't sometimes mislead for Microsoft Office files.
    $in = fopen($file, "rb");
    $data = fread($in, 3);
    fclose($in);
    $maybeMicrosoft = false;
    // Magic numbers: old Microsoft container and new zip-based Microsoft container
    if (($data === sprintf("%c%c%c", 0xD0, 0xCF, 0x11)) || ($data === sprintf("%c%c%c", 0x50, 0x4B, 0x03)))
    {
      $maybeMicrosoft = true;
    }
    if (!$maybeMicrosoft)
    {
      return null;
    }
    
    $ms = aValidatorFilePersistent::$msExtensions;
    
    if (preg_match('/\.(\w+)$/', $this->originalName, $matches))
    {
      $extension = $matches[1];
      if (isset($ms[$extension]))
      {
        return $ms[$extension];
      }
    }
    return null;
  }

  /**
   * Get the mime type of the file. We do our best to short circuit this
   * early rather than doing 80000 reads of the file over a slow link
   * @param mixed $file
   * @param mixed $fallback
   * @return mixed
   */
  protected function getMimeType($file, $fallback)
  {
    if (!is_null($this->validatedType))
    {
      return $this->validatedType;
    }

    return parent::getMimeType($file, $fallback);
  }
  
  protected function discard($persistid)
  {
    $cache = aCacheTools::get('persistentFiles');
    $cache->remove($persistid);
    $dir = aValidatorFilePersistent::getPersistentDir();
    @unlink("$dir/$persistid.file");
  }
}
