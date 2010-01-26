<?php

class aGlobalButton
{
  protected $label;
  protected $link;
  protected $cssClass;
  
  public function __construct($label, $link, $cssClass = '')
  {
    $this->label = $label;
    $this->link = $link;
    $this->cssClass = $cssClass;
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
}