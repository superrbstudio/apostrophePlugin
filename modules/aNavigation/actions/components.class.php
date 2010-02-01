<?php


class aNavigationComponents extends sfComponents
{
  
  public function executeAccordian()
  {
    $this->root = isset($this->root)? $this->root : '/';
    $this->active = isset($this->active)? $this->active : $this->root;
    
    
    $this->navigation = new aNavigationAccordian($this->root, $this->active, $this->options);
    $this->nav = $this->navigation->getNav();
    
    $this->nest = 0;
    $page = aPageTable::retrieveBySlug($this->root);
    $this->draggable = $page->userHasPrivilege('edit');
  }
  
  public function executeBreadcrumb()
  {
    $this->root = isset($this->root)? $this->root : '/';
    $this->active = isset($this->active)? $this->active : $this->root;
    
    $this->seperator = isset($this->seperator)? $this->seperator : ' > ';
    
    $this->navigation = new aNavigationBreadcrumb($this->root, $this->active, $this->options);
    $this->nav = $this->navigation->getNav();
  }
  
  public function executeTabs()
  {
    $this->root = isset($this->root)? $this->root : '/';
    $this->active = isset($this->active)? $this->active : $this->root;
    
    $this->navigation = new aNavigationTabs($this->root, $this->active, $this->options);
    $this->nav = $this->navigation->getNav();
  }
  
}
