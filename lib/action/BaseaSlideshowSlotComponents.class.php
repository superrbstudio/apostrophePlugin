<?php

class BaseaSlideshowSlotComponents extends BaseaSlotComponents
{
  public function executeEditView()
  {
    $this->setup();
  }

  public function executeNormalView()
  {
    $this->setup();
    $this->getLinkedItems();
    
    if ($this->getOption('random', false))
    {
      shuffle($this->items);
    }

		$this->options['constraints'] = $this->getOption('constraints', array());
    
  }

  protected function getLinkedItems()
  {
    // Behave well if it's not set yet!
    $data = $this->slot->getArrayValue();
    if (isset($data['order']))
    {
      $this->items = $this->slot->getOrderedMediaItems();
      $this->itemIds = aArray::getIds($this->items);
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
    $this->options['height'] = $this->getOption('height', false);
    $this->options['resizeType'] = $this->getOption('resizeType', 's');
    $this->options['flexHeight'] = $this->getOption('flexHeight');
    $this->options['title'] = $this->getOption('title');
    $this->options['description'] = $this->getOption('description');
    $this->options['credit'] = $this->getOption('credit');
    $this->options['interval'] = $this->getOption('interval', false) + 0;
    $this->options['arrows'] = $this->getOption('arrows', true);
    $this->options['transition'] = ($this->options['height']) ? $this->getOption('transition', 'normal') : 'normal-forced';
    $this->options['position'] = $this->getOption('position', false);
		$this->options['itemTemplate'] = $this->getOption('itemTemplate', 'slideshowItem');
	}
}
