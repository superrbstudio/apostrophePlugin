<?php

/**
 * apostrophePlugin configuration.
 * 
 * @package     apostrophePlugin * @subpackage  config
 */
class apostrophePluginConfiguration extends sfPluginConfiguration
{
  /**
   * @see sfPluginConfiguration
   */  public function initialize()
  {
    if (sfConfig::get('app_a_media_plugin_routes_register', true) && in_array('aMedia', sfConfig::get('sf_enabled_modules', array())))
    {
      $this->dispatcher->connect('routing.load_configuration', array('aMediaRouting', 'listenToRoutingLoadConfigurationEvent'));
    }
  }
}


