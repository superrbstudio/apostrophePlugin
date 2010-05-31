<?php

class aNavigationTree extends aNavigation
{ 
  protected $showPeers = true;
  protected $showAncestorPeers = true;
  protected $showDescendants = 1;
  protected $showDescendantsRoot = 0;
  
  public function buildNavigation()
  {
    $this->showPeers = isset($this->options['showPeers'])? $this->options['showPeers'] : $this->showPeers;
    $this->showAncestorPeers = isset($this->options['showAncestorPeers'])? $this->options['showAncestorPeers'] : $this->showAncestorPeers;
    $this->showDescendants = isset($this->options['showDescendants'])? $this->options['showDescendants'] : $this->showDescendants;
    $this->showDescendantsRoot = isset($this->options['showDescendantsRoot'])? $this->options['showDescendantsRoot'] : $this->showDescendantsRoot; 
    $tree = $this->rootPage->getTreeInfo($this->getLivingOnly(), $this->getOption('rootDepth'));
    $this->setItems($this->createObjects($tree, null));
  }
  
  public function createObjects($tree, $parent)
  {
    $navItems = array();
    $n = 0;
    $peerBit = false;
    $ancestorPeerBit = false;
    foreach($tree as $item)
    {
      $navItem = $this->buildNavigationItem($tree, $item, $n++);
      if($navItem->isCurrent())
      {
        $peerBit = true;
      }
      elseif($navItem->isAncestor($this->activePage))
      {
        $ancestorPeerBit = true;
        $navItem->ancestorOfCurrentPage = true;
      }
      $navItem->setRelativeDepth($item['level'] - $this->rootPage->getLevel());
      if(isset($item['children']))
      {
        $navItem->setChildren($this->createObjects($item['children'], $navItem));
      }
    $navItems[] = $navItem;  
    }
    foreach($navItems as $key => $navItem)
    {
      if(!$this->showItem($navItem, $peerBit, $ancestorPeerBit))
      {
        unset($navItems[$key]);
      }
    } 
    return $navItems;
  }
  
  public function showItem(aNavigationItem $navItem, $peerBit, $ancestorPeerBit)
  {
    if($peerBit && $this->showPeers)
    {
      $navItem->peerOfCurrentPage = true;
      return true;
    }
    elseif($navItem->ancestorOfCurrentPage)
    {
      return true;
    }
    elseif($ancestorPeerBit && $this->showAncestorPeers)
    {
      $navItem->peerOfAncestorOfCurrentPage = true;
      return true;
    }
    elseif($navItem->isDescendant($this->activePage) && $navItem->getLevel() <= $this->activePage->getLevel() + $this->showDescendants)
    {
      return true;
    }
    elseif($this->showDescendantsRoot && $navItem->getLevel() <= $this->rootPage->getLevel() + $this->showDescendantsRoot )
    {
      return true;
    }
    elseif($navItem->isCurrent())
    {
      return true;
    }
    else return false;
  }
  
}