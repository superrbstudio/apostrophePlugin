<?php 

class aProjectConfiguration extends sfProjectConfiguration
{
  // Symfony's autoloader has a bug: in the absence of an application,
  // it doesn't respect project level overrides even in the main lib/ folder.
  // Fix that so rebuild-search-index and friends see project overrides.
  // Figured out by Dan at some point. Dan gets a medal at some point.
  public function loadPlugins()
  {
    parent::loadPlugins();
    if (!$this instanceof sfApplicationConfiguration)
    {
      $this->autoloadProjectLib();
    }
  }
  public function autoloadProjectLib()
  {
    $autoload = sfSimpleAutoload::getInstance(sfConfig::get('sf_cache_dir').'/project_autoload.cache');
    $files = sfFinder::type('file')->maxdepth(0)->name('*.php')->in(sfConfig::get('sf_lib_dir'));
    $dirs = sfFinder::type('directory')->prune('vendor')->not_name('vendor')->maxdepth(0)->in(sfConfig::get('sf_lib_dir'));
    $autoload->addFiles($files);
    foreach($dirs as $dir)
    {
      $autoload->addDirectory($dir);
    }
    $autoload->register();
  }
}
