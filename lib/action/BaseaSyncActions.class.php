<?php
/**
 * Cloud-oriented web APIs. At the moment only the zipDemo action lives here. The
 * APC action was removed in favor of rsyncing more intelligently.
 * For security all of this is disabled unless configured in properties.ini.
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaSyncActions extends sfActions
{
  protected $sync;

  /**
   * DOCUMENT ME
   * @param mixed $param
   * @param mixed $default
   * @return mixed
   */
  protected function get($param, $default = null)
  {
    if (!isset($this->sync))
    {
      $settings = parse_ini_file(sfConfig::get('sf_root_dir') . "/config/properties.ini", true);
      if ($settings === false)
      {
        throw new sfException("Cannot find config/properties.ini");
      }
    
      if (!isset($settings['sync']))
      {
        throw new sfException('sync section not configured in properties.ini');
      }      
      $this->sync = $settings['sync'];
    }
    
    if (!isset($this->sync[$param]))
    {
      return $default;
    }
    return $this->sync[$param];
  }

  // preExecute validates the password before allowing anything to be done
  // (this module is intended to grow to encompass PHP-based code syncing like
  // what I did for Pressroom for future cloud sites; right now it just has a
  // method to clear the APC cache)
    
  protected $api = false;

  /**
   * DOCUMENT ME
   */
  public function preExecute()
  {
    $syncPassword = $this->get('password');
    if (!$syncPassword)
    {
      throw new sfException('Sync password is not set, sync module disabled');
    }    
    if ($this->getRequestParameter('password') !== $syncPassword)
    {
      throw new sfException('Bad sync password');
    }
    // We want to give back script-friendly responses
    $this->setLayout(false);
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   */
  public function executeZipDemo(sfWebRequest $request)
  {
    if (!$this->get('zip_demo', false))
    {
      throw new sfException('Zip demo feature is not enabled in properties.ini');
    }
    $uploads = aFiles::getUploadFolder();
    $uploadsDemo = "$uploads/apostrophedemo-uploads.zip";
    file_put_contents("$uploads/readme.txt", 
"This is the Symfony uploads folder. This file is here so that zipping this folder does not
result in an error when it happens to be empty. Move along, nothing to see here.");
    $this->zip($uploadsDemo, $uploads);
    // Has to be in the writable folder or we won't be able to write to it in many cases
    $data = aFiles::getWritableDataFolder();
    $dump = "$data/ademocontent.sql";
    
    $params = sfSyncContentTools::shellDatabaseParams(sfSyncContentTools::getDatabaseParams(sfContext::getInstance()->getConfiguration(), 'doctrine'));

    // Yes, you need to have mysql and mysqldump to use this feature.
    // However you can set app_syncContent_mysqldump to
    // the path of your mysqldump utility if it is called something
    // else or not in the PATH
    
    $mysql = sfConfig::get('app_syncContent_mysql', 'mysql');
    $mysqldump = sfConfig::get('app_syncContent_mysqldump', 'mysqldump');
    
    $cmd = escapeshellarg($mysqldump) . " --skip-opt --add-drop-table --create-options " .
      "--disable-keys --extended-insert --set-charset $params > " . escapeshellarg($dump);
    system($cmd, $result);

    if ($result != 0)
    {
      throw new sfException("mysqldump failed");
    }

    // You can explicitly set demo_password to an empty string to keep
    // the passwords in your demo unchanged
    $demoPassword = $this->get('demo_password', 'demo');
    if (!preg_match('/^\w+$/', $demoPassword))
    {
      throw new sfException("demo_password must contain only alphanumeric characters and underscores");
    }
    if ($demoPassword)
    {
      // New set of parameters for a temporary database in which we'll fix the passwords so that
      // the demo doesn't allow dictionary attacks on your real passwords
      $params = array();
      $params['dbname'] = $this->get('demo_tempdb');
      $params['username'] = $this->get('demo_tempuser');
      $params['password'] = $this->get('demo_temppassword');
      $params['host'] = $this->get('demo_temphost');
      $params = sfSyncContentTools::shellDatabaseParams($params);
      $cmd = escapeshellarg($mysql) . ' ' . $params . ' < ' . escapeshellarg($dump);
      system($cmd, $result);
      if ($result != 0)
      {
        throw new sfException("Unable to load sql into tempdb for password alteration. Did you configure tempdb, tempuser, temppassword and temphost in properties.ini?");
      }
      // I really ought to PDO this
      $cmd = escapeshellarg($mysql) . ' ' . $params;
      $out = popen($cmd, "w");

      // If we set the salt here, everyone who starts from the demo has the same salt.
      // If they change their passwords to real passwords, they are still vulnerable to
      // dictionary attack if their databases are compromised.
      
      // That's no good, so we'll fix the passwords with a clever trick in the demo fixtures
      // task that imports all this. We stash the demo password (not a secret) in the salt field
      // as cleartext for now, and the demo fixtures task grabs that, clears the salt field and
      // calls setPassword, resulting in a new, robustly unique salt on the new site.
      fwrite($out, "UPDATE sf_guard_user SET salt = '$demoPassword';\n");
      fwrite($out, "UPDATE sf_guard_user SET password = '';\n");
      $result = pclose($out);
      $cmd = escapeshellarg($mysqldump) . " --skip-opt --add-drop-table --create-options " .
        "--disable-keys --extended-insert --set-charset $params > " . escapeshellarg($dump);      
      system($cmd, $result);
      if ($result != 0)
      {
        throw new sfException('Second mysqldump failed after password adjustment');
      }
    }

    $dataDemo = "$uploads/apostrophedemo-awritable.zip";
    $this->zip($dataDemo, $data);
    unlink($dump);
  }

  /**
   * DOCUMENT ME
   * @param mixed $file
   * @param mixed $dir
   */
  static protected function zip($file, $dir)
  {
    if (file_exists($file))
    {
      unlink($file);
    }
    // We want only relative paths in the zipfile so we must chdir
    // I wish we could use --filesync but most zips don't have it.
    // We must be carefully lowest common denominator here
    $cmd = '(cd ' . escapeshellarg($dir) . '; zip -r ' . escapeshellarg($file) . ' . -q -x ' . escapeshellarg('apostrophedemo-*.zip') . ' ' . escapeshellarg('.svn*') . ' )';
    // echo("$cmd\n");
    // exit(0);
    system($cmd, $result);
    if ($result != 0)
    {
      throw new sfException("Zip failed");
    }
  }  
}
