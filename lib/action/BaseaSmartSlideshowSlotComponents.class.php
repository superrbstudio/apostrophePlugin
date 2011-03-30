<?php
/**
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaSmartSlideshowSlotComponents extends aSlotComponents
{

  /**
   * DOCUMENT ME
   */
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

  /**
   * DOCUMENT ME
   */
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

  /**
   * DOCUMENT ME
   * @return mixed
   */
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
    // We have getBrowseQuery, so reuse it!
    $params = array();
    if (isset($value['categories_list']))
    {
      $params['allowed_categories'] = aCategoryTable::getInstance()->createQuery('c')->whereIn('c.id', $value['categories_list'])->execute();
    }
    if (isset($value['tags_list']))
    {
      $params['allowed_tags'] = $value['tags_list'];
    }
    if (isset($this->options['constraints']))
    {
      foreach ($this->options['constraints'] as $k => $v)
      {
        $params[$k] = $v;
      }
    }
    $params['type'] = 'image';
    $q = aMediaItemTable::getBrowseQuery($params);
    $q->andWhere('(aMediaItem.view_is_secure IS NULL OR aMediaItem.view_is_secure IS FALSE)');
    $q->limit($value['count']);
    $q->orderBy('aMediaItem.created_at DESC');
    $this->items = $q->execute();
    // shuffle likes real arrays better
    $a = array();
    foreach ($this->items as $item)
    {
      $a[] = $item;
    }
    $this->items = $a;
    $this->itemIds = aArray::getIds($this->items);
  }
}
