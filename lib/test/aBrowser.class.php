<?php
/**
 * @package    apostrophePlugin
 * @subpackage    test
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aBrowser extends sfBrowser
{

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function restart()
  {
    parent::restart();
    $this->newRequestReset();
    
    return $this;
  }

  /**
   * DOCUMENT ME
   * @param mixed $uri
   * @param mixed $method
   * @param mixed $parameters
   * @param mixed $changeStack
   * @return mixed
   */
  public function call($uri, $method = 'get', $parameters = array(), $changeStack = true)
  {
    parent::call($uri, $method, $parameters, $changeStack);
    $this->newRequestReset();
    
    return $this;
  }

  /**
   * DOCUMENT ME
   */
  public function newRequestReset()
  {
    $this->clearTableIdentityMaps();
    $dispatcher = sfContext::getInstance()->getConfiguration()->getEventDispatcher();
    $dispatcher->notify(new sfEvent(null, 'test.simulate_new_request'));
  }

  /**
   * DOCUMENT ME
   */
  protected function clearTableIdentityMaps()
  {
    $c = Doctrine_Manager::getInstance()->getCurrentConnection();

    $tables = $c->getTables();

    foreach ($tables as $table) 
    {
      $table->clear();
    }
  }
}
