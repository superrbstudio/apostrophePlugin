<?php

class BaseaMediaVideoForm extends aMediaItemForm
{
  // Use this to i18n select choices that SHOULD be i18ned. It never gets called,
  // it's just here for our i18n-update task to sniff
  private function i18nDummy()
  {
    __('Public', null, 'apostrophe');
    __('Hidden', null, 'apostrophe');
  }
  
  public function configure()
  {
    unset($this['id'], $this['type'], $this['slug'], $this['width'], $this['height'], $this['format']);
    $object = $this->getObject();
//    if ($object->embed)
//    {
//      unset($this['service_url']);
//      $this->setValidator('embed',
//        new sfValidatorText(
//          array('required' => true, 'trim' => true),
//          array('required' => "Not a valid embed code")));
//    }
//    else
//    {
//      unset($this['embed']);
      $this->setValidator('service_url',
        new sfValidatorUrl(
          array('required' => true, 'trim' => true),
          array('required' => "Not a valid YouTube URL")));
//    }
	

		$this->setWidget('view_is_secure', new sfWidgetFormChoice(
			array(
				'expanded' => true,
			  'choices' => array(
				0 => "Public",
				1 => "Hidden"
				),
				'default' => 0
				)));
    $this->setValidator('view_is_secure', new sfValidatorBoolean());
    $this->widgetSchema->setLabel("view_is_secure", "Permissions");
    $this->widgetSchema->setlabel("media_categories_list", "Categories");
    $this->widgetSchema->setFormFormatterName('aAdmin');  
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
    
  }
  public function updateObject($values = null)
  {
    $object = parent::updateObject($values);
    $object->type = 'video';
    return $object;
  }
}
