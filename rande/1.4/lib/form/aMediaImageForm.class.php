<?php

class aMediaImageForm extends aMediaItemForm
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
    unset($this['type']);
    unset($this['service_url']);
    unset($this['slug']);
    unset($this['width']);
    unset($this['height']);
    unset($this['format']);
    
    $this->setWidget('file', new aWidgetFormInputFilePersistent(array(
      // Not yet
      // "iframe" => true,
      // "progress" => "Uploading...",
      'image-preview' => aMediaTools::getOption('gallery_constraints')
    )));

    $item = $this->getObject();
    if (!$item->isNew())
    {
      $this->getWidget('file')->setOption('default-preview', $item->getOriginalPath());
    }
    
    $this->setValidator('file', new aValidatorFilePersistent(array(
      'mime_types' => array('image/jpeg', 'image/png', 'image/gif'), 
      'required' => (!$this->getObject()->getId())
    ), array(
      'mime_types' => 'JPEG, PNG and GIF only.',
      'required' => 'Select a JPEG, PNG or GIF file')
    ));
    
    $this->setValidator('title', new sfValidatorString(array(
      'min_length' => 3,
      'max_length' => 200,
      'required' => true
    ), array(
      'min_length' => 'Title must be at least 3 characters.',
      'max_length' => 'Title must be <200 characters.',
      'required' => 'You must provide a title.')
    ));

		$this->setWidget('view_is_secure', new sfWidgetFormSelectRadio(array(
		  'choices' => array(0 => 'Public', 1 => 'Hidden'),
		  'default' => 0
		)));
	
		$this->setValidator('view_is_secure', new sfValidatorBoolean());

    $this->widgetSchema->setLabel('view_is_secure', 'Permissions');
    $this->widgetSchema->setNameFormat('a_media_item[%s]');
    // $this->widgetSchema->setFormFormatterName('aAdmin');
    
    $this->widgetSchema->setLabel('media_categories_list', 'Categories');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
  }
  
  public function updateObject($values = null)
  {
    if (!isset($values))
    {
      $values = $this->getValues();
    }
    $object = parent::updateObject($values);
    $object->type = 'image';
    return $object;
  }
}