<?php

abstract class aNavigation
{
  
  public static $tree = null;
  public static $hash = null;
  
  // Functional testing reuses the same PHP session, we must
  // accurately simulate a new one. This method is called by
  // an event listener in aTools. Add more calls there for other
  // classes that do static caching
  public static function simulateNewRequest()
  {
    if (sfConfig::get('app_a_many_pages', false))
    {
      
    }
  }
  
  protected abstract function buildNavigation();
  
  public function __construct(aPage $root, aPage $active, $options = array())
  {
    $this->user = sfContext::getInstance()->getUser();
    $this->livingOnly = !(aTools::isPotentialEditor() &&  sfContext::getInstance()->getUser()->getAttribute('show-archived', true, 'apostrophe'));
    
    $this->root = $root;
    $this->active = $active;
    $this->options = $options;

    $this->buildNavigation();
  }

  public function traverse(&$tree)
  {
    foreach($tree as $pos => &$node)
    {
      if( isset($node['children']) && count($node['children']) )
        $this->traverse($node['children']);

      if($node['lft'] < $this->active['lft'] && $node['rgt'] > $this->active['rgt'])
      {
        $node['ancestor'] = true;
        foreach($tree as $pos => &$peer)
        {
          if($peer != $node)
          {
            $peer['ancestor-peer'] = true;
          }
        }
      }

      if($node['archived'] == true)
      {
        if($this->livingOnly)
          unset($tree[$pos]);
      }
    }
  }
  
}