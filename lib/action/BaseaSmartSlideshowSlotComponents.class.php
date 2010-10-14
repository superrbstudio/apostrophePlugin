<?php

class BaseaSmartSlideshowSlotComponents extends BaseaSlotComponents
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
      $this->form = new aSmartSlideshowForm($this->id, $value);
    }
  }

  public function executeNormalView()
  {
    $this->setup();
    $this->getTaggedItems();
    
    if ($this->getOption('random', false))
    {
      shuffle($this->items);
    }

		$this->options['constraints'] = $this->getOption('constraints', array());
    
  }

  protected function getTaggedItems()
  {
    $value = $this->slot->getArrayValue();
    $this->items = array();
    $this->itemIds = array();
    // Not set yet
    if (!count($value))
    {
      return;
    }
    if (isset($value['form']))
    {
      // Tolerate what my early alphas did to save our devs some grief, but don't
      // respect it
      return;
    }
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
}
