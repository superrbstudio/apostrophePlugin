<?php

class BaseaUserAdminForm extends sfGuardUserAdminForm
{
  public function configure()
  {
    parent::configure();
    
    if(!sfContext::getInstance()->getUser()->isSuperAdmin())
      unset($this['is_super_admin']);

    $this->setWidget('groups_list', new sfWidgetFormDoctrineChoice(array(
      'model' => 'sfGuardGroup',
      'expanded' => true,
      'multiple' => true
    )));
    
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
  }
  
  private function i18nDummy()
  {
    // This phrase isn't being discovered otherwise
    __('Password (Again)', null, 'apostrophe');
  }
}
