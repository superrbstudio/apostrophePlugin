<?php

class aNavigationTabs extends aNavigation
{
  protected $cssClass = 'a-tab-nav-item'; 
  public function buildNavigation()
  {
    $this->rootInfo = parent::$hash[$this->root];
    $this->activeInfo = parent::$hash[$this->active];
    $this->nav = $this->rootInfo['children'];
    $this->traverse($this->nav);
  }
  
  public function traverse(&$tree)
  {
    foreach($tree as &$node)
    {
      $node['class'] = $this->cssClass;
      if($node['id'] == $this->activeInfo['id'])
        $node['class'] = $node['class'].' current';
    }
  }
  
  public function getNav()
  {
    return $this->nav;
  }
}