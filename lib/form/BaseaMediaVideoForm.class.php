<?php

// Common base class of aMediaVideoEmbedForm (for embedded media we don't
// have an aEmbedService for) and aMediaVideoYoutubeForm (for embedded media
// we DO have an aEmbedService for)

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
    // This call was missing, preventing easy extension of all media item edit forms at the project level
    parent::configure();
    
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
	
    $this->setWidget('file', new aWidgetFormInputFilePersistent());

    $item = $this->getObject();
    if (!$item->isNew())
    {
      $this->getWidget('file')->setOption('default-preview', $item->getOriginalPath());
    }

    $this->setValidator('file', new aValidatorFilePersistent(array(
      'mime_types' => array('image/jpeg', 'image/png', 'image/gif'), 
      'required' => false
    ), array(
      'mime_types' => 'JPEG, PNG and GIF only.',
      'required' => 'Select a JPEG, PNG or GIF file as a thumbnail')
    ));
    
    $label = 'Replace Thumbnail';
    if (!$this['file']->getWidget()->getOption('default-preview'))
    {
      $label = 'Choose Thumbnail';
    }
    $this->widgetSchema->setLabel("file", $label);
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
