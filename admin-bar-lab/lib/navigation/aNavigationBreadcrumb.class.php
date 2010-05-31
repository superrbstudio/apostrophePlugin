<?php

class aNavigationBreadcrumb extends aNavigation
{
 
  public function buildNavigation()
  {
    // true = include the page itself
    $this->nav = $this->active->getAncestorsInfo(true);
    $i = count($this->nav);
    $info = &$this->nav[$i - 1];
  }
    
  public function getNav()
  {
    return $this->nav;
  }
  
}