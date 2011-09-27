<?php
/**
 * 
 * aCategoryAdmin module helper.
 * @package    a
 * @subpackage aCategoryAdmin
 * @author     Your name here
 * @version    SVN: $Id: helper.php 12474 2008-10-31 10:41:27Z fabien $
 */
class aCategoryAdminGeneratorHelper extends BaseaCategoryAdminGeneratorHelper
{
  public $counts;

  /**
   * DOCUMENT ME
   */
  public function __construct()
  {
    $event = new sfEvent(null, 'a.get_count_by_category');
    sfContext::getInstance()->getEventDispatcher()->filter($event, array());
    $this->counts = $event->getReturnValue();
  }

  /**
   * DOCUMENT ME
   * @param mixed $class
   * @param mixed $category_id
   * @return mixed
   */
  public function getCount($class, $category_id)
  {
    return isset($this->counts[$class]['counts'][$category_id]['count'])? $this->counts[$class]['counts'][$category_id]['count'] : 0;
  }
}