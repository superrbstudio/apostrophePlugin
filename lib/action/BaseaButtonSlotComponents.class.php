<?php
/**
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaButtonSlotComponents extends aSlotComponents
{

  /**
   * DOCUMENT ME
   */
  protected function getButtonMedia()
  {
    // We are going to return the media in both Normal and Edit View

    // Backwards compatibility with pkContextCMS  button slots that the data migration task missed
    if (!count($this->slot->MediaItems))
    {
      $value = $this->slot->getArrayValue();
      if (isset($value['image']))
      {
        $mediaItem = Doctrine::getTable('aMediaItem')->find($value['image']->id);
        if ($mediaItem)
        {
          $this->slot->MediaItems[] = $mediaItem;
        }
      }
    }
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
      if (($this->options['maxHeight'] !== false) && ($this->dimensions['height'] > $this->options['maxHeight']))
      {
        $this->dimensions = aDimensions::constrain(
          $this->item->width, 
          $this->item->height,
          $this->item->format, 
          array("width" => false,
            "height" => $this->options['maxHeight'],
            "resizeType" => $this->options['resizeType']));

      }
      $this->embed = $this->item->getEmbedCode($this->dimensions['width'], $this->dimensions['height'], $this->dimensions['resizeType'], $this->dimensions['format'], false, 'opaque', false, array('alt' => strlen($this->options['title']) ? $this->options['title'] : ''));
    }
  }

  /**
   * DOCUMENT ME
   */
  protected function setupOptions()
  {
    $this->options['constraints'] = $this->getOption('constraints', array());
    $this->options['width'] = $this->getOption('width', 440);
    $this->options['height'] = $this->getOption('height', false);
    $this->options['resizeType'] = $this->getOption('resizeType', 's');
    $this->options['flexHeight'] = $this->getOption('flexHeight', true);
    $this->options['maxHeight'] = $this->getOption('maxHeight', false);
    $this->options['title'] = $this->getOption('title', false);
    $this->options['description'] = $this->getOption('description', true);
    $this->options['link'] = $this->getOption('link', false);
    $this->options['url'] = $this->getOption('link', false);
    $this->options['rollover'] = $this->getOption('rollover', true);
    $this->options['defaultImage'] = $this->getOption('defaultImage', false);
    $this->options['itemTemplate'] = $this->getOption('itemTemplate', 'default');    
    $this->options['image'] = $this->getOption('image', true);
  }

  /**
   * DOCUMENT ME
   */
  public function executeEditView()
  {
    $this->setup();
    $this->setupOptions();
    $this->options['width'] = 160;
    $this->options['height'] = 160;

    // Careful, don't clobber a form object provided to us with validation errors
    // from an earlier pass
    if (!isset($this->form))
    {
      $this->form = new aButtonForm($this->id, $this->options);
      $value = $this->slot->getArrayValue();
      if (isset($value['url']))
      {
        $this->form->setDefault('url', $value['url']);      
      }
      else
      {
        $this->form->setDefault('url', $this->getOption('link'));
      }
      if (isset($value['title']))
      {
        $this->form->setDefault('title', $value['title']);      
      }
      else
      {
        // Careful, just plain true is a valid setting for this option
        $title = $this->getOption('title');
        if (strlen($title) && ($title !== true))
        {
          $this->form->setDefault('title', $title);
        }
      }
      if (isset($value['description']))
      {
        $this->form->setDefault('description', $value['description']);      
      }
    }

    $this->getButtonMedia();
  }

  /**
   * DOCUMENT ME
   */
  public function executeNormalView()
  {
    // Mostly identical to aImage, but we have the URL to contend with too
    $this->setup();
    $this->setupOptions();

    // Behave well if it's not set yet!
    $data = $this->slot->getArrayValue();

		// If there is a URL stored in the slot
		// Use that URL (instead of the supplied link slot)
		if (isset($data['url']))
    {
      $this->options['url'] = $data['url'];
    }

		// If the title is TRUE or a String, check if there's a title set in the slot $data
		// IF NOT, THEN check if there's a string set in the slot options
    if ($this->options['title'])
     {
      if (isset($data['title']))
      {
        $this->options['title'] = $data['title'];
      }
      else
      {
       	$this->options['title'] = ($this->options['title'] === true) ? false : $this->options['title'];
      }
    }

    if ($this->options['description'])
    {
      if (isset($data['description'])) {
        $this->options['description'] = $data['description'];
      }
      else
      {
        $this->options['description'] = false;        
      }
    }

    $this->getButtonMedia();        
  }
}
