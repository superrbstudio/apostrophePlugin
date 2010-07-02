<?php

class BaseaCreateForm extends BaseForm
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
    $this->setWidget('parent', new sfWidgetFormInputHidden(array('default' => $this->page->getSlug())));
    // It's not sfFormWidgetInput anymore in 1.4
    $this->setWidget('title', new sfWidgetFormInputText(array(), array('class' => 'a-breadcrumb-create-childpage-title a-breadcrumb-input')));
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
  }
}

