<?php

class BaseaMediaEngineForm extends aPageForm
{
  public function configure()
  {
    $this->useFields();
    $q = Doctrine::getTable('aCategory')->createQuery()->where('aCategory.media_items = true');
    $this->setWidget('categories_list', new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'aCategory', 'query' => $q)));
    $this->widgetSchema->setLabel('categories_list', 'Categories');
		$this->widgetSchema->setHelp('categories_list','(Defaults to All Cateogories)');
    $this->setValidator('categories_list', new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'aCategory', 'query' => $q, 'required' => false)));
    $this->widgetSchema->setNameFormat('enginesettings[%s]');
    $this->widgetSchema->setFormFormatterName('aAdmin');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
  }
}
