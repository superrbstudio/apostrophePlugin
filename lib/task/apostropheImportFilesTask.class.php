<?php

class apostropheImportFilesTask extends sfBaseTask
{
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
      new sfCommandOption('dir', null, sfCommandOption::PARAMETER_REQUIRED, 'The directory to scan for files to be imported', 'web/uploads/media_import'),
      new sfCommandOption('verbose', null, sfCommandOption::PARAMETER_NONE, 'Output more info about file conversions', null)
    ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'import-files';
    $this->briefDescription = 'import media files into Apostrophe';
    $this->detailedDescription = <<<EOF
The [apostrophe:import-files|INFO] task scans the specified folder, which defaults to 
web/uploads/media_import, for files and imports them into the media repository if they
are in a supported format (GIF, JPEG, PNG, PDF). THESE FILES ARE REMOVED AFTER IMPORT,
however the originals are copied to web/uploads/media_items. This was a deliberate choice
to enable users to upload new files as needed to this folder by their bulk file transfer
method of choice (FTP, SFTP, etc.) and be able to tell at a glance whether they have
been imported yet or not. Files that cannot be imported are left alone.

Call it with:

  [php symfony apostrophe:import-files --application=frontend --env=dev --dir=web/uploads/media_import|INFO]
  
Be certain to specify the right environment for the system you are running it on.

Use --verbose if you want output in non-error situations, such as a report of how many files were converted.

EOF;
  }

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
  
  // Must be public to be part of a callable
  public function importFeedback($category, $message, $file = null)
  {
    if (!is_null($file))
    {
      echo("$file: ");
    }
    if ($category === 'completed')
    {
      echo($message . " files converted\n");
    }
    elseif (($category === 'total') || ($category === 'info') || ($category === 'completed'))
    {
      if ($this->verbose)
      {
        if (($category === 'total') || ($category === 'completed'))
        {
          echo("Files converted: $message\n");
        }
        else
        {
          echo("$mesage\n");
        }
      }
    }
    else
    {
      echo("$message\n");
    }
  }
}
