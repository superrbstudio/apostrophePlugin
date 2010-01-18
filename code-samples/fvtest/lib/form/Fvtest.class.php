<?php

class FvtestForm extends sfForm
{
  public function configure()
  {
    $this->setWidgets(array(
      "count" => new sfWidgetFormInput(array())
    ));
    $this->setValidators(array(
      "count" => new sfValidatorInteger(array('min' => 10, 'max' => 20,
        'required' => true))));
    $this->widgetSchema->setFormFormatterName('table');
  }
  public function setId($id)
  {
    $this->widgetSchema->setNameFormat("Fvtest-$id" . "[%s]");
  }
}
