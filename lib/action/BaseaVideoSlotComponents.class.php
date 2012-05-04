<?php
/**
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaVideoSlotComponents extends aSlotComponents
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
    // Shut off Chrome's poorly designed XSS filtering that clobbers perfectly legitimate iframe embed submissions
    // http://code.google.com/p/chromium/issues/detail?id=98787
    // Otherwise you have to refresh the page again to see your video after selecting and saving it
    $this->getResponse()->setHttpHeader('X-XSS-Protection', '0');

    $this->setup();
    $this->options['constraints'] = $this->getOption('constraints', array());
    $this->options['width'] = $this->getOption('width', 320);
    $this->options['height'] = $this->getOption('height', 240);
    $this->options['resizeType'] = $this->getOption('resizeType', 's');
    $this->options['flexHeight'] = $this->getOption('flexHeight', true);
    $this->options['title'] = $this->getOption('title', false);
    $this->options['description'] = $this->getOption('description', false);
    $this->options['itemTemplate'] = $this->getOption('itemTemplate', 'defaultItem');
    $this->options['autoplay'] = $this->getOption('autoplay', false);

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
          "resizeType" => $this->options['resizeType'],
          // Upsampling video is OK (and commonplace)
          'forceScale' => true));
      if ($this->getOption('thumbnailOnly'))
      {
        if ($this->item->getImageAvailable())
        {
          $this->embed = '<img src="' . aHtml::entities($this->item->getImgSrcUrl($this->dimensions['width'], $this->dimensions['height'], $this->dimensions['resizeType'], $this->dimensions['format'], false)) . '" />';
        }
        else
        {
          $this->embed = '<div class="a-video-slot-missing-thumbnail">No Thumbnail</div>';
        }
      }
      else
      {
        $this->embed = $this->item->getEmbedCode($this->dimensions['width'], $this->dimensions['height'], $this->dimensions['resizeType'], $this->dimensions['format'], false, 'opaque', $this->options['autoplay']);
      }
    }
    $this->stretch16x9 = false;
    if ($this->item)
    {
      $this->stretch16x9 = $this->item->is16x9();
    }
  }
}
