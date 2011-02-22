<?php

class aTagForm extends TagForm
{
  public function setup()
  {
    parent::setup();

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'Tag', 'column' => array('name')))
    );

    $this->useFields(array('name'));
  }
  
  public function updateObject($values = null)
  {
    if (is_null($values))
    {
      $values = $this->getValues();
    }
    // Slashes break routes in most server configs. Do NOT force case of tags.
    
    $values['name'] = str_replace('/', '-', isset($values['name']) ? $values['name'] : '');
    parent::updateObject($values);
  }
}