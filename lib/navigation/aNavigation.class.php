<?php

abstract class aNavigation
{
  
  public static $tree = null;
  public static $hash = null;
  
  protected abstract function buildNavigation();
  
  
  public function initializeTree()
  {
    if(!isset(self::$tree))
    {
      $root = aPageTable::retrieveBySlugWithTitles('/');
      
      $rootInfo['id'] = $root['id'];
      $rootInfo['lft'] = $root['lft'];
      $rootInfo['rgt'] = $root['rgt'];
      $rootInfo['title'] = $root['title'];
      $rootInfo['slug'] = $root['slug'];
      
      $tree = $root->getTreeInfo(false);
      $rootInfo['children'] = $tree;
      self::$tree = array($rootInfo);
      self::createHashTable(self::$tree);
    }
  }
  
  public function createHashTable($tree)
  {
    foreach ( $tree as $node )
    {
      self::$hash[$node['slug']] = $node;
      if(isset($node['children']))
        $this->createHashTable($node['children']);
    } 
  }
  
  public function __construct($root, $active, $options = array())
  {
    $this->user = sfContext::getInstance()->getUser();
    $this->livingOnly = !(aTools::isPotentialEditor() &&  sfContext::getInstance()->getUser()->getAttribute('show-archived', true, 'a'));
    
    $this->initializeTree();
    $subTree = self::$hash[$root];

    $this->root = $root;
    $this->active = $active;
    
    $this->buildNavigation();
  }
  
  public static function isAncestor($node1, $node2)
  {
    return $node1['lft'] < $node2['lft'] && $node1['rgt'] > $node2['rgt'];
  }
  
  public static function isChild($node1, $node2)
  {
    return $node1['lft'] > $node2['lft'] && $node1['rgt'] < $node2['rgt'] && $node1['lvl'] - $node2['lvl'] == 1;
  }

}