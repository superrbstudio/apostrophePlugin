<?php

class aNavigationTabs extends aNavigation
{
  protected $cssClass = 'a-tab-nav-item'; 
  public function buildNavigation()
  {
    $this->rootInfo = parent::$hash[$this->root];
    $this->activeInfo = parent::$hash[$this->active];
    $this->nav = $this->rootInfo['children'];
    $this->depth = $this->options['depth'];
    $this->traverse($this->nav);
  }
  
  public function traverse(&$tree, $depth=1)
  {
    foreach($tree as &$node)
    {
      $node['class'] = $this->cssClass;
      if($node['id'] == $this->activeInfo['id'])
        $node['class'] = $node['class'].' current';
      if(isset($node['children']) && $depth < $this->depth)
        $this->traverse($node['children'], $depth + 1);
    }
  }
  
  public function getNav()
  {
    return $this->nav;
  }
}