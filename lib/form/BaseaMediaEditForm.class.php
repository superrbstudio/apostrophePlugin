<?php

class BaseaMediaEditForm extends aMediaItemForm
{
  // Use this to i18n select choices that SHOULD be i18ned. It never gets called,
  // it's just here for our i18n-update task to sniff
  private function i18nDummy()
  {
    __('Public', null, 'apostrophe');
    __('Hidden', null, 'apostrophe');
    __('Replace File', null, 'apostrophe');
  }
  
  public function configure()
  {
    // This call was missing, preventing easy extension of all media item edit forms at the project level
    parent::configure();
    unset($this['id']);
    unset($this['type']);
    unset($this['service_url']);
    unset($this['slug']);
    unset($this['width']);
    unset($this['height']);
    unset($this['format']);
    
    $this->setWidget('file', new aWidgetFormInputFilePersistent(array(
    )));

    $item = $this->getObject();
    
    // A safe assumption because we always go through the separate upload form on the first pass.
    // This label is a hint that changing the file is not mandatory
    
    // The 'Replace File' label is safer than superimposing a file button
    // on something that may or may not be a preview or generally a good thing 
    // to try to read a button on top of
    
    $this->getWidget('file')->setLabel('Select a new file');
    if (!$item->isNew())
    {
      $this->getWidget('file')->setOption('default-preview', $item->getOriginalPath());
    }
    
    $mimeTypes = aMediaTools::getOption('mime_types');
    // It comes back as a mapping of extensions to types, get the types
    $extensions = array_keys($mimeTypes);
    $mimeTypes = array_values($mimeTypes);
    
    $type = false;
    if (!$this->getObject()->isNew()) {
      // You can't change the major type of an existing media object as
      // this would break slots (a .doc where a .gif should be...)
      $type = $this->getObject()->type;
    }
    // What we are selecting to add to a page
    if (!$type)
    {
      $type = aMediaTools::getType();
    }
    if (!$type)
    {
      // What we are filtering for 
      $type = aMediaTools::getSearchParameter('type');
    }
    if ($type)
    {
      // This supports composite types like _downloadable
      $infos = aMediaTools::getTypeInfos($type);
      $extensions = array();
      foreach ($infos as $info)
      {
        foreach ($info['extensions'] as $extension)
        {
          $extensions[] = $extension;
        }
      }
      $mimeTypes = array();
      $mimeTypesByExtension = aMediaTools::getOption('mime_types');
      foreach ($extensions as $extension)
      {
        $mimeTypes[] = $mimeTypesByExtension[$extension];
      }
    }
    
    // The file is mandatory if it's new. Otherwise
    // we have a problem when they get the file type wrong
    // for one of two and we have to reject that one,
    // then they resubmit - we can add an affirmative way
    // to remove one item from the annotation form later
    
    $this->setValidator("file", new aValidatorFilePersistent(
      array("mime_types" => $mimeTypes,
        'validated_file_class' => 'aValidatedFile',
        "required" => $this->getObject()->isNew() ? true : false),
      array("mime_types" => "The following file types are accepted: " . implode(', ', $extensions))));
    
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
    
    $this->widgetSchema->setLabel('categories_list', 'Categories');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
  }
  
  public function updateObject($values = null)
  {
    if (!isset($values))
    {
      $values = $this->getValues();
    }
    $object = parent::updateObject($values);
    return $object;
  }
}