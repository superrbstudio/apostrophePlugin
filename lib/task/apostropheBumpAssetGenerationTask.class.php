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
class apostropheBumpAssetGenerationTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('filename', null, sfCommandOption::PARAMETER_REQUIRED, 'The asset_generation.yml file name', null),
      // add your own options here
    ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'bump-asset-generation';
    $this->briefDescription = 'Changes app_a_asset_generation';
    $this->detailedDescription = <<<EOF
When the minifier is active it is possible to avoid the need to clear the browser cache to 
see new assets by setting app_a_asset_generation to a new value on each deployment. This task
facilitates that by updating apps/frontend/config/asset_generation.yml with a new
random string. This works only if app.yml is set up to actually load that file 
inside the a: key (see the sandbox project).

You can specify an alternate location for asset_generation.yml via the --filename option.

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
    // So we can play with app.yml settings from the application
    $context = sfContext::createInstance($this->configuration);
    if (is_null($options['filename']))
    {
      $options['filename'] = sfConfig::get('sf_root_dir') . '/apps/' . $options['application'] . '/config/asset_generation.yml';
    }
    $generation = mt_rand(0, 1000000000) . mt_rand(0, 1000000000);
    file_put_contents($options['filename'], <<<EOM
    # Think I look useful? See http://trac.apostrophenow.org/wiki/ManualDeployment
    # for what to put in app.yml in order to load this effectively.
    asset_generation: $generation

EOM
    );
  }
}
