<?php

class aNavigationAccordion extends aNavigation
{
  protected $cssClass = 'a-nav-item'; 
  public function buildNavigation()
  {
    $this->rootInfo = parent::$hash[$this->root];
    $this->activeInfo = parent::$hash[$this->active];
    if(isset($this->rootInfo['children']))
    {
      $this->nav = $this->rootInfo['children'];
    }
    else
    {
      $this->nav = $this->rootInfo['parent']['children'];
    }
    $this->traverse($this->nav);
  }
  
  public function traverse(&$tree)
  {
    foreach($tree as $pos => &$node)
    {
      
      $this->applyCSS($tree, $node);
      
      if(!self::isAncestor($node, $this->activeInfo) || !$node['id'] == $this->activeInfo['id'])
      {
        unset($node['children']);
      }
      
      if( isset($node['children']) && count($node['children']) )
        $this->traverse($node['children']);
        
      if($node['archived'] == true)
      {
        $node['class'] = $node['class'] . ' a-archived-page';
        if($this->livingOnly)
          unset($tree[$pos]);
      }
    }  
  }
  
  public function getNav()
  {
    return $this->nav;
  }
  
}
