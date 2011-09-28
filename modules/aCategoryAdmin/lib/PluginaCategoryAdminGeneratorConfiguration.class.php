<?php

/**
 * 
 * aCategoryAdmin module configuration.
 * @package    aBlog
 * @subpackage aCategoryAdmin
 * @author     Your name here
 * @version    SVN: $Id: configuration.php 12474 2008-10-31 10:41:27Z fabien $
 */
class PluginaCategoryAdminGeneratorConfiguration extends BaseaCategoryAdminGeneratorConfiguration
{
  public $fields;

  /**
   * DOCUMENT ME
   */
  public function __construct()
  {
    $event = new sfEvent(null, 'a.get_categorizables');
    sfContext::getInstance()->getEventDispatcher()->filter($event, array());
    $this->fields = $event->getReturnValue();
    parent::__construct();
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function getFieldsDefault()
  {
    $fields = parent::getFieldsDefault();
    foreach($this->fields as $info)
    {
      $fields[$info['class']] = array('is_link' => false,  'is_real' => false,  'is_partial' => false,  'is_component' => false,  'type' => 'Category');
    }
    return $fields;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function getListDisplay()
  {
    $fields = parent::getListDisplay();
    foreach($this->fields as $info)
    {
      $fields[] = $info['class'];
    }
    return $fields;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function getFieldsList()
  {
    $fields = parent::getFieldsList();
    foreach($this->fields as $info)
    {
      $fields[$info['class']] = array('label' => $info['name']);
    }
    return $fields;
  }


}