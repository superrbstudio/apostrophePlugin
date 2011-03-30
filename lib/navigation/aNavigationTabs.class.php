<?php
/**
 * @package    apostrophePlugin
 * @subpackage    navigation
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aNavigationTabs extends aNavigation
{

  /**
   * DOCUMENT ME
   */
  public function buildNavigation()
  {      
    $this->rootInfo = $this->root->getTreeInfo($this->livingOnly, $this->options['depth']);

    if (!count($this->rootInfo))
    {
      // Try the parent
      $parent = $this->root->getParent();
      if (!$parent)
      {
        // Parent does not exist - this is the home page and there are no subpages
        // (unlikely in practice due to admin pages)
        $this->rootInfo = array();
      }
      else
      {
        // Parent does exist, use its kids
        $this->rootInfo = $parent->getTreeInfo($this->livingOnly, $this->options['depth']);
      }
    }
    $this->nav = $this->rootInfo;
    $this->traverse($this->nav);
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