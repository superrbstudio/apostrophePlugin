<?php

class aRenameForm extends sfForm
{
  protected $page;
  public function __construct($page)
  {
    $this->page = $page;
    parent::__construct();
  }
  
  public function configure()
  {
    $this->setWidget('id', new sfWidgetFormInputHidden(array('default' => $this->page->getId())));
    // It's not sfFormWidgetInput anymore in 1.4
    $this->setWidget('title', new sfWidgetFormInputText(array('default' => html_entity_decode($this->page->getTitle())), array('class' => 'epc-value a-breadcrumb-input')));
  }
}

