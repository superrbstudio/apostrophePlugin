<?php

/**
 * aTagAdmin module configuration.
 *
 * @package    aBlog
 * @subpackage aTagAdmin
 * @author     Your name here
 * @version    SVN: $Id: configuration.php 12474 2008-10-31 10:41:27Z fabien $
 */
class aTagAdminGeneratorConfiguration extends BaseaTagAdminGeneratorConfiguration
{

  public function getTaggableModels()
  {
    $fields = array();
    foreach($this->getListDisplay() as $field)
    {
      $parts = preg_split('/^tag_/', $field);
      if(count($parts) > 1)
      {
        $fields[] = $parts[1];
      }
    }

    return $fields;
  }

  public function getFormClass()
  {
    return 'aTagForm';
  }

	// public function hasFilterForm()
	// {
	// 	return false;
	// }
	// 

}