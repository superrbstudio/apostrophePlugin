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
class aImportSiteTask extends sfBaseTask
{

  /**
   * DOCUMENT ME
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('file', null, sfCommandOption::PARAMETER_REQUIRED, 'Your XML file of page data', null),
      new sfCommandOption('pages', null, sfCommandOption::PARAMETER_REQUIRED, 'Directory of page xml files', null)
      // add your own options here
    ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'import-site';
    $this->briefDescription = 'Imports a site from an XML file';
    $this->detailedDescription = <<<EOF
This task imports a website from an XML file. This task is a work in progress and not yet ready
for production use. The goal is to be able to import large sites, which Symfony fixtures currently
cannot do. That's because Doctrine 1.2 is optimized for single web requests, not large datasets,
and uses too much memory in such situations. This task therefore uses PDO for better speed and lower
memory usage.

Usage:

php symfony apostrophe:import-site mypages.xml

See the Wiki for documentation of the XML format required.
EOF;
  }

  /**
   * DOCUMENT ME
   * @param mixed $args
   * @param mixed $options
   */
  protected function execute($args = array(), $options = array())
  {
    aTaskTools::signinAsTaskUser($this->configuration, $options['connection']);
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getDoctrineConnection();
    
    if(!$this->askConfirmation("Importing any content will erase any existing content, are you sure? [y/N]", 'QUESTION_LARGE', false))
    {
      die("Import CANCELLED.  No changes made.\n");
     }

    if (is_null($options['file']))
    {
      $rootDir = $this->configuration->getRootDir();
      $dataDir = $rootDir.'/data/a';
      $options['file'] = $dataDir.'/site.xml';
      $options['pages'] = $dataDir.'/pages';
      $options['images'] = $dataDir.'/images';
    }
    
    $importer = new aImporter($connection, array(
      'xmlFile' => $options['file'],
      'pagesDir' => $options['pages'],
      'imagesDir' => $options['images']
    ));
    $importer->import();
  }
}
