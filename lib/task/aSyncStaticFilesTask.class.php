<?php

class aSyncStaticFilesTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('sync-uploads', null, sfCommandOption::PARAMETER_NONE, 'Include uploads in sync (only for initial deployment!)'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'sync-static-files';
    $this->briefDescription = 'sync static files (css, js, plugin assets...) to s3 or similar';
    $this->detailedDescription = <<<EOF
The [apostrophe:sync-static-files|INFO] task copies everything in the web/ folder, except
for .php, .svn and .htaccess files and the uploads folder, to the location specified by 
app_a_static_dir. Together with a_include_stylesheets, a_include_javascripts and a_image_tag, 
this allows all static asset files and Apostrophe media items to be delivered by a separate 
server, for instance Amazon S3.

For this to work app_a_static_path must be accessible via the filesystem or via a registered
PHP stream wrapper, such as our aS3StreamWrapper.

Note that web/uploads is NOT copied because this would OVERWRITE what your users have been
uploading in production, which is bad. However as part of a migration to S3 you can specify 
--sync-uploads to include web/uploads in the operation. You wouldn't want to do this twice.

There's more to the project of scaling an Apostrophe site into the cloud. See:

http://trac.apostrophenow.org/wiki/ManualScaling

For details.

EOF;
  }

  /**
   * DOCUMENT ME
   * @param mixed $arguments
   * @param mixed $options
   */
  protected function execute($arguments = array(), $options = array())
  {
    // The relevant settings are generally environment and application specific
    $appConfiguration = ProjectConfiguration::getApplicationConfiguration($options['application'], $options['env'], true);
    sfContext::createInstance($appConfiguration);
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($appConfiguration);
    // Sync all files in web/ except those that are inappropriate. We exclude
    // fckeditor because it is (1) huge and (2) not possible to load from the cloud
    // anyway due to security restrictions on iframes
    $exclude = array('/\.php$/', '/\/\.svn$/', '/\/\.htaccess$/', '/\/apostrophePlugin\/js\/fckeditor/');
    // Normally we do not sync /uploads (as this would trash production content!) but
    // it is useful to explicitly do so on first deploy or migration of a site to S3
    if (!$options['sync-uploads'])
    {
      $exclude[] = '/\/uploads.*/';
    }
    if (sfConfig::get('app_a_static_dir') === null)
    {
      echo("app_a_static_dir not set, no need to sync.");
      exit(0);
    }
    // aFiles::sync copies modified stuff recursively
    aFiles::sync(sfConfig::get('sf_web_dir'), sfConfig::get('app_a_static_dir'), array('exclude' => $exclude));
    
  }
}
