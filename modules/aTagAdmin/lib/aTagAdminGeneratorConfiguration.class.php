<?php
/**
 * 
 * aTagAdmin module configuration.
 * @package    aBlog
 * @subpackage aTagAdmin
 * @author     Your name here
 * @version    SVN: $Id: configuration.php 12474 2008-10-31 10:41:27Z fabien $
 */
class aTagAdminGeneratorConfiguration extends BaseaTagAdminGeneratorConfiguration
{

  /**
   * Convince the admin generator that the tag columns are "real" and can therefore
   * be sorted upon. Thanks to Dan
   * @return mixed
   */
  public function getFieldsDefault()
  {
    $models = $this->getTaggableModels();
    $fields = parent::getFieldsDefault();
    foreach ($models as $model)
    {
      if (!isset($fields['tag_' . $model]))
      {
        $fields['tag_' . $model] = array('is_link' => false,  'is_real' => true,  'is_partial' => false,  'is_component' => false,  'type' => 'Tag');
      }
    }
    return $fields;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function getTaggableModels()
  {
    $fields = array();
    foreach($this->getListDisplay() as $field)
    {
      $parts = preg_split('/^=?tag_/', $field);
      if(count($parts) > 1)
      {
        $fields[] = $parts[1];
      }
    }

    return $fields;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function getFormClass()
  {
    return 'aTagForm';
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function hasFilterForm()
  {
    return false;
  }

}