<?php

class aGlobalButton
{
  protected $label;
  protected $link;
  protected $cssClass;
  protected $targetEnginePage;

  // Use the name when reordering them in app.yml etc. The label will 
  // be automatically i18n'd for you
  
  // 1.5: the $targetEngine parameter never made sense and was not used by globalTools. Your link
  // implies a route which implies an engine. Only $targetEnginePage is in question
  
  public function __construct($name, $label, $link, $cssClass = '', $targetEnginePage = null)
  {
    $this->name = $name;
    $this->label = $label;
    $this->link = $link;
    $this->cssClass = $cssClass;
    $this->targetEnginePage = $targetEnginePage;
    if ($this->targetEnginePage)
    {
      // 1.5: we've had this sane alternative to pushing and popping engines for a while so use it.
      // It's also possible that you already did this for us and didn't bother passing $targetEnginePage,
      // which is fine
      
      // Oops, I forgot to add $this->
      
      $this->link = aUrl::addParams($this->link, array('engine-slug' => $this->targetEnginePage));
    }
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

  public function getTargetEngine()
  {
    return $this->targetEngine;
  }
  
  public function setLabel($l)
  {
    $this->label = $l;
  }
}