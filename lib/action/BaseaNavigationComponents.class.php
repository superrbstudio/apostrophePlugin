<?php
/**
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaNavigationComponents extends sfComponents
{

  /**
   * DOCUMENT ME
   */
  public function navSetup()
  {
    if(is_object($this->root) && $this->root instanceof aPage)
    {
      $this->rootPage = $this->root;
    }
    else
    {
      $this->root = isset($this->root)? $this->root : '/';
      $this->rootPage = aPageTable::retrieveBySlug($this->root);
    }

    if(is_object($this->active) && $this->active instanceof aPage)
    {
      $this->activePage = $this->active;
      $this->active = $this->active->slug;
    }
    else
    {
      $this->active = !empty($this->active)? $this->active : $this->root;
      $this->activePage = aPageTable::retrieveBySlug($this->active);
    }
    $this->dragIcon = isset($this->dragIcon)? $this->dragIcon : false;    
    $this->draggable = isset($this->draggable)? $this->rootPage->userHasPrivilege('edit'): false;

    $this->class = isset($this->class)? $this->class : 'a-nav-item';
    
    if (!isset($this->nest))
    {
      $this->nest = 0;
    }
    if (!isset($this->ulClass))
    {
      $this->ulClass = '';
    }
  }

  /**
   * DOCUMENT ME
   */
  public function executeAccordion()
  {
    $this->navSetup();
    $this->maxDepth = isset($this->maxDepth)? $this->maxDepth : 999;
    $this->navigation = new aNavigationAccordion($this->rootPage, $this->activePage, array('maxDepth' => $this->maxDepth));
    
    $this->nav = $this->navigation->getNav();    
  }

  /**
   * DOCUMENT ME
   */
  public function executeTabs()
  {
    $this->navSetup();
    $this->options = array('depth' => isset($this->depth)? $this->depth : 1);
    $this->navigation = new aNavigationTabs($this->rootPage, $this->activePage, $this->options);

    
    $this->depth = $this->options['depth'];

    $this->nav = $this->navigation->getNav();

    $this->extras = isset($this->extras)? $this->extras : array();

    $i = 0;
    $urlValidator = new sfValidatorUrl();
    foreach($this->extras as $slug => $info)
    {
      if (!is_array($info))
      {
        $info = array('title' => $info);
      }
      $title = $info['title'];
      $class = isset($info['class']) ? $info['class'] : '';
      $external = false;
      try {
        $urlValidator->clean($slug);
        $external = true;
      } catch(sfValidatorError $e) {}
      $item = array(
        'title' => $title,
        'slug' => $slug,
        'id' => $i,
        'view_is_secure' => false,
        'archived' => false,
        'extra' => true,
        'class' => $class,
        'external' => $external );

      array_unshift($this->nav, $item);
    }
  }

  /**
   * DOCUMENT ME
   */
  public function executeBreadcrumb()
  {
    $this->navSetup();
    
    $this->separator = isset($this->separator)? $this->separator : ' > ';
    $this->navigation = new aNavigationBreadcrumb($this->rootPage, $this->activePage, $this->options);
    $this->nav = $this->navigation->getNav();
  }
  
}
