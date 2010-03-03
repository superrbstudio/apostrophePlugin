<?php


class BaseaNavigationComponents extends sfComponents
{
  
  public function executeAccordion()
  {
    $page = aPageTable::retrieveBySlug($this->root);

    $this->root = isset($this->root)? $this->root : '/';
    $this->active = isset($this->active)? $this->active : $this->root;

    $this->dragIcon = isset($this->dragIcon)? $this->dragIcon : false;    
    $this->draggable = isset($this->draggable)? $page->userHasPrivilege('edit'): false;

    $this->maxDepth = isset($this->maxDepth)? $this->maxDepth : 999;
    $this->navigation = new aNavigationAccordion($this->root, $this->active);
    $this->nav = $this->navigation->getNav();
  
    $this->nest = 0;

  }
  
  public function executeBreadcrumb()
  {
    $this->root = isset($this->root)? $this->root : '/';
    $this->active = isset($this->active)? $this->active : $this->root;
    
    $this->separator = isset($this->separator)? $this->separator : ' > ';
    $this->navigation = new aNavigationBreadcrumb($this->root, $this->active, $this->options);
    $this->nav = $this->navigation->getNav();
  }
  
  public function executeTabs()
  {
    $page = aPageTable::retrieveBySlug($this->root);

    $this->root = isset($this->root)? $this->root : '/';
    $this->active = isset($this->active)? $this->active : $this->root;
    
    $this->options = array('depth' => isset($this->depth)? $this->depth : 1);
    $this->depth = $this->options['depth'];
    
    $this->draggable = isset($this->draggable)? $page->userHasPrivilege('edit'): false;
    $this->dragIcon = isset($this->dragIcon)? $this->dragIcon : false;
    $this->navigation = new aNavigationTabs($this->root, $this->active, $this->options);
    $this->nav = $this->navigation->getNav();
    
  }
  
}
