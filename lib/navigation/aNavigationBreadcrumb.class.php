<?php

class aNavigationBreadcrumb extends aNavigation
{
  protected $cssClass = 'a-breadcrumb-nav-item'; 
  public function buildNavigation()
  {
    $this->rootInfo = parent::$hash[$this->root];
    $this->activeInfo = parent::$hash[$this->active];
    $this->nav = array();
    $tree = array($this->rootInfo);
    $this->traverse($tree);
  }
  
  public function traverse(&$tree)
  {
    foreach($tree as &$node)
    {
      $node['class'] = $this->cssClass;
      if(self::isAncestor($node, $this->activeInfo))
      {
        $this->nav[] = $node;
        $this->traverse($node['children']);
      }
      else if($node['id'] == $this->activeInfo['id'])
      {
        $node['class'] = $node['class']. " a-current-page";
        $this->nav[] = $node;
      }
    }
  }
    
  public function getNav()
  {
    return $this->nav;
  }
  
}