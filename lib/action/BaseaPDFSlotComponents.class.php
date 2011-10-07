<?php
/**
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaPDFSlotComponents extends aSlotComponents
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
    $this->constraints = $this->getOption('constraints', array());
    $this->width = $this->getOption('width', 100);
    $this->height = $this->getOption('height', 220);
    $this->resizeType = $this->getOption('resizeType', 's');
    $this->flexHeight = $this->getOption('flexHeight', true);
    $this->defaultImage = $this->getOption('defaultImage');     
    $this->title = $this->getOption('title', true);
    $this->pdfPreview = $this->getOption('pdfPreview', false);
    $this->description = $this->getOption('description', true);
    
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
      if ($this->pdfPreview)
      {
        $this->dimensions = aDimensions::constrain(
          $this->item->width, 
          $this->item->height,
          $this->item->format, 
          array("width" => $this->width,
            "height" => $this->flexHeight ? false : $this->height,
            "resizeType" => $this->resizeType));
      }
      else
      {
        // Placeholder dimensions
        $this->dimensions = array('width' => $this->width, 'height' => $this->width * 3 / 4, 'format' => 'png', 'resizeType' => 's');
      }
      $this->embed = $this->item->getEmbedCode($this->dimensions['width'], $this->dimensions['height'], $this->dimensions['resizeType'], $this->dimensions['format'], false);
    }
  }
}

