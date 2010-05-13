<?php

// Copyright 2009, P'unk Ave LLC. Released under the MIT license.

/**
 * aValidatorFilePersistent validates an uploaded file, or
 * revalidates the existing file of the same browser-side name
 * uploaded on a previous submission by the same user in the case where 
 * no new file has been specified. 
 *
 * The file should come from the aWidgetFormInputFilePersistent widget.
 *
 * Should behave like the parent class in all other respects.
 *
 * @see sfValidatorFile
 */
class aValidatorFilePersistent extends sfValidatorFile
{
  
  protected function configure($options = array(), $messages = array())
  {
    $guessersSet = isset($options['mime_type_guessers']);
    parent::configure($options, $messages);
    if (!$guessersSet)
    {
      // Extend the default list from the parent class with a guesser that is
      // robust on rackspace cloud sites and works fine elsewhere as well.
      // Based on getimagesize. Also checks the PDF file signature. Everything
      // else falls back to the other guessers
      $mimeTypeGuessers = $this->getOption('mime_type_guessers');
      array_unshift($mimeTypeGuessers, array($this, 'guessFromImageconverter'));
      $this->setOption('mime_type_guessers', $mimeTypeGuessers);
    }
  }

  /**
   * The input value must be an array potentially containing two
   * keys, newfile and persistid. newfile must contain an array of
   * the following subkeys, if it is present:
   *
   *  * tmp_name: The absolute temporary path to the newly uploaded file
   *  * name:     The original file name (optional)
   *  * type:     The file content type (optional)
   *  * error:    The error code (optional)
   *  * size:     The file size in bytes (optional)
   * 
   * The persistid key allows lookup of a previously uploaded file
   * when no new file has been submitted. 
   *
   * A RARE BUT USEFUL CASE: if you need to prefill this cache before
   * invoking the form for the first time, you can instantiate this 
   * validator yourself:
   * 
   * $vfp = new aValidatorFilePersistent();
   * $guid = aGuid::generate();
   * $vfp->clean(
   *   array(
   *     'newfile' => 
   *       array('tmp_name' => $myexistingfile), 
   *     'persistid' => $guid));
   *
   * Then set array('persistid' => $guid) as the default value
   * for the file widget. This logic is most easily encapsulated in
   * the configure() method of your form class.
   *
   * @see sfValidatorFile
   * @see sfValidatorBase
   */

  public function clean($value)
  {
    $user = sfContext::getInstance()->getUser();
    $persistid = false;
    if (isset($value['persistid']))
    {
      $persistid = $value['persistid'];      
    }
    $newFile = false;
    $persistentDir = $this->getPersistentDir();
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
        $filePath = "$persistentDir/$persistid.file";
        $data = false;
        if (file_exists($filePath))
        {
          $dataPath = "$persistentDir/$persistid.data";
          // Don't let them expire
          touch($filePath);
          touch($dataPath);
          $data = file_get_contents($dataPath);
          if (strlen($data))
          {
            $data = unserialize($data);
          }
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
    // This will throw an exception if there is a validation error.
    // That's a good thing: we don't want to save it for reuse
    // in that situation.
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
        $infoPath = "$persistentDir/$persistid.data";
        $filePath = "$persistentDir/$persistid.file";
        @unlink($infoPath);
        @unlink($filePath);
      }
      throw $e;
    }
    if ($newFile)
    {
      // Expiration of abandoned stuff has to happen somewhere
      self::removeOldFiles($persistentDir);
      if ($persistid !== false)
      {
        $filePath = "$persistentDir/$persistid.file";
        copy($cvalue['tmp_name'], $filePath);
        $data = $cvalue;
        $data['newfile'] = true;
        $data['tmp_name'] = $filePath;
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
    }
    return $result;
  }

  static protected function getPersistentDir()
  {
    return aFiles::getWritableDataFolder(array("persistent_uploads"));
  }

  static public function removeOldFiles($dir)
  {
    // Age off any stale uploads in the cache
    // (TODO: for performance, do this one time in a hundred or similar,
    // it's simple to do that probabilistically).
    $files = glob("$dir/*");
    $now = time();
    foreach ($files as $file)
    {
      if ($now - filemtime($file) > 
        sfConfig::get('sf_persistent_upload_lifetime', 60) * 60)
      {
        unlink($file); 
      }
    }
  }

  static public function previewAvailable($value)
  {
    if (isset($value['persistid']))
    {
      $persistid = $value['persistid'];
      $info = self::getFileInfo($persistid);
      return !!$info['tmp_name'];
    }
    return false;
  }
  
  static public function getFileInfo($persistid)
  {
    if (!self::validPersistId($persistid))
    {
      // Roll our eyes at the hackers
      return false;
    }
    $persistentDir = self::getPersistentDir();
    $infoPath = "$persistentDir/$persistid.data";
    if (file_exists($infoPath))
    {
      return unserialize(file_get_contents($infoPath));
    }
    else
    {
      return false;
    }
  }

  static public function putFileInfo($persistid, $data)
  {
    $persistentDir = self::getPersistentDir();
    file_put_contents("$persistentDir/$persistid.data", serialize($data));
  }
  
  static public function validPersistId($persistid)
  {
    return preg_match("/^[a-fA-F0-9]+$/", $persistid);
  }
  
  /**
   * Guess the file mime type with mime_content_type function (deprecated)
   *
   * @param  string $file  The absolute path of a file
   *
   * @return string The mime type of the file (null if not guessable)
   */
  protected function guessFromImageconverter($file)
  {
    $info = aImageConverter::getInfo($file);
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
}
