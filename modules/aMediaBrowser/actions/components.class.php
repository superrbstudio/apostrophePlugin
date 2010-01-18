<?php

class aMediaBrowserComponents extends aBaseComponents
{
  protected function valueSetup()
  {
    $value = unserialize($this->slot->value);
    $this->browseParams = array();
    if (is_array($value))
    {
      if (isset($value['tag']))
      {
        $this->logMessage("ZZYYYYYYY we got a tag and it is " . $value['tag'], "info");
        $this->browseParams['tag'] = $value['tag'];
      }
      if (isset($value['search']))
      {
        $this->browseParams['search'] = $value['search'];
      }
      if (isset($value['type']))
      {
        $this->browseParams['type'] = $value['type'];
      }
      if (isset($value['limit']))
      {
        $this->browseParams['limit'] = $value['limit'];
      }
      else
      {
        $this->browseParams['limit'] = sfConfig::get('app_aMediaCMSSlots_mediaBrowser_limit', 10);
      }
    }
    
    $this->constraints = $this->getOption('constraints', array());
    $this->width = $this->getOption('width', 440);
    $this->height = $this->getOption('height', 330);
    $this->resizeType = $this->getOption('resizeType', 's');
    $this->flexHeight = $this->getOption('flexHeight');
    
    $api = new aMediaAPI();
    $result = $api->browseItems($this->browseParams);
    if ($result)
    {
      $this->total = $result->total;
      $this->items = $result->items;
    }
  }
  public function executeEditView()
  {
    $this->setup();
    $this->valueSetup();
    // If there is already a form object reuse it! It contains
    // validation errors from the user's first attempt to submit it.
    if (!isset($this->form))
    {
      $this->form = new aMediaBrowserEditForm();
      $this->form->setParams($this->browseParams);

      // Necessary to prevent HTML id collisions between multiple slots
      // on the same page (see the setId method of the FvtestForm class)
      $this->form->setId($this->id);
    }    
  }
  public function executeNormalView()
  {
    $this->setup();
    $this->valueSetup();    
  }
}
