<?php

class aNavigationAccordion extends aNavigation
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
      $this->rootInfo = $activePage->getAccordionInfo(false, null, $this->root);
      // This rootInfo is already an array of kids
      $this->nav = $this->rootInfo;
    }
    else
    {
      $this->rootInfo = parent::$hash[$this->root];
      $this->activeInfo = parent::$hash[$this->active];
      // This rootInfo is an individual page info
      $this->nav = $this->rootInfo['children'];
    }
    // We no longer try to special case the situation where the root page has no children,
    // because the active page should always be a descendant of the root page, and it
    // complicated the implementation
    $this->traverse($this->nav);
  }
  
  public function traverse(&$tree)
  {
    foreach($tree as $pos => &$node)
    {
      
      $this->applyCSS($tree, $node);
      
      // This is redundant if we used getAccordionInfo, and it won't work because we
      // never set activeInfo or the 'parent' pointers
      if (!sfConfig::get('app_a_many_pages', true))
      {
        if(!self::isAncestor($node, $this->activeInfo) && !($node['id'] == $this->activeInfo['id']))
        {
          unset($node['children']);
        }
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
