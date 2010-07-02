<?php

class BaseaButtonSlotComponents extends BaseaSlotComponents
{
  public function executeEditView()
  {
    $this->setup();
    // Careful, don't clobber a form object provided to us with validation errors
    // from an earlier pass
    if (!isset($this->form))
    {
      $this->form = new aButtonForm($this->id);
      $value = $this->slot->getArrayValue();
      if (isset($value['url']))
      {
        $this->form->setDefault('url', $value['url']);      
      }
      if (isset($value['title']))
      {
        $this->form->setDefault('title', $value['title']);      
      }
    }
  }
  public function executeNormalView()
  {
    // Mostly identical to aImage, but we have the URL to contend with too
    $this->setup();
    $this->constraints = $this->getOption('constraints', array());
    $this->width = $this->getOption('width', 440);
    $this->height = $this->getOption('height', 330);
    $this->resizeType = $this->getOption('resizeType', 's');
    $this->flexHeight = $this->getOption('flexHeight');
    $this->defaultImage = $this->getOption('defaultImage');
    $this->title = $this->getOption('title');
    $this->description = $this->getOption('description');
    // Behave well if it's not set yet!
    $data = $this->slot->getArrayValue();
    $this->link = false;
    if (isset($data['url']))
    {
      $this->link = $data['url'];
    }
    if (isset($data['title']))
    {
      $this->img_title = $data['title'];
    }
    // Behave well if it's not set yet!
    if (!count($this->slot->MediaItems))
    {
      $this->item = false;
      $this->itemId = false;
    }
    else
    {
      $this->item = $this->slot->MediaItems[0];
      $this->itemId = $this->item->id;
      $this->dimensions = aDimensions::constrain(
        $this->item->width, 
        $this->item->height,
        $this->item->format, 
        array("width" => $this->width,
          "height" => $this->flexHeight ? false : $this->height,
          "resizeType" => $this->resizeType));
      $this->embed = $this->item->getEmbedCode('_WIDTH_', '_HEIGHT_', '_c-OR-s_', '_FORMAT_', false);
    }
  }
}
