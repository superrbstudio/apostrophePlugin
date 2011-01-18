<?php

class BaseaUserAdminForm extends sfGuardUserAdminForm
{
  public function configure()
  {
    parent::configure();
    
    unset($this['is_super_admin']);

    $this->setWidget('groups_list', new sfWidgetFormDoctrineChoice(array(
      'model' => 'sfGuardGroup',
      'expanded' => true,
      'multiple' => true
    )));
    
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    // It's convenient to kill these when using Shibboleth
    if (!sfConfig::get('app_a_userAdmin_password', true))
    {
      unset($this['password']);
      unset($this['password_again']);
    }
    if (!sfConfig::get('app_a_userAdmin_is_active', true))
    {
      unset($this['is_active']);
    }
    // Handing out permissions directly is usually a mistake, use groups and
    // restrict full permissions admin to the superadmin
    if (!sfConfig::get('app_a_userAdmin_permissions', false))
    {
      unset($this['permissions_list']);
    }
    foreach ($this->getUseFields() as $field)
    {
      $this->getWidget($field)->setAttribute('autocomplete', 'off');
    }
  }
  
  private function i18nDummy()
  {
    // This phrase isn't being discovered otherwise
    __('Password (again)', null, 'apostrophe');
  }
}
