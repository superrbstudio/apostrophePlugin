<?php

class aNavigationBreadcrumb extends aNavigationTree
{
  protected $template = "navigation";
  protected $showPeers = false;
  protected $showAncestorPeers = false;
  protected $showDescendants = false;
  
  public function buildNavigation($options = null)
  {
    $tree = $this->rootPage->getTreeInfo($this->getLivingOnly(), $this->getOption('rootDepth'));
    $rootArray = $this->rootPage->toArray(false);
    $rootArray['title'] = $this->rootPage->getTitle();
    $rootArray['children'] = $tree;
    $this->setItems($this->createObjects(array($rootArray), null));
  }
  
}