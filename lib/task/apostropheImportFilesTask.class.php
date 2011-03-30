<?php
/**
 * @package    apostrophePlugin
 * @subpackage    task
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class apostropheImportFilesTask extends sfBaseTask
{

  /**
   * DOCUMENT ME
   */
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('dir', null, sfCommandOption::PARAMETER_REQUIRED, 'The directory to scan for files to be imported', sfConfig::get('sf_root_dir') . '/web/uploads/media_import'),
      new sfCommandOption('verbose', null, sfCommandOption::PARAMETER_NONE, 'Output more info about file conversions', null)
    ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'import-files';
    $this->briefDescription = 'import media files into Apostrophe';
    $this->detailedDescription = <<<EOF
The [apostrophe:import-files|INFO] task scans the specified folder, which 
defaults to  web/uploads/media_import, for files and imports them into 
the media repository if they are in anyformat that can be uploaded via 
the regular media upload page (GIF, JPEG, PNG, PDF, XLS, DOC, etc). 

THESE FILES ARE REMOVED AFTER IMPORT, however the originals are copied to 
web/uploads/media_items. This was a deliberate choice to enable users to 
upload new files as needed to this folder by their bulk file transfer 
method of choice (FTP, SFTP, etc.) and be able to tell at a glance whether 
they have been imported yet or not. Files that cannot be imported are 
left alone.

Call it with:

  [php symfony apostrophe:import-files --application=frontend --env=dev --dir=web/uploads/media_import|INFO]
  
The dir option defaults to SF_ROOT_DIR/web/uploads/media_import. 

Be certain to specify the right environment for the system you are 
running it on.

Use --verbose if you want output in non-error situations, such as a 
report of how many files were converted.

EOF;
  }

  /**
   * DOCUMENT ME
   * @param mixed $arguments
   * @param mixed $options
   */
  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();
    // So we can play with app.yml settings from the application
    $context = sfContext::createInstance($this->configuration);

    $this->verbose = $options['verbose'];
    
    $import = new aMediaImporter(array('dir' => $options['dir'], 'feedback' => array($this, 'importFeedback')));
    $import->go();
  }

  /**
   * Must be public to be part of a callable
   * @param mixed $category
   * @param mixed $message
   * @param mixed $file
   */
  public function importFeedback($category, $message, $file = null)
  {
    if (($category === 'total') || ($category === 'info') || ($category === 'completed'))
    {
      if ($this->verbose)
      {
        if (($category === 'total') || ($category === 'completed'))
        {
          echo((is_null($file) ? '' : $file . ": ") . "Files converted: $message\n");
        }
        else
        {
          echo((is_null($file) ? '' : $file . ": ") . "$message\n");
        }
      }
    }
    if ($category === 'error')
    {
      echo((is_null($file) ? '' : $file . ": ") . "$message\n");
    }
  }
}
