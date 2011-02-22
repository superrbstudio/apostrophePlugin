<?php

class aGlobalButton
{
  protected $label;
  protected $link;
  protected $cssClass;
  protected $targetEnginePage;

  // Use the name when reordering them in app.yml etc. The label will 
  // be automatically i18n'd for you
  public function __construct($name, $label, $link, $cssClass = '', $targetEnginePage = null)
  {
    $this->name = $name;
    $this->label = $label;
    $this->link = $link;
    $this->cssClass = $cssClass;
    $this->targetEnginePage = $targetEnginePage;
  }

  public function getName()
  {
    return $this->name;
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