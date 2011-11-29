<?php
/**
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaSlideshowSlotComponents extends aSlotComponents
{

  /**
   * DOCUMENT ME
   */
  public function executeEditView()
  {
    $this->setup();
  }

  /**
   * DOCUMENT ME
   */
  public function executeNormalView()
  {
    $this->setup();
    $this->setupOptions();
    $this->getLinkedItems();
    
    if ($this->options['random'] && count($this->items))
    {
      shuffle($this->items);
    }
  }

  /**
   * DOCUMENT ME
   */
  public function executeSlideshow()
  {
    $this->setupOptions();
  }

  protected function getLinkedItems()
  {
    $this->items = $this->slot->getOrderedMediaItems();
    $this->itemIds = aArray::getIds($this->items);
  }

  /**
   * Setup Options for Slideshow Slot
   */
  protected function setupOptions()
  {
    $this->options['width'] = $this->getOption('width', 440);
    $this->options['height'] = $this->getOption('height', false);
    $this->options['resizeType'] = $this->getOption('resizeType', 's');
    $this->options['flexHeight'] = $this->getOption('flexHeight');
    $this->options['maxHeight'] = $this->getOption('maxHeight', false);
    $this->options['title'] = $this->getOption('title', false);
    $this->options['description'] = $this->getOption('description', false);
    $this->options['credit'] = $this->getOption('credit');
    $this->options['interval'] = $this->getOption('interval', 0) + 0;
    $this->options['arrows'] = $this->getOption('arrows', true);
    $this->options['transition'] = ($this->options['height']) ? $this->getOption('transition', 'normal') : 'normal-forced';
    $this->options['duration'] = $this->getOption('duration', 300) + 0;
    $this->options['position'] = $this->getOption('position', false);
    $this->options['itemTemplate'] = $this->getOption('itemTemplate', 'slideshowItem');
    $this->options['slideshowTemplate'] = $this->getOption('slideshowTemplate', 'slideshow');
    $this->options['random'] = $this->getOption('random', false);
    
    // We automatically set up the aspect ratio if the resizeType is set to 'c'
    $constraints = $this->getOption('constraints', array());
    if (($this->getOption('resizeType', 's') === 'c') && isset($constraints['minimum-width']) && isset($constraints['minimum-height']) && (!isset($constraints['aspect-width'])))
    {
      $constraints['aspect-width'] = $constraints['minimum-width'];
      $constraints['aspect-height'] = $constraints['minimum-height'];
    }
    $this->options['constraints'] = $constraints;

    // idSuffix works with the Blog Slot slideshows 
    // Creates unique ids for the same slideshows if they show up in separate slots on a single page.
    $this->options['idSuffix'] = $this->getOption('idSuffix', false); 
  }

}
