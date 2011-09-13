<?php
/**
 * 
 * apostrophePlugin configuration.
 * @package     apostrophePlugin * @subpackage  config
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class apostrophePluginConfiguration extends sfPluginConfiguration
{

  /**
   * 
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    // These were merged in from the separate plugins. TODO: clean up a little.
    
    // There is no more "default" routing outside the sandbox project. We never used it (thus never tested it) and it stopped working 
    // with the introduction of plugins like the blog that need routes that appear before the a_page route, so it's gone.
    // It's very simple to write an a_page route at the project level that puts CMS pages somewhere other than
    // the root, just edit the one that's in our sandbox project routing.yml.
    
    // Routes for various admin modules
    
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

    $this->dispatcher->connect('command.pre_command', array('aToolkitEvents',  'listenToCommandPreCommandEvent'));  
    
    $this->dispatcher->connect('command.post_command', array('aToolkitEvents',  'listenToCommandPostCommandEvent'));  

    $this->dispatcher->connect('a.get_categorizables', array($this, 'listenToGetCategorizables'));
    
    $this->dispatcher->connect('a.get_count_by_category', array($this, 'listenToGetCountByCategory'));

    $this->dispatcher->connect('a.merge_category', array($this, 'listenToMergeCategory'));
    
    $class = sfConfig::get('app_a_search_service_class', null);
    if ($class)
    {
      aTools::$searchService = new $class(sfConfig::get('app_a_search_service_options', null));
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $event
   * @param mixed $results
   * @return mixed
   */
  public function listenToGetCategorizables($event, $results)
  {
    // You must play nice and append to what is already there
    $info = array('class' => 'aMediaItem', 'name' => 'Media', 'relation' => 'MediaItems', 'refClass' => 'aMediaItemToCategory');
    $results['aMediaItem'] = $info;
    return $results;
  }

  /**
   * Also includes the above info so we know what the result is referring to
   * @param mixed $event
   * @param mixed $results
   * @return mixed
   */
  public function listenToGetCountByCategory($event, $results)
  {
    // You must play nice and append to what is already there
    $info = array('class' => 'aMediaItem', 'name' => 'Media');
    $counts = Doctrine::getTable('aMediaItem')->getCountByCategory();
    $info['counts'] = $counts;
    $results['aMediaItem'] = $info;
    return $results;
  }

  /**
   * DOCUMENT ME
   * @param mixed $event
   */
  public function listenToMergeCategory($event)
  {
    $parameters = $event->getParameters();
    Doctrine::getTable('aMediaItemToCategory')->mergeCategory($parameters['old_id'], $parameters['new_id']);
    Doctrine::getTable('aPageToCategory')->mergeCategory($parameters['old_id'], $parameters['new_id']);
    Doctrine::getTable('aCategoryUser')->mergeCategory($parameters['old_id'], $parameters['new_id']);
    Doctrine::getTable('aCategoryGroup')->mergeCategory($parameters['old_id'], $parameters['new_id']);
  }
}
