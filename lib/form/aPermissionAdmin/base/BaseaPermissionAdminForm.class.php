<?php

class BaseaPermissionAdminForm extends sfGuardPermissionForm
{
  public function configure()
  {
    parent::configure();
    
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
  }
  
  private function i18nDummy()
  {
    // This phrase isn't being discovered otherwise
    __('Save and add', null, 'apostrophe');
  }
}
