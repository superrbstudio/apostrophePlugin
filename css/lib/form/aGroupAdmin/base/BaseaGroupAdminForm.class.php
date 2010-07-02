<?php

class BaseaGroupAdminForm extends sfGuardGroupForm
{
  public function configure()
  {
    parent::configure();
    
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
  }
}
