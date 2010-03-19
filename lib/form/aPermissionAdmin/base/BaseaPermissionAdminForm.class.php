<?php

class BaseaPermissionAdminForm extends sfGuardPermissionForm
{
  public function configure()
  {
    parent::configure();
    
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
  }
}
