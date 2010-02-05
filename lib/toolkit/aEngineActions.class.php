<?php

class aEngineActions extends sfActions
{
  protected $page = null;
  
  public function preExecute()
  {
    aEngineTools::preExecute($this);
  }
}