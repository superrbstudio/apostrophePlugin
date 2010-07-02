<?php

class BaseaRawHTMLForm extends BaseForm
{
  protected $id;
  public function __construct($id)
  {
    $this->id = $id;
    parent::__construct();
  }
  public function configure()
  {
    $this->setWidgets(array('value' => new sfWidgetFormTextarea(array(), array('class' => 'aRawHTMLSlotTextarea'))));
    // Raw HTML slot, so anything goes, including an empty response 
    $this->setValidators(array('value' => new sfValidatorString(array('required' => false))));
    $this->widgetSchema->setNameFormat('slotform-' . $this->id . '[%s]');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
  }
}