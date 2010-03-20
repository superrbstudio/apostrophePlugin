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
    // Not sure why extraction is failing for these
    __('Remember', null, 'apostrophe');
    __('The username and/or password is invalid.', null, 'apostrophe');
  }
}
