<?php

class aEngineActions extends sfActions
{
  public $page = null;
  
  public function preExecute()
  {
    aEngineTools::preExecute($this);
  }
}