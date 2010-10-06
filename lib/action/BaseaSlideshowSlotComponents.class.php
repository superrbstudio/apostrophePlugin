<?php

class BaseaSlideshowSlotComponents extends BaseaSlotComponents
{
  public function executeEditView()
  {
    $this->setup();
    
    $this->options['constraints'] = $this->getOption('constraints', array());
		
    // Careful, don't clobber a form object provided to us with validation errors
    // from an earlier pass
    if (!isset($this->form))
    {
      $value = $this->slot->getArrayValue();
      if (!isset($value['form']))
      {
        $value['form'] = array();
      }
      $this->form = new aSlideshowForm($this->id, $value['form']);
    }
    
    $this->getLinkedItems();
  }

  public function executeNormalView()
  {
    $this->setup();

    $value = $this->slot->getArrayValue();
    if (isset($value['form']['type']))
    {
      switch ($value['form']['type'])
      {
        case 'selected':
        $this->getLinkedItems();
        break;
        case 'tagged':
        $this->getTaggedItems();
        break;
      }
    }
    
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
      $items = $this->slot->MediaItems;
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
    }
    else
    {
      $this->items = array();
      $this->itemIds = array();
    }
  }
  
  protected function getTaggedItems()
  {
    $value = $this->slot->getArrayValue();
    $value = $value['form'];
    // Tag join will break if we give it a name other than the model name in the query
    $q = Doctrine::getTable('aMediaItem')->createQuery();
    if (isset($value['categories_list']) && count($value['categories_list']) > 0)
    {
      $q->innerJoin('aMediaItem.Categories c');
      $q->andWhereIn('c.id', $value['categories_list']);
    }
    if(isset($value['tags_list']) && strlen($value['tags_list']) > 0)
    {
      PluginTagTable::getObjectTaggedWithQuery('aMediaItem', $value['tags_list'], $q, array('nb_common_tags' => 1));
    }
    $q->andWhere('(aMediaItem.view_is_secure IS NULL OR aMediaItem.view_is_secure IS FALSE) AND aMediaItem.type = ?', array('image'));
    $q->limit($value['count']);
    $q->orderBy('aMediaItem.created_at DESC');
    $this->items = $q->execute();
    $this->itemIds = aArray::getIds($this->items);
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
		$this->options['itemTemplate'] = $this->getOption('itemTemplate', 'slideshowItem');
	}
}
