<?php
/**
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aFiles
{

  /**
   * 
   * Returns a data folder in which files can be read and written by
   * the web server, but NOT seen as part of the server's document space.
   * Automatically checks for overriding path settings via app.yml so
   * you can customize these directory settings.
   * getWritableDataFolder() returns sf_data_dir/a_writable unless
   * overridden by app_aToolkit_writable_dir. Note that this main directory
   * is automatically chmodded appropriately by symfony project:permissions.
   * (apostrophePlugin registers an event handler that extends this task.)
   * getWritableDataFolder(array('indexes')) returns
   * sf_data_dir/a_writable/indexes unless overridden by
   * app_aToolkit_writable_indexes_dir (first preference) or
   * app_aToolkit_writable_dir (second preference). If app_aToolkit_writable_indexes_dir
   * is not set, but app_aToolkit_writable_dir is found, then
   * /indexes will be appended to app_aToolkit_writable_dir.
   * You may supply more than one component in the array. For instance,
   * getWritableDataFolder(array('indexes', 'purple')) returns
   * sf_data_dir/a_writable/indexes/purple unless overridden by
   * app_aToolkit_writable_indexes_purple_dir (first choice), or
   * app_aToolkit_writable_indexes_dir (second choice), or
   * app_aToolkit_writable_dir (third choice).
   * You can also pass a single path argument rather than an
   * array, in which case it is split into components at the slashes,
   * with any leading and trailing slashes removed first.
   * Always attempts to create the folder if needed. This generally
   * succeeds except for the top level sf_data_dir/a_writable folder,
   * so you'll need to create that folder and make it readable,
   * writable and executable by the web server (chmod 777 in many cases).
   * Occurrences of SF_DATA_DIR in the final path will be automatically
   * replaced with the value of sfConfig::get('sf_data_dir'). This is
   * useful when specifying alternate paths in app.yml, e.g.
   * (to be compatible with a very early release of our CMS):
   * a_writable_zend_indexes: SF_DATA_DIR/zendIndexes
   * SF_WEB_DIR is supported in the same way.
   * @param mixed $components
   * @return mixed
   */
  static public function getWritableDataFolder($components = array())
  {
    return self::getOrCreateFolder("app_aToolkit_writable_dir", 
      sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . 'a_writable',
      $components);
  }

  /**
   * 
   * Returns a subfolder of the project's upload folder in which files
   * can be read and written by the web server and also seen as part of the
   * web server's document space. Automatically checks for overriding
   * path settings via app.yml so you can customize these directory settings.
   * getUploadFolder() returns sf_upload_dir unless
   * overridden by app_aToolkit_upload_dir.
   * getUploadFolder(array('media')) returns sf_upload_dir/media
   * unless overridden by app_aToolkit_upload_media_dir (first preference) or
   * app_aToolkit_upload_dir (second preference). If app_aToolkit_upload_media_dir
   * is not set, but app_aToolkit_upload_dir is found, then
   * /media will be appended to app_aToolkit_upload_dir.
   * You may supply more than one component in the array. For instance,
   * getUploadFolder(array('media', 'jpegs')) returns
   * sf_upload_dir/media/jpegs unless overridden by
   * app_aToolkit_upload_media_jpegs_dir (first choice), or
   * app_aToolkit_upload_media_dir (second choice), or
   * app_aToolkit_upload_dir (third choice).
   * You can also pass a single path argument rather than an
   * array, in which case it is split into components at the slashes,
   * with any leading and trailing slashes removed first.
   * Always attempts to create the folder if needed. This generally
   * succeeds because Symfony projects have a world-writable
   * top-level web/upload folder by default.
   * Occurrences of SF_DATA_DIR in the final path will be automatically
   * replaced with the value of sfConfig::get('sf_data_dir'). This is
   * useful when specifying alternate paths in app.yml, e.g.
   * (to be compatible with a very early release of our CMS):
   * a_writable_zend_indexes: SF_DATA_DIR/zendIndexes
   * SF_WEB_DIR is supported in the same way.
   * @param mixed $components
   * @return mixed
   */
  static public function getUploadFolder($components = array())
  {
    return self::getOrCreateFolder("app_aToolkit_upload_dir",
      sfConfig::get('sf_upload_dir'), $components);
  }

  static protected $folderCache = array();

  /**
   * 
   * Returns a subfolder of $basePath.
   * Automatically checks for overriding path settings via app.yml
   * so you can customize these directory settings.
   * getOrCreateFolder('app_key_dir', '/path') returns /path unless
   * overridden by the Symfony config setting app_key_dir.
   * getOrCreateFolder('app_key_dir', '/path', array('media')) returns
   * /path/media unless overridden by app_key_media_dir (first preference) or
   * app_key_dir (second preference). If app_key_media_dir
   * is not set, but app_key_dir is set, then
   * /media will be appended to app_key_dir.
   * You may supply more than one component in the array. For instance,
   * getOrCreateFolder('app_key_dir', '/path', array('media', 'jpegs'))
   * returns /path/media/jpegs unless overridden by
   * app_key_media_jpegs_dir (first choice), or
   * app_key_media_dir (second choice), or
   * app_key_dir (third choice).
   * You can also pass a single path argument rather than an
   * array, in which case it is split into components at the slashes,
   * with any leading and trailing slashes removed first.
   * Always attempts to create the folder if needed. This generally
   * succeeds because Symfony projects have a world-writable
   * top-level web/upload folder by default.
   * Occurrences of SF_DATA_DIR in the final path will be automatically
   * replaced with the value of sfConfig::get('sf_data_dir'). This is
   * useful when specifying alternate paths in app.yml, e.g.
   * (to be compatible with a very early release of our CMS):
   * all:
   * aToolkit:
   * _writable_zend_indexes_dir: SF_DATA_DIR/zendIndexes
   * SF_WEB_DIR is supported in the same way.
   *
   * Results of this call are cached for the duration of the request so that you can
   * call it repeatedly without hitting the filesystem with slow stat() calls.
   *
   * @param mixed $baseKey
   * @param mixed $basePath
   * @param mixed $components
   * @return mixed
   */
  static public function getOrCreateFolder($baseKey, $basePath, $components = array())
  {
    if (!is_array($components))
    {
      $components = preg_split("/\//", $components, -1, PREG_SPLIT_NO_EMPTY);
    }
    $cacheKey = implode('/', $components);
    if (strlen($cacheKey))
    {
      $cacheKey = $baseKey . $cacheKey;
    }
    else
    {
      $cacheKey = $baseKey;
    }
    // Keep trying to find it in the per-request cache, first by
    // checking the persistent cache, then by actually doing the 
    // slow filesystem stat() and mkdir() work
    if (!isset(aFiles::$folderCache[$cacheKey]))
    {
      $persistentCache = aCacheTools::get('folder');
      aFiles::$folderCache[$cacheKey] = $persistentCache->get($cacheKey);
    }
    if (!isset(aFiles::$folderCache[$cacheKey]))
    {
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
          throw new Exception("Unable to create $path in $ancestor the admin will probably need to do this manually the first time and set permissions so that the web server can write to that folder");
        }
      }
      aFiles::$folderCache[$cacheKey] = $path;
      $persistentCache->set($cacheKey, $path, 86400 * 365);
    }
    return aFiles::$folderCache[$cacheKey];
  }

  /**
   * 
   * Symfony has a getTempDir method in sfToolkit but it is only
   * used by unit tests. It relies on the system temporary folder
   * which might not always be accessible in a non-command-line
   * PHP environment. Let's use something more local to our project.
   * @return mixed
   */
  static public function getTemporaryFileFolder()
  {
    return self::getWritableDataFolder(array("tmp"));
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function getTemporaryFilename()
  {

    $filename = aGuid::generate();
    $tempDir = self::getTemporaryFileFolder();
    return $tempDir . DIRECTORY_SEPARATOR . $filename;
  }
  
  static public function touch($file)
  {
    // Update the modification time of the file, even if it
    // is accessed via a stream wrapper. PHP does not support
    // this otherwise in the regular touch() function
    $out = fopen($file, "a");
    if ($out)
    {
      fclose($out);
      return true;
    }
    return false;
  }
  
  /**
   * Returns array of filenames in directory, without the useless and dangerous . and .. entries,
   * using only functions that stream wrappers support. Returns just the basenames, the
   * full path is NOT returned unless you specify $options['fullPath'] = true. Returns false
   * if the path does not exist or is not a directory (you may get an empty list for stream wrappers
   * that can't really make this distinction)
   */
  static public function ls($path, $options = array())
  {
    $dir = @opendir($path);
    if ($dir === false)
    {
      return false;
    }
    $files = array();
    $fullPath = isset($options['fullPath']) && $options['fullPath'];
    if ($fullPath)
    {
      $prependPath = preg_replace('/\/$/', '', $path);
    }
    while (($file = readdir($dir)) !== false)
    {
      if (($file === '.') || ($file === '..'))
      {
        continue;
      }
      if ($fullPath)
      {
        $files[] = "$prependPath/$file";
      }
      else
      {
        $files[] = $file;
      }
    }
    closedir($dir);
    return $files;
  }
  
  /**
   * A partial implementation of glob() that uses only functions that stream wrappers support. 
   * Right now this implementation is very limited: you can only have one * wildcard and it must
   * be in the last component of the path. The . and .. entries are never returned. Subdirectories
   * are returned. Performance would be better if we used native globbing functionality of the
   * underlying implementations like S3 to avoid pulling a list of everything in the folder first. 
   * For now it's good enough for the media repository's needs
   *
   * Returns a full path, because glob does
   */
   
  static public function glob($pattern)
  {
    $path = dirname($pattern);
    $pattern = basename($pattern);
    $pattern = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/';
    $files = aFiles::ls($path);
    $results = array();
    foreach ($files as $file)
    {
      if (preg_match($pattern, $file))
      {
        $results[] = $path . '/' . $file;
      }
    }
    return $results;
  }
  
  /**
   * $statInfo is an array returned by stat(). Determines whether
   * it ultimately refers to a regular file
   */
  static public function statIsFile($statInfo)
  {
    return $statInfo['mode'] & 0100000;
  }

  /**
   * $statInfo() is an array returned by stat(). Determines whether
   * it ultimately refers to a directory
   */
  static public function statIsDir($statInfo)
  {
    return $statInfo['mode'] & 0040000;
  }
  
  /**
   * Be careful with this, it follows symlinks if any. Mainly for stream wrappers
   */
  static public function rmRf($path)
  {
    $stat = @stat($path);
    if (!$stat)
    {
      return;
    }
    if (aFiles::statIsDir($stat))
    {
      $list = aFiles::ls($path);
      foreach ($list as $file)
      {
        $filePath = "$path/$file";
        if (strlen($filePath) < strlen($path))
        {
          throw new sfException("I almost tried to delete something higher up the original, I don't like this, bailing out");
        }
        aFiles::rmRf($filePath);
      }
      if (!rmdir($path))
      {
        return false;
      }
    }
    else
    {
      if (!unlink($path))
      {
        return false;
      }
    }
    return true;
  }
  
  /**
   * Make one directory a mirror of the other, deleting and adding files as needed.
   * Creates $to if necessary. Source files that disappear in mid-sync log a warning.
   * Failures to write to the destination are considered serious errors and result in the
   * entire operation returning false. 
   *
   * Compares sizes and modification dates to determine whether source is newer
   * than destination unless 'force' => true is specified as an option. In addition, you can 
   * specify an array of regular expressions
   * to be compared to each full source pathname with 'exclude' => array('regexp1', 'regexp2' ...).
   * Note that if a regular expression matches a parent folder then files and subfolders within it
   * will not be synced, regardless of whether they individually match or not.
   *
   * Patterns excluded on the source are also left alone on the destination.
   *
   * This is useful for syncing content to an Amazon S3 wrapper
   * and in other situations where rsync is not available. Does not attempt to
   * set permissions (stream wrappers don't support chmod, for one thing). With our
   * usual s3 configuration you should just use the s3public: protocol for public stuff and 
   * the s3private: protocol for private stuff.
   * 
   * Symlinks, if encountered, are followed and what they refer to is copied,
   * so don't copy any recursive references
   */
  static public function sync($from, $to, $options = array())
  {
    // Let's be verbose for this first big scary migration on staging
    $fromList = aFiles::ls($from);
    if ($fromList === false)
    {
      return false;
    }
    $toList = aFiles::ls($to);
    if ($toList === false)
    {
      if (!mkdir($to))
      {
        return false;
      }
      $toList = array();
    }
    $valid = array();
    foreach ($fromList as $file)
    {
      $fromPath = "$from/$file";
      if (aFiles::syncExclude($fromPath, $options))
      {
        continue;
      }
      // Ensure consistency regardless of whether a given system likes trailing slashes on directories
      $valid[preg_replace('/\/$/', '', $file)] = true;
      $toPath = "$to/$file";
      $fromStat = @stat($fromPath);
      if (!$fromStat)
      {
        error_log("Warning: cannot stat $fromPath, maybe it disappeared in mid-sync?");
        continue;
      }
      $toStat = @stat($toPath);
      $fromDir = aFiles::statIsDir($fromStat);
      $fromFile = aFiles::statIsFile($fromStat);
      if ($toStat)
      {
        $toDir = aFiles::statIsDir($toStat);
        $toFile = aFiles::statIsFile($toStat);
        if (($toDir !== $fromDir) || ($toFile !== $fromFile))
        {
          /**
           * Same name but a completely different kind of animal.
           * Remove it on the destination so we'll able to make the
           * other (dir vs. file or vice versa)
           */
          aFiles::rmRf($toPath);
        }
        elseif ($fromFile)
        {
          if ((!isset($options['force'])) || (!$options['force']))
          {
            if (($toStat['mtime'] >= $fromStat['mtime']) && ($toStat['size'] === $fromStat['size']))
            {
              continue;
            }
          }
        }
      }
      if ($fromDir)
      {
        if (!aFiles::sync($fromPath, $toPath, $options))
        {
          return false;
        }
      }
      else
      {
        if (!aFiles::copy($fromPath, $toPath))
        {
          error_log("Cannot copy $fromPath to $toPath, maybe it disappeared in mid-sync or receiving drive is full");
          return false;
        }
      }
    }
    // Remove any files on the destination that did not exist on the source
    foreach ($toList as $file)
    {
      // Remove any inconsistency as to whether a trailing slash is present,
      // otherwise we trash perfectly good folders
      $testFile = preg_replace('/\/$/', '', $file);
      if (!isset($valid[$testFile]))
      {
        $toPath = "$to/$file";
        if (aFiles::syncExclude($toPath, $options))
        {
          continue;
        }
        if (!aFiles::rmRf($toPath))
        {
          error_log("Warning: can't remove $toPath, maybe someone else got rid of it for us");
        }
      }
    }
    return true;
  }
  
  static public function syncExclude($path, $options)
  {
    if (isset($options['exclude']))
    {
      $excluded = false;
      // Remove any trailing / before considering patterns.
      // The s3 wrapper appends / to folders to make stat calls faster,
      // but people writing exclude expressions cannot be reasonably
      // expected to consider this
      $excludePath = preg_replace('/\/$/', '', $path);
      foreach ($options['exclude'] as $regexp)
      {
        if (preg_match($regexp, $excludePath))
        {
          return true;
        }
      }
    }
    return false;
  }
  
  /**
   * Recursively copy one folder to another. Assumes the second path does not exist.
   * This is a lot faster than a sync because it doesn't have to stat() everything.
   *
   * This is useful for syncing content to an Amazon S3 wrapper
   * and in other situations where rsync is not available. Does not attempt to
   * set permissions (stream wrappers don't support chmod, for one thing). 
   * 
   * Symlinks, if encountered, are followed and what they refer to is copied,
   * so don't copy any recursive references
   *
   * By default, if any part of the copy fails the whole thing fails and is
   * backed out, leaving nothing at $to. If you don't want this, specify
   * 'continue-on-error' => true as an option and the copy will be as complete
   * as possible in the event that one or more items cannot be copied. Verbose
   * errors are logged to the PHP log in this situation.
   *
   * Returns false if any errors occur, true otherwise.
   */
  static public function copyFolder($from, $to, $options = array())
  {
    $continueOnError = isset($options['continue-on-error']) && $options['continue-on-error'];
    $result = true;
    
    $fromList = aFiles::ls($from);
    if ($fromList === false)
    {
      error_log("WARNING: aFiles::ls returns false for $from");
      $result = false;
      return $result;
    }
    
    foreach ($fromList as $file)
    {
      $fromPath = "$from/$file";
      $toPath = "$to/$file";
      if (is_dir($fromPath))
      {
        $result = aFiles::copyFolder($fromPath, $toPath);
        if (!$result)
        {
          $result = false;
          error_log("WARNING: unable to copy $fromPath to $toPath");
          if (!$continueOnError)
          {
            // If we fail on any file undo the whole thing
            aFiles::rmRf($to);
            return $result;
          }
        }
      }
      else
      {
        if (!aFiles::copy($fromPath, $toPath))
        {
          $result = false;
          error_log("WARNING: unable to copy $fromPath to $toPath");
          if (!$continueOnError)
          {
            // If we fail on any file undo the whole thing
            aFiles::rmRf($to);
            return $result;
          }
        }
      }
    }
    return $result;
  }
  
  /**
   * Copy a file, checking the result of fflush() to make sure it really
   * got there. Native php copy() DOES NOT do this. Returns true if the
   * whole thing actually got there. If not, removes $to and returns false
   */
  static public function copy($from, $to)
  {
    $in = fopen($from, "rb");
    if (!$in)
    {
      return false;
    }
    $out = fopen($to, "wb");
    if (!$out)
    {
      fclose($in);
      return false;
    }
    while (true)
    {
      $buf = fread($in, 65536);
      if ($buf === false)
      {
        // Read failed
        fclose($in);
        fclose($out);
        unlink($to);
        return false;
      }
      if (strlen($buf) === 0)
      {
        // EOF
        break;
      }
      if (fwrite($out, $buf) !== strlen($buf))
      {
        fclose($in);
        fclose($out);
        unlink($to);
        return false;
      }
    }
    fclose($in);
    if (!aFiles::close($out))
    {
      unlink($to);
      return false;
    }
    return true;
  }
  
  /**
   * Close a file opened with fopen() and friends, after making sure
   * that the write (if any) has actually been successful by checking
   * the result of fflush(). Returns true only if both fflush and fclose
   * succeed. As of this writing fclose always returns true in PHP even
   * if its implicit flush call fails so we need this for reliable close
   */
  static public function close($file)
  {
    if (!fflush($file))
    {
      fclose($file);
      return false;
    }
    // It would be nice if this reported false on a failed implicit flush but it doesn't
    if (!fclose($file))
    {
      return false;
    }
    return true;
  }
}
