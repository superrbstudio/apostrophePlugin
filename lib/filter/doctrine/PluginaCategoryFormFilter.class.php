<?php

/**
 * PluginaCategory form.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage filter
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfDoctrineFormFilterPluginTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
abstract class PluginaCategoryFormFilter extends BaseaCategoryFormFilter
{
  protected function getUseFields()
  {
    $useFields = array();
    $event = new sfEvent(null, 'apostrophe.get_categorizables');
    sfContext::getInstance()->getEventDispatcher()->filter($event, array());
    $infos = $event->getReturnValue();
    foreach ($infos as $info)
    {
      $table = Doctrine::getTable($info['class']);
      $useFields[] = $table->getCategoryColumn();
    }
    $useFields[] = 'groups_list';
    $useFields[] = 'users_list';
    return $useFields;
  }
  
  public function setup()
  {
    parent::setup();    
    $this->useFields($this->getUseFields());
  }
}
