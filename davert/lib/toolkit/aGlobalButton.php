<?php

class aGlobalButton
{
  protected $label;
  protected $link;
  protected $cssClass;
  protected $targetEnginePage;
  
  public function __construct($label, $link, $cssClass = '', $targetEnginePage = null)
  {
    $this->label = $label;
    $this->link = $link;
    $this->cssClass = $cssClass;
    $this->targetEnginePage = $targetEnginePage;
  }
  
  public function getLabel()
  {
    return $this->label;
  }
  
  public function getLink()
  {
    return $this->link;
  }
  
  public function getCssClass()
  {
    return $this->cssClass;
  }
  
  public function getTargetEnginePage()
  {
    return $this->targetEnginePage;
  }
}