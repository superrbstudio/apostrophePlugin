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
    $this->items = $this->slot->getOrderedMediaItemsWithOptions(array('constraints' => $this->options['constraints']));
    $this->itemIds = aArray::getIds($this->items);
  }
}
