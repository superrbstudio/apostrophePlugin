<?php

// The name is historical - this class is actually for all
// embedded media from services that have a registered
// aEmbedService implementation

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
  }
}
