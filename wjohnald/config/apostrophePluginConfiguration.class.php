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
    // These were merged in from the separate plugins. TODO: clean up a little.
    
    if (sfConfig::get('app_a_routes_register', true) && in_array('a', sfConfig::get('sf_enabled_modules', array())))
    {
      $this->dispatcher->connect('routing.load_configuration', array('aRouting', 'listenToRoutingLoadConfigurationEvent'));
    }

    if (sfConfig::get('app_a_admin_routes_register', true))
    {
      $this->dispatcher->connect('routing.load_configuration', array('aRouting', 'listenToRoutingAdminLoadConfigurationEvent'));
    }

    // Allows us to reset static data such as the current CMS page.
    // Necessary when writing functional tests that use the restart() method
    // of the browser to start a new request - something that never happens in the
    // lifetime of the same PHP invocation under normal circumstances
    $this->dispatcher->connect('test.simulate_new_request', array('aTools', 'listenToSimulateNewRequestEvent'));

    // Register an event so we can add our buttons to the set of global CMS back end admin buttons
    // that appear when the apostrophe is clicked. We do it this way as a demonstration of how it
    // can be done in other plugins that enhance the CMS
    $this->dispatcher->connect('a.getGlobalButtons', array('aTools', 'getGlobalButtonsInternal'));
    
    $this->dispatcher->connect('a.getGlobalButtons', array('aMediaCMSSlotsTools', 
      'getGlobalButtons'));
      
    if (sfConfig::get('app_a_media_routes_register', true) && in_array('aMedia', sfConfig::get('sf_enabled_modules', array())))
    {
      $this->dispatcher->connect('routing.load_configuration', array('aMediaRouting', 'listenToRoutingLoadConfigurationEvent'));
    }
    
    $this->dispatcher->connect('command.post_command', array('aToolkitEvents',  'listenToCommandPostCommandEvent'));  
  }
}
