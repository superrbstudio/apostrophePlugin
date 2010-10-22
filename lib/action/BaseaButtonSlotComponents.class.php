<?php

class BaseaButtonSlotComponents extends BaseaSlotComponents
{
	protected function getButtonMedia()
	{
		// We are going to return the media in both Normal and Edit View

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
      $this->embed = $this->item->getEmbedCode('_WIDTH_', '_HEIGHT_', '_c-OR-s_', '_FORMAT_', false);
    }
	}
	
	protected function setupOptions()
	{
    $this->options['constraints'] = $this->getOption('constraints', array());
    $this->options['width'] = $this->getOption('width', 440);
    $this->options['height'] = $this->getOption('height', false);
    $this->options['resizeType'] = $this->getOption('resizeType', 's');
    $this->options['flexHeight'] = $this->getOption('flexHeight', true);
    $this->options['title'] = $this->getOption('title', false);
    $this->options['description'] = $this->getOption('description', true);
		$this->options['link'] = $this->getOption('link', false);
		$this->options['rollover'] = $this->getOption('rollover', true);
		$this->options['defaultImage'] = $this->getOption('defaultImage', false);
	}
	
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
      $this->form = new aButtonForm($this->id);
      $value = $this->slot->getArrayValue();
      if (isset($value['url']))
      {
        $this->form->setDefault('url', $value['url']);      
      }
      if (isset($value['title']))
      {
        $this->form->setDefault('title', $value['title']);      
      }
      if (isset($value['description']))
      {
        $this->form->setDefault('description', $value['description']);      
      }
    }

		$this->getButtonMedia();
  }

  public function executeNormalView()
  {
    // Mostly identical to aImage, but we have the URL to contend with too
    $this->setup();
		$this->setupOptions();

    // Behave well if it's not set yet!
    $data = $this->slot->getArrayValue();

    if (isset($data['url']))
    {
      $this->options['link'] = $data['url'];
    }

    if ($this->options['title'] && isset($data['title']))
    {
      $this->options['title'] = $data['title'];
    }

    if ($this->options['description'] && isset($data['description']))
    {
      $this->options['description'] = $data['description'];
    }

		$this->getButtonMedia();				
  }
}
