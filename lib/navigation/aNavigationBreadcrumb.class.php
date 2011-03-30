<?php
/**
 * @package    apostrophePlugin
 * @subpackage    navigation
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aNavigationBreadcrumb extends aNavigation
{

  /**
   * DOCUMENT ME
   */
  public function buildNavigation()
  {
    // true = include the page itself
    $this->nav = $this->active->getAncestorsInfo(true);
    $i = count($this->nav);
    $info = &$this->nav[$i - 1];
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function getNav()
  {
    return $this->nav;
  }
  
}