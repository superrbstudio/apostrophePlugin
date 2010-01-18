<?php

class aNavigationItem
{
  protected $name = '';
  protected $url = '';
  protected $slug = '';
  protected $first = false;
  protected $last = false;
  protected $current = false;
  protected $options = array();
  
  protected $children = array();
  protected $peers = array();
  
  protected $level;
  protected $relativeDepth;
  public $ancestorOfCurrentPage = false;
  public $peerOfAncestorOfCurrentPage = false;
  public $peerOfCurrentPage = false;

  public function __construct($pageInfo, $url, $options = array(), $children = array())
  {
    $this->name = $pageInfo['title'];
    $this->url = $url;
    $this->lft = $pageInfo['lft'];
    $this->rgt = $pageInfo['rgt'];
    $this->slug = $pageInfo['slug'];
    $this->id = $pageInfo['id'];
    $this->level = $pageInfo['level'];
    
    $this->options = $options;
    $this->first = isset($this->options['first']) ? $this->options['first'] : '';
    $this->last = isset($this->options['last']) ? $this->options['last'] : '';
    $this->current = isset($this->options['current']) ? $this->options['current'] : '';
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function setName($name)
  {
    return $this->name = $name;
  }
  
  public function getUrl()
  {
    return $this->url;
  }
  
  public function isLast()
  {
    return $this->last;
  }
  
  public function isFirst()
  {
    return $this->first;
  }
  
  public function isCurrent()
  {
    return $this->current;
  }

  public function setChildren($items = array())
  {
    $this->children = $items;
  }
  
  public function getChildren()
  {
    return $this->children;
  }
  
  public function hasChildren()
  {
    return count($this->children) > 0;
  }
  
  public function setRelativeDepth($relativeDepth)
  {
    $this->relativeDepth = $relativeDepth;
  }
  
  public function getRelativeDepth()
  {
    return $this->relativeDepth;
  }
  
  public function getAbsoluteDepth()
  {
    return $this->level;
  }
  
  public function getLevel()
  {
    return $this->level;
  }
  
  public function isAncestor(aPage $page)
  {
    return ($this->lft < $page->lft && $this->rgt > $page->rgt)? true : false;
  }
  
  public function isDescendant(aPage $page, $offset=null)
  {
    if($this->lft > $page->lft && $this->rgt < $page->rgt)
    {
      if(isset($offset))
      {
        return $page->getLevel() + $offset >= $this->getAbsoluteDepth(); 
      }
      return true;
    }
    return false;
  }
}

?>