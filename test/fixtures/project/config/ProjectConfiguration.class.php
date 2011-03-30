<?php

if (!isset($_SERVER['SYMFONY']))
{
  throw new RuntimeException('Could not find symfony core libraries.');
}

require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();/**
 * @package    Apostrophe
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class ProjectConfiguration extends sfProjectConfiguration
{

  /**
   * DOCUMENT ME
   */
  public function setup()
  {
    $this->setPlugins(array('apostrophePlugin'));
    $this->setPluginPath('apostrophePlugin', dirname(__FILE__).'/../../../..');
  }
}
