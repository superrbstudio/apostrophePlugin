<?php

class BaseaMediaVideoSearchForm extends BaseForm
{
  public function configure()
  {
    $this->setWidget('q', new sfWidgetFormInput(array(),array('class'=>'a-search-video a-search-form')));
    $this->setValidator('q', new sfValidatorString(array('required' => true)));
    $this->widgetSchema->setNameFormat('videoSearch[%s]');
    $this->widgetSchema->setFormFormatterName('aAdmin');  
    $this->widgetSchema->setLabel('q', ' ');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
  }
}
