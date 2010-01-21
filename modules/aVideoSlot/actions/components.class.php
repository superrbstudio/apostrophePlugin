<?php

class aVideoSlotComponents extends BaseaSlotComponents
{
  public function executeEditView()
  {
    // Just a stub, we don't really utilize this for this slot type,
    // we have an external editor instead
    $this->setup();
  }
  public function executeNormalView()
  {
    $this->setup();
    $this->options['constraints'] = $this->getOption('constraints', array());
    $this->options['width'] = $this->getOption('width', 320);
    $this->options['height'] = $this->getOption('height', 240);
    $this->options['resizeType'] = $this->getOption('resizeType', 's');
    $this->options['flexHeight'] = $this->getOption('flexHeight');
    $this->options['title'] = $this->getOption('title');
    $this->options['description'] = $this->getOption('description');
    $this->options['credit'] = $this->getOption('credit');
		
    // Behave well if it's not set yet!
    if (strlen($this->slot->value))
    {
      $this->item = unserialize($this->slot->value);
      $this->itemId = $this->item->id;
    }
    else
    {
      $this->item = false;
      $this->itemId = false;
    }
  }
}
