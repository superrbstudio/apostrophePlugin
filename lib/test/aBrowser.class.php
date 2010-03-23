<?php

class aBrowser extends sfBrowser
{
  public function restart()
  {
    parent::restart();
    $this->newRequestReset();
    
    return $this;
  }
  
  public function call($uri, $method = 'get', $parameters = array(), $changeStack = true)
  {
    parent::call($uri, $method, $parameters, $changeStack);
    $this->newRequestReset();
    
    return $this;
  }
  
  public function newRequestReset()
  {
    $this->clearTableIdentityMaps();
    $dispatcher = sfContext::getInstance()->getConfiguration()->getEventDispatcher();
    $dispatcher->notify(new sfEvent(null, 'test.simulate_new_request'));
  }
    
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
