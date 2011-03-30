<?php
/**
 * @package    apostrophePlugin
 * @subpackage    filter
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaUserAdminFilter extends sfGuardUserFormFilter
{

  /**
   * DOCUMENT ME
   */
  public function configure()
  {
    // TODO: it would be nice to have blog_categories_list and other things without writing 
    // code specific to other plugins here. Use an event? My main goal in limiting this list
    // was to prevent memory usage from exploding when you add more relations
    $this->useFields(array('username', 'is_active', 'is_super_admin', 'last_login', 'created_at', 'groups_list'));
    $this->widgetSchema->setLabel('username', 'Name');
  }

  /**
   * DOCUMENT ME
   * @param Doctrine_Query $query
   * @param mixed $field
   * @param mixed $value
   * @return mixed
   */
  public function addUsernameColumnQuery(Doctrine_Query $query, $field, $value)
  {
    // You get an associative array with an sfWidgetFormFilterInput
    if ((!isset($value['text'])) || (!strlen($value['text'])))
    {
      return;
    }
    $like = '%' . $value['text'] . '%';
    $r = $query->getRootAlias();
    $query->addWhere("($r.username LIKE ?) OR concat($r.first_name, $r.last_name) LIKE ?", array($like, $like));
  }
}
