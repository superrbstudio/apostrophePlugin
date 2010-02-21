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
  }
}
