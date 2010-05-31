<?php

class aFiles
{
  /*
   * Returns a data folder in which files can be read and written by
   * the web server, but NOT seen as part of the server's document space. 
   * Automatically checks for overriding path settings via app.yml so
   * you can customize these directory settings.
   * 
   * getWritableDataFolder() returns sf_data_dir/a_writable unless 
   * overridden by app_aToolkit_writable_dir. Note that this main directory
   * is automatically chmodded appropriately by symfony project:permissions.
   * (apostrophePlugin registers an event handler that extends this task.)
   *
   * getWritableDataFolder(array('indexes')) returns 
   * sf_data_dir/a_writable/indexes unless overridden by 
   * app_aToolkit_writable_indexes_dir (first preference) or 
   * app_aToolkit_writable_dir (second preference). If app_aToolkit_writable_indexes_dir
   * is not set, but app_aToolkit_writable_dir is found, then 
   * /indexes will be appended to app_aToolkit_writable_dir.
   *
   * You may supply more than one component in the array. For instance,
   * getWritableDataFolder(array('indexes', 'purple')) returns
   * sf_data_dir/a_writable/indexes/purple unless overridden by
   * app_aToolkit_writable_indexes_purple_dir (first choice), or
   * app_aToolkit_writable_indexes_dir (second choice), or
   * app_aToolkit_writable_dir (third choice). 
   *
   * You can also pass a single path argument rather than an
   * array, in which case it is split into components at the slashes,
   * with any leading and trailing slashes removed first.
   * 
   * Always attempts to create the folder if needed. This generally
   * succeeds except for the top level sf_data_dir/a_writable folder,
   * so you'll need to create that folder and make it readable, 
   * writable and executable by the web server (chmod 777 in many cases).
   * 
   * Occurrences of SF_DATA_DIR in the final path will be automatically
   * replaced with the value of sfConfig::get('sf_data_dir'). This is
   * useful when specifying alternate paths in app.yml, e.g.
   * (to be compatible with a very early release of our CMS):
   *
   * a_writable_zend_indexes: SF_DATA_DIR/zendIndexes
   *
   * SF_WEB_DIR is supported in the same way.
   */
  static public function getWritableDataFolder($components = array())
  {
    return self::getOrCreateFolder("app_aToolkit_writable_dir", 
      sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . 'a_writable',
      $components);
  }

  /*
   * Returns a subfolder of the project's upload folder in which files
   * can be read and written by the web server and also seen as part of the
   * web server's document space. Automatically checks for overriding 
   * path settings via app.yml so you can customize these directory settings.
   * 
   * getUploadFolder() returns sf_upload_dir unless 
   * overridden by app_aToolkit_upload_dir.
   *
   * getUploadFolder(array('media')) returns sf_upload_dir/media 
   * unless overridden by app_aToolkit_upload_media_dir (first preference) or 
   * app_aToolkit_upload_dir (second preference). If app_aToolkit_upload_media_dir
   * is not set, but app_aToolkit_upload_dir is found, then 
   * /media will be appended to app_aToolkit_upload_dir.
   *
   * You may supply more than one component in the array. For instance,
   * getUploadFolder(array('media', 'jpegs')) returns
   * sf_upload_dir/media/jpegs unless overridden by
   * app_aToolkit_upload_media_jpegs_dir (first choice), or
   * app_aToolkit_upload_media_dir (second choice), or
   * app_aToolkit_upload_dir (third choice).
   * 
   * You can also pass a single path argument rather than an
   * array, in which case it is split into components at the slashes,
   * with any leading and trailing slashes removed first.
   *
   * Always attempts to create the folder if needed. This generally
   * succeeds because Symfony projects have a world-writable
   * top-level web/upload folder by default.
   *
   * Occurrences of SF_DATA_DIR in the final path will be automatically
   * replaced with the value of sfConfig::get('sf_data_dir'). This is
   * useful when specifying alternate paths in app.yml, e.g.
   * (to be compatible with a very early release of our CMS):
   *
   * a_writable_zend_indexes: SF_DATA_DIR/zendIndexes
   *
   * SF_WEB_DIR is supported in the same way.
   */
  static public function getUploadFolder($components = array())
  {
    return self::getOrCreateFolder("app_aToolkit_upload_dir",
      sfConfig::get('sf_upload_dir'), $components);
  }

  /*
   * Returns a subfolder of $basePath.
   * Automatically checks for overriding path settings via app.yml 
   * so you can customize these directory settings.
   * 
   * getOrCreateFolder('app_key_dir', '/path') returns /path unless
   * overridden by the Symfony config setting app_key_dir. 
   *
   * getOrCreateFolder('app_key_dir', '/path', array('media')) returns 
   * /path/media unless overridden by app_key_media_dir (first preference) or 
   * app_key_dir (second preference). If app_key_media_dir
   * is not set, but app_key_dir is set, then 
   * /media will be appended to app_key_dir.
   *
   * You may supply more than one component in the array. For instance,
   * getOrCreateFolder('app_key_dir', '/path', array('media', 'jpegs')) 
   * returns /path/media/jpegs unless overridden by
   * app_key_media_jpegs_dir (first choice), or
   * app_key_media_dir (second choice), or
   * app_key_dir (third choice).
   *
   * You can also pass a single path argument rather than an
   * array, in which case it is split into components at the slashes,
   * with any leading and trailing slashes removed first.
   *
   * Always attempts to create the folder if needed. This generally
   * succeeds because Symfony projects have a world-writable
   * top-level web/upload folder by default.
   *
   * Occurrences of SF_DATA_DIR in the final path will be automatically
   * replaced with the value of sfConfig::get('sf_data_dir'). This is
   * useful when specifying alternate paths in app.yml, e.g.
   * (to be compatible with a very early release of our CMS):
   *
   * all:
   *   aToolkit:
   *     _writable_zend_indexes_dir: SF_DATA_DIR/zendIndexes
   *
   * SF_WEB_DIR is supported in the same way.
   */
  static public function getOrCreateFolder($baseKey, $basePath, $components = array())
  {
    if (!is_array($components))
    {
      $components = preg_split("/\//", $components, -1, PREG_SPLIT_NO_EMPTY);
    }
    $key = $baseKey;
    $count = count($components);
    $path = false;
    $baseKeyStem = $baseKey;
    $pos = strpos($baseKey, "_dir");
    if ($pos !== false)
    {
      $baseKeyStem = substr($baseKey, 0, $pos) . "_";
    }
    for ($i = $count; ($i >= 0); $i--)
    {
      if ($i === 0)
      {
        $key = $baseKey;
      }
      else
      {
        $key = $baseKeyStem . 
          implode("_", array_slice($components, 0, $i)) . "_dir";
      }
      $default = false;
      if ($i === 0)
      {
        $default = $basePath;
      }
      $result = sfConfig::get($key, $default);
      if ($result !== false)
      {
        $remainder = implode(DIRECTORY_SEPARATOR, array_slice($components, $i));
        $ancestor = $result;
        if (strlen($remainder))
        {
          $path = $result . DIRECTORY_SEPARATOR . $remainder;
        }
        else
        {
          $path = $result;
        }
        break;
      }
    }
    
    $path = str_replace(
      array("SF_DATA_DIR", "SF_WEB_DIR"),
      array(sfConfig::get('sf_data_dir'), sfConfig::get('sf_web_dir')),
      $path);
    if (!is_dir($path))
    {
      // There's a recursive mkdir flag in PHP 5.x, neato
      if (!mkdir($path, 0777, true))
      {
        // It's better to report $ancestor rather than $path because
        // creating that one parent should solve the problem
        throw new Exception("Unable to create $ancestor the admin will probably need to do this manually the first time and set permissions so that the web server can write to that folder");
      }
    }
    return $path;
  }

  /*
   * Symfony has a getTempDir method in sfToolkit but it is only
   * used by unit tests. It relies on the system temporary folder
   * which might not always be accessible in a non-command-line
   * PHP environment. Let's use something more local to our project.
   */
  static public function getTemporaryFileFolder()
  {
    return self::getWritableDataFolder(array("tmp"));
  }
  
  static public function getTemporaryFilename()
  {

    $filename = aGuid::generate();
    $tempDir = self::getTemporaryFileFolder();
    return $tempDir . DIRECTORY_SEPARATOR . $filename;
  }
}
