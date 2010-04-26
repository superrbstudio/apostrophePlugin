<?php

class aNavigationTabs extends aNavigation
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
      $this->activeInfo = $activePage->getInfo();
      
      $rootPage = aPageTable::retrieveBySlug($this->root);
      $this->rootInfo = $rootPage->getTreeInfo(false, $this->options['depth']);
      // If no kids...
      if (!count($this->rootInfo))
      {
        // Try the parent
        $rootPage = $rootPage->getParent();
        if (!$rootPage)
        {
          // Parent does not exist - this is the home page and there are no subpages
          // (unlikely in practice due to admin pages)
          $this->rootInfo = array();
        }
        else
        {
          // Parent does exist, use its kids
          $this->rootInfo = $rootPage->getTreeInfo(false, $this->options['depth']);
        }
      }
      $this->nav = $this->rootInfo;
    }
    else
    {
      $this->rootInfo = parent::$hash[$this->root];
      $this->activeInfo = parent::$hash[$this->active];
      if(isset($this->rootInfo['children']))
      {
        $this->nav = $this->rootInfo['children'];
      }
      else
      {
        if (!isset($this->rootInfo['parent']))
        {
          // A site root with no children
          $this->nav = array();
        }
        else
        {
          $this->nav = $this->rootInfo['parent']['children'];
        }
      }
    }
    $this->depth = $this->options['depth'];
    
    $this->traverse($this->nav);
  }
  
  public function traverse(&$tree, $depth=1)
  {
    foreach($tree as $key => &$node)
    {
      $this->applyCSS($tree, $node);
        
      if(isset($node['children']) && $depth < $this->depth)
        $this->traverse($node['children'], $depth + 1);
      else
        unset($node['children']);
		  
      if($node['archived'] == true)
      {
        $node['class'] = $node['class'] . ' a-archived-paged';
        if($this->livingOnly)
          unset($tree[$key]);
      }
    }
  }
  
  public function getNav()
  {
    return $this->nav;
  }
}