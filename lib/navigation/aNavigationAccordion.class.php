<?php
/**
 * @package    apostrophePlugin
 * @subpackage    navigation
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aNavigationAccordion extends aNavigation
{
  protected $cssClass = 'a-nav-item';

  /**
   * DOCUMENT ME
   */
  public function buildNavigation()
  {
    $this->activeInfo = $this->active->getInfo();
    if($this->active['slug'] != $this->root['slug'])
    {
      $this->rootInfo = $this->active->getAccordionInfo($this->livingOnly, null, $this->root['slug']);
    }else
    {
      $this->rootInfo = $this->root->getTreeInfo($this->livingOnly, 1);
    }
    // This rootInfo is already an array of kids
    $this->nav = $this->rootInfo;

    // We no longer try to special case the situation where the root page has no children,
    // because the active page should always be a descendant of the root page, and it
    // complicated the implementation
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
