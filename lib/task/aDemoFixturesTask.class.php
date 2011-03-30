<?php
/**
 * 
 * This file is part of Apostrophe
 * (c) 2009 P'unk Avenue LLC, www.punkave.com
 * 
 * @package    apostrophePlugin
 * @subpackage Tasks
 * @author     Tom Boutell <tom@punkave.com>
 */
class aDemoFixturesTask extends sfBaseTask
{

  /**
   * DOCUMENT ME
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('verbose', null, sfCommandOption::PARAMETER_NONE, 'Output more info during the update', null)
      // add your own options here
    ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'demo-fixtures';
    $this->briefDescription = 'Downloads the current demo site fixtures from content.apostrophenow.com';
    $this->detailedDescription = <<<EOF
This task downloads the latest content samples, including the database and the media files, from 
content.apostrophenow.com. That content is much more complete and interesting than the
simple fixtures files provided with apostrophePlugin. This is the same content that is regularly
used to reset content.apostrophenow.com.

To use this task you must be using MySQL. The command line 'mysql' utility must be in the path.
You must have the command line 'unzip' utility. And PHP must be compiled with support for http
in file commands like 'copy' (which is almost always the case).

If you are missing any of these requirements, it's no big deal: you can still use the fixtures
(with the ./symfony doctrine:data-load task). They just aren't as much fun. But you're going to
build your own site anyway, right?

You should only do this on a brand-new site. On an existing site it will overwrite all of your content.
EOF;
  }

  /**
   * DOCUMENT ME
   * @param mixed $args
   * @param mixed $options
   */
  protected function execute($args = array(), $options = array())
  {    
    $conn = 'doctrine';
    $params = sfSyncContentTools::shellDatabaseParams(sfSyncContentTools::getDatabaseParams($this->configuration, $conn));

    $dataDir = aFiles::getWritableDataFolder();
    $uploadDir = aFiles::getUploadFolder();

    // $dataDir will be a_writable, sf_data_dir is its parent, so we can put things there and
    // trust that they are not crushed by the unzip
    
    $dataZip = sfConfig::get('sf_data_dir') . '/apostrophedemo-awritable.zip';
    if (!copy('http://content.apostrophenow.com/uploads/apostrophedemo-awritable.zip', $dataZip))
    {
      throw new sfException("Unable to copy http://content.apostrophenow.com/uploads/apostrophedemo-awritable.zip to $dataZip");
    }
    $uploadZip = sfConfig::get('sf_data_dir') . '/apostrophedemo-uploads.zip';
    if (!copy("http://content.apostrophenow.com/uploads/apostrophedemo-uploads.zip", $uploadZip))
    {
      throw new sfException('Unable to copy http://content.apostrophenow.com/uploads/apostrophedemo-uploads.zip to $dataZip');
    }
    $this->unzip($dataDir, $dataZip, $options);
    $this->unzip($uploadDir, $uploadZip, $options);

    // Yes, you need to have mysql to use this feature.
    // However you can set app_syncContent_mysql to
    // the path of your mysql utility if it is called something
    // else or not in the PATH
    $mysql = sfConfig::get('app_syncContent_mysql', 'mysql');

    system(escapeshellarg($mysql) . " $params < " . escapeshellarg($dataDir . '/ademocontent.sql'), $result);
    
    if ($result != 0)
    {
      throw new sfException("mysql failed. Maybe you don't have it in your PATH");
    }
    
    // Undo the little dance we did to send the demo password across without forcing the use
    // of a known password salt or a particular encryption scheme on the receiving site.
    // The demo password is no secret, but new passwords set later should be, so we don't
    // want to force a salt when generating the demo dump. See aSyncActions for details
    
    if ($options['verbose'])
    {
      echo("Postprocessing users\n");
    }
    $users = Doctrine::getTable('sfGuardUser')->findAll();
    foreach ($users as $user)
    {
      // If there is a salt, and no password, it's really a hint to turn the cleartext password
      // into a proper one and establish a salt
      if (!strlen($user->getPassword()))
      {
        $demoPassword = $user->getSalt();
        if (strlen($demoPassword))
        {
          $user->setSalt('');
          $user->setPassword($demoPassword);
          $user->save();
        }
      }
    }
    if ($options['verbose'])
    {
      echo("Content loaded.\n");
    }
    system('./symfony apostrophe:rebuild-search-index', $result);
    if ($result != 0)
    {
      throw new sfException('Problem executing apostrophe:rebuild-search-index task.');
    }
    
  }

  /**
   * DOCUMENT ME
   * @param mixed $dir
   * @param mixed $file
   * @param mixed $options
   */
  protected function unzip($dir, $file, $options)
  {
    // Does a nice job of leaving .svn and .cvs alone
    sfToolkit::clearDirectory($dir);
    // Overwrite existing files. Without this it'll fail when used to regularly refresh a demo
    $zipOptions = '-o ';
    if (!$options['verbose'])
    {
      $zipOptions .= '-q ';
    }
    system("(cd " . escapeshellarg($dir) . "; unzip $zipOptions " . escapeshellarg($file) . " )", $result);
    if ($result != 0)
    {
      throw new sfException("unzip of $file to $dir failed. Maybe you don't have unzip in your PATH");
    }
  }
}
