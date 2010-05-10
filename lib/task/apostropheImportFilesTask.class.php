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
      new sfCommandOption('verbose', null, sfCommandOption::PARAMETER_REQUIRED, 'Output more info about file conversions', false)
      // add your own options here
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
    
    $dir_iterator = new RecursiveDirectoryIterator($options['dir']);
    $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
    $count = 0;
    foreach ($iterator as $sfile)
    {
      if ($sfile->isFile())
      {
        $file = $sfile->getPathname();
        if (preg_match('/(^|\/)\./', $file))
        {
          # Silently ignore all dot folders to avoid trouble with svn and friends
          echo("Ignoring $file\n");
          continue;
        }
        $pathinfo = pathinfo($file);
        if ($pathinfo['filename'] === 'Thumbs.db')
        {
          continue;
        }
        $info = aImageConverter::getInfo($file);
        if ($info === false)
        {
          echo("Skipping $file not supported or corrupt (is this a BMP with a JPG extension etc?)\n");
          continue;
        }
        $item = new aMediaItem();
        if ($info['format'] === 'pdf')
        {
          $item->type = 'pdf';
        }
        else
        {
          $item->type = 'image';
        }
        // Split it up to make tags out of the portion of the path that isn't dir (i.e. the folder structure they used)
        $dir = $options['dir'];
        $dir = preg_replace('/\/$/', '', $dir) . '/';
        $relevant = preg_replace('/^' . preg_quote($dir, '/') . '/', '', $file);
        // TODO: not Microsoft-friendly, might matter in some setting
        $components = preg_split('/\//', $relevant);
        $tags = array_slice($components, 0, count($components) - 1);
        foreach ($tags as &$tag)
        {
          // We don't strictly need to be this harsh, but it's safe and definitely
          // takes care of some things we definitely can't allow, like periods
          // (which cause mod_rewrite problems with pretty Symfony URLs).
          // TODO: clean it up in a nicer way without being UTF8-clueless
          // (aTools::slugify is UTF8-safe)
          $tag = aTools::slugify($tag);
        }
        $item->title = aTools::slugify($pathinfo['filename']);
        $item->setTags($tags);
        if (!strlen($item->title))
        {
          echo("File must have a basename to convert to a title. Ignoring $file\n");
          continue;
        }
        // The preSaveImage / save / saveImage dance is necessary because
        // the sluggable behavior doesn't kick in until save and the image file
        // needs a slug based filename.
        if (!$item->preSaveImage($file))
        {
          echo("Save FAILED for $file\n");
          continue;
        }
        $item->save();
        if (!$item->saveImage($file))
        {
          echo("Save FAILED for $file\n");
          $item->delete();
          continue;
        }
        unlink($file);
        $count++;
      }
    }
    if ($count)
    {
      if ($options['verbose'])
      {
        echo("Converted $count files.\n");
      }
    }
  }
}
