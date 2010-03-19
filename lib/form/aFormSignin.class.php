<?php

class aFormSignin extends sfGuardFormSignin
{
  public function configure()
  {
    parent::configure();
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
  } 
}
