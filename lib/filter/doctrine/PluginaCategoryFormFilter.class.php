<?php
/**
 * 
 * PluginaCategory form.
 * @package    ##PROJECT_NAME##
 * @subpackage filter
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfDoctrineFormFilterPluginTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
abstract class PluginaCategoryFormFilter extends BaseaCategoryFormFilter
{

  /**
   * DOCUMENT ME
   * @return mixed
   */
  protected function getUseFields()
  {
    $useFields = array();
    $useFields[] = 'groups_list';
    $useFields[] = 'users_list';
    return $useFields;
  }

  /**
   * DOCUMENT ME
   */
  public function setup()
  {
    parent::setup();    
    $this->useFields($this->getUseFields());
  }
}
