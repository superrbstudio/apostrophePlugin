<?php

class aFormSignin extends sfGuardFormSignin
{
  public function configure()
  {
    parent::configure();
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
  } 
  
  private function i18nDummy()
  {
    // I have no idea why this never gets extracted otherwise
    __('Remember', null, 'apostrophe');
  }
}
