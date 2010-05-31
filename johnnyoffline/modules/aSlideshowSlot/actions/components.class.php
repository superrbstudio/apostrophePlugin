<?php

class aSlideshowSlotComponents extends BaseaSlotComponents
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
    
    // Behave well if it's not set yet!
    if (strlen($this->slot->value))
    {
      $this->items = unserialize($this->slot->value);
      $this->itemIds = array();
      foreach ($this->items as $item)
      {
        $this->itemIds[] = $item->id;
      }
      if($this->getOption('random', false))
      {
        shuffle($this->items);
      }
    }
    else
    {
      $this->items = array();
      $this->itemIds = array();
    }
  }

	public function executeSlideshow()
	{
    $this->options['width'] = $this->getOption('width', 440);
    $this->options['height'] = $this->getOption('height', 330);
    $this->options['resizeType'] = $this->getOption('resizeType', 's');
    $this->options['flexHeight'] = $this->getOption('flexHeight');
    $this->options['title'] = $this->getOption('title');
    $this->options['description'] = $this->getOption('description');
    $this->options['credit'] = $this->getOption('credit');
    $this->options['interval'] = $this->getOption('interval', false) + 0;
    $this->options['arrows'] = $this->getOption('arrows', ($this->getOption('interval') <= 0));
    $this->options['transition'] = $this->getOption('transition');
	}
}
