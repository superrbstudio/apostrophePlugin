<?php

class aNavigationBreadcrumb extends aNavigation
{
  protected $cssClass = 'a-nav-item'; 
  public function initializeTree()
  {
    if (sfConfig::get('app_a_many_pages', true))
    {
      // The use of the static sitewide page tree 
      // requires too much memory on sites with more
      // than about 500-1000 pages. On smaller sites
      // it turns out to be a performance win to get
      // all of the page information for the site and
      // cache it for subsequent navigation elements on
      // the same page, which the base class does for us
    }
    else
    {
      parent::initializeTree();
    }
  }
  
  public function buildNavigation()
  {
    if (sfConfig::get('app_a_many_pages', true))
    {
      $activePage = aPageTable::retrieveBySlug($this->active);
      // true = include the page itself
      $this->nav = $activePage->getAncestorsInfo(true);
      $i = count($this->nav);
      $info = &$this->nav[$i - 1];
      if (!isset($info['class']))
      {
        $info['class'] = '';
      }
      $info['class'] .= ' a-current-page';
      return;
    }
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