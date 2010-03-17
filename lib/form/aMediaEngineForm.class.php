<?php

class aMediaEngineForm extends aPageForm
{
  public function configure()
  {
    $this->useFields();
    $this->setWidget('media_categories_list', new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'aMediaCategory')));
    $this->widgetSchema->setLabel('media_categories_list', 'Media Categories');
		$this->widgetSchema->setHelp('media_categories_list','(Defaults to All Cateogories)');
    $this->setValidator('media_categories_list', new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'aMediaCategory', 'required' => false)));
    $this->widgetSchema->setNameFormat('enginesettings[%s]');
    $this->widgetSchema->setFormFormatterName('aAdmin');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
  }
}
