<?php

class aRenameForm extends BaseForm
{
  protected $page;
  // PARAMETERS ARE REQUIRED, no-parameters version is strictly to satisfy i18n-update
  public function __construct($page = null)
  {
    if (!$page)
    {
      $page = new aPage();
    }
    $this->page = $page;
    parent::__construct();
  }
  
  public function configure()
  {
    $this->setWidget('title', new sfWidgetFormInputText(array('default' => $this->page->getTitle()), array('class' => 'epc-value a-breadcrumb-input')));
    $this->setValidator('title', new sfValidatorString(array('required' => true)));
    $this->widgetSchema->setNameFormat('aRenameForm[%s]');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe'); 
  }
}

