<?php

class BaseaMediaVideoYoutubeForm extends aMediaVideoForm
{
  public function configure()
  {
    parent::configure();
    unset($this['embed']);
    $this->setValidator('service_url',
      new sfValidatorUrl(
        array('required' => true, 'trim' => true),
        array('required' => "Not a valid YouTube URL")));
    $this->widgetSchema->setFormFormatterName('aAdmin');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
  }
}
