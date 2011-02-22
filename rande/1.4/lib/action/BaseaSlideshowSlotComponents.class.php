<?php

class BaseaSlideshowSlotComponents extends BaseaSlotComponents
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
      $items = $this->slot->MediaItems;
      $data = $this->slot->getArrayValue();
      $order = $data['order'];
      $itemsById = aArray::listToHashById($items);
      $this->items = array();
      foreach ($order as $id)
      {
        if (isset($itemsById[$id]))
        {
          $this->items[] = $itemsById[$id];
        }
      }
      $this->itemIds = aArray::getIds($this->items);
      foreach ($this->items as $item)
      {
        $this->itemIds[] = $item->id;
      }
      if ($this->getOption('random', false))
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
    $this->options['arrows'] = $this->getOption('arrows', true);
    $this->options['transition'] = $this->getOption('transition');
    $this->options['position'] = $this->getOption('position', false);
		$this->options['slideshow_item_template'] = $this->getOption('slideshow_item_template', 'slideshowItem');
	}
}
