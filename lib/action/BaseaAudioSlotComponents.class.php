<?php
/**
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaAudioSlotComponents extends aSlotComponents
{

  /**
   * DOCUMENT ME
   */
  public function executeEditView()
  {
    // Must be at the start of both view components
    $this->setup();
    // Doesn't really use the edit view, just a browse button in the normal view
  }

  /**
   * DOCUMENT ME
   */
  public function executeNormalView()
  {
    $this->setup();

    $this->options['width'] = $this->getOption('width', 340);
    $this->options['height'] = $this->getOption('height', false);
    $this->options['title'] = $this->getOption('title', true);
    $this->options['description'] = $this->getOption('description', true);
    $this->options['download'] = $this->getOption('download', true);
    $this->options['playerTemplate'] = $this->getOption('playerTemplate','default');

   // Behave well if it's not set yet!
    if (!count($this->slot->MediaItems))
    {
      $this->item = false;
      $this->itemId = false;
    }
    else
    {
      $this->item = $this->slot->MediaItems[0];
      $this->itemId = $this->item->getId();
    }
  }
}
