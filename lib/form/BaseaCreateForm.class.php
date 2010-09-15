<?php

class BaseaCreateForm extends aPageSettingsForm
{
  public function configure()
  {
    parent::configure();
    $this->useFields(array('title', 'template', 'engine'));
  }
}

