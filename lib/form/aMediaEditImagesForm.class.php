<?php

class aMediaEditImagesForm extends sfForm
{
  private $active = array();
  
  public function __construct($active)
  {
    $this->active = array_flip($active);
    parent::__construct();
  }
  
  public function configure()
  {
    for ($i = 0; ($i < aMediaTools::getOption('batch_max')); $i++)
    {
      if (isset($this->active[$i]))
      {
        $this->embedForm("item-$i", new aMediaImageForm());
      }
    }
    
    $this->widgetSchema->setNameFormat('a_media_items[%s]'); 
    // $this->widgetSchema->setFormFormatterName('aAdmin');
    
  }
}
