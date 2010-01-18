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
    // Register an event so we can add our buttons to the set of global CMS back end admin buttons
    // that appear when the apostrophe is clicked. 
    $this->dispatcher->connect('command.post_command', array('aToolkitEvents',  'listenToCommandPostCommandEvent'));
  }
}
