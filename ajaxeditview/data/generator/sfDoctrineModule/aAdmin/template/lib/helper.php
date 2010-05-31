[?php

/**  
 * <?php echo $this->getModuleName() ?> module configuration.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage <?php echo $this->getModuleName()."\n" ?>
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: helper.php 12482 2008-10-31 11:13:22Z fabien $
 */
class Base<?php echo ucfirst($this->getModuleName()) ?>GeneratorHelper extends sfModelGeneratorHelper
{
  public function linkToNew($params)
  {
    return '<li class="a-admin-action-new">'.link_to(__($params['label'], array(), 'apostrophe'), $this->getUrlForAction('new'), array() ,array("class"=>"a-btn icon big a-add")).'</li>';
  }

  public function linkToEdit($object, $params)
  {
    return '<li class="a-admin-action-edit">'.link_to(__($params['label'], array(), 'apostrophe'), $this->getUrlForAction('edit'), $object, array('class'=>'a-btn icon no-label a-edit')).'</li>';
  }

  public function linkToDelete($object, $params)
  {
    if ($object->isNew())
    {
      return '';
    }

    return '<li class="a-admin-action-delete">'.link_to(__($params['label'], array(), 'apostrophe'), $this->getUrlForAction('delete'), $object, array('class'=>'a-btn icon no-label a-delete','method' => 'delete', 'confirm' => !empty($params['confirm']) ? __($params['confirm'], array(), 'apostrophe') : $params['confirm'])).'</li>';
  }

  public function linkToList($params)
  {
    return '<li class="a-admin-action-list">'.link_to(__($params['label'], array(), 'apostrophe'), $this->getUrlForAction('list'), array(), array('class'=>'a-btn a-cancel')).'</li>';
  }

  public function linkToSave($object, $params)
  {
    return '<li class="a-admin-action-save">'.jq_link_to_function(__($params['label'], array(), 'apostrophe'), "$('#a-admin-form').submit()", array('class'=>'a-btn a-save') ).'</li>';
  }

  public function linkToSaveAndAdd($object, $params)
  {
    if (!$object->isNew())
    {
      return '';
    }
    return '<li class="a-admin-action-save-and-add">'.jq_link_to_function(__($params['label'], array(), 'apostrophe'), '$(this).after("<input type=\"hidden\" name=\"_save_and_add\" value=\"1\" id=\"a_admin_save_and_add\">");$("#a-admin-form").submit()', array('class'=>'a-btn') ).'</li>';
  }

  public function getUrlForAction($action)
  {
    return 'list' == $action ? '<?php echo $this->params['route_prefix'] ?>' : '<?php echo $this->params['route_prefix'] ?>_'.$action;
  }
}
