<?php
/**
 * @package    apostrophePlugin
 * @subpackage    navigation
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
abstract class aNavigation
{

  public static $tree = null;
  public static $hash = null;

  /**
   * Functional testing reuses the same PHP session, we must
   * accurately simulate a new one. This method is called by
   * an event listener in aTools. Add more calls there for other
   * classes that do static caching
   */
  public static function simulateNewRequest()
  {
    if (sfConfig::get('app_a_many_pages', false))
    {

    }
  }

  /**
   * DOCUMENT ME
   */
  protected abstract function buildNavigation();

  /**
   * DOCUMENT ME
   * @param mixed $root
   * @param mixed $active
   * @param mixed $options
   */
  public function __construct($root, $active, $options = array())
  {
    $this->user = sfContext::getInstance()->getUser();
    $this->livingOnly = !(aTools::isPotentialEditor() &&  sfContext::getInstance()->getUser()->getAttribute('show-archived', true, 'apostrophe'));

    $this->root = $root;
    $this->active = $active;
    $this->options = $options;

    $this->buildNavigation();
  }

  /**
   * DOCUMENT ME
   * @param mixed $tree
   */
  public function traverse(&$tree)
  {
    foreach($tree as $pos => &$node)
    {
      if( isset($node['children']) && count($node['children']) )
        $this->traverse($node['children']);

      if($node['lft'] < $this->active['lft'] && $node['rgt'] > $this->active['rgt'])
      {
        $node['ancestor'] = true;
        foreach($tree as $pos => &$peer)
        {
          if($peer != $node)
          {
            $peer['ancestor-peer'] = true;
          }
        }
      }
    }
  }

}