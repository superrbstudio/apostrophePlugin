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

}