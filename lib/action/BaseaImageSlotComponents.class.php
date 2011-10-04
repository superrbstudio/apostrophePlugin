<?php
/**
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaImageSlotComponents extends aSlotComponents
{

  /**
   * DOCUMENT ME
   */
  public function executeEditView()
  {
    // Just a stub, we don't really utilize this for this slot type,
    // we have an external editor instead
    $this->setup();
  }

  /**
   * DOCUMENT ME
   */
  public function executeNormalView()
  {
    $this->setup();
		$this->setupOptions();
		if (!isset($this->options['showTemplate']))
		{
		  // Overriding this allows you to change the rendering of the slot for
		  // a particular a_slot call
		  $this->options['showTemplate'] = 'show';
		}
    // $this->constraints = $this->getOption('constraints', array());
    // $this->width = $this->getOption('width', 440);
    // $this->height = $this->getOption('height', 330);
    // $this->resizeType = $this->getOption('resizeType', 's');
    // $this->link = $this->getOption('link', false);
    // $this->flexHeight = $this->getOption('flexHeight');
    // $this->defaultImage = $this->getOption('defaultImage');
    // $this->title = $this->getOption('title');
    // $this->description = $this->getOption('description');
    // Behave well if it's not set yet!
    if (!count($this->slot->MediaItems))
    {
      $this->item = false;
      $this->itemId = false;
    }
    else
    {
      $this->item = $this->slot->MediaItems[0];
      $this->itemId = $this->item->id;
      $this->dimensions = aDimensions::constrain(
        $this->item->width, 
        $this->item->height,
        $this->item->format, 
        array("width" => $this->options['width'],
          "height" => $this->options['flexHeight'] ? false : $this->options['height'],
          "resizeType" => $this->options['resizeType']));
      $this->embed = $this->item->getEmbedCode($this->dimensions['width'], $this->dimensions['height'], $this->dimensions['resizeType'], $this->dimensions['format'], false);
    }
  }

  /**
   * DOCUMENT ME
   */
  protected function setupOptions()
  {
    $this->options['width'] = $this->getOption('width', 440);
    $this->options['height'] = $this->getOption('height', false);
    $this->options['resizeType'] = $this->getOption('resizeType', 's');
    $this->options['flexHeight'] = $this->getOption('flexHeight');
    $this->options['maxHeight'] = $this->getOption('maxHeight', false);
    $this->options['title'] = $this->getOption('title');
    $this->options['description'] = $this->getOption('description');
    $this->options['credit'] = $this->getOption('credit');
    $this->options['link'] = $this->getOption('link');
    $this->options['defaultImage'] = $this->getOption('defaultImage');
    
    // We automatically set up the aspect ratio if the resizeType is set to 'c'
    $constraints = $this->getOption('constraints', array());
    if (($this->getOption('resizeType', 's') === 'c') && isset($constraints['minimum-width']) && isset($constraints['minimum-height']) && (!isset($constraints['aspect-width'])))
    {
      $constraints['aspect-width'] = $constraints['minimum-width'];
      $constraints['aspect-height'] = $constraints['minimum-height'];
    }
    $this->options['constraints'] = $constraints;
  }

}
