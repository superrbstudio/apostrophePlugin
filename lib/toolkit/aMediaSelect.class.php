<?php
/**
 * Conveniences for selecting content with the media repository.
 * This replaces aMediaAPI for new projects in which the media repository
 * is part of the same site.
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aMediaSelect
{

  /**
   * Let's have a nice non-static API to be a bit more futureproof
   */
  public function __construct()
  {
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function getSelectedItem()
  {
    $result = aMediaTools::getSelection();
    if (count($result))
    {
      return $result[0];
    }
    return false;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function getSelectedItems()
  {
    return aMediaTools::getSelection();
  }

  /**
   * Returns a hash by image id of hashes containing cropping info:
   * cropLeft, cropTop, cropWidth, cropHeight. There may not be
   * cropping info for images that were never cropped. In such cases
   * you should refer to the original dimensions of the image
   * @return mixed
   */
  public function getCroppingInfo()
  {
    $croppingInfo = array();
    $imageInfo = aMediaTools::getAttribute('imageInfo', array());
    $selection = aMediaTools::getSelection();
    foreach ($selection as $item)
    {
      if (isset($imageInfo['cropLeft']))
      {
        $info = array('cropLeft' => $imageInfo['cropLeft'],
          'cropTop' => $imageInfo['cropTop'],
          'cropWidth' => $imageInfo['cropWidth'],
          'cropHeight' => $imageInfo['cropHeight']);
        $croppingInfo[$item->id] = $info;
      }
    }
    return $croppingInfo;
  }

  /**
   * Select a media item or items, then redirect to the URL
   * specified by the $after parameter, at which time the above
   * information retrieving methods are valid for use.
   * The $actions parameter should be the current actions class
   * ($this, if you are writing an executeFoo method).
   * $after is the URL to redirect to after the selection is completed or cancelled.
   * For backwards compatibility this URL will receive several GET method parameters,
   * however you should use the methods above rather than consulting them. The methods
   * above are not limited by URL length considerations.
   * $currentIds should contain a list of ids or a list of aMediaItems that are
   * currently selected (allowing the user to modify the list rather than making
   * an entirely new selection), or a single item or id, or false for no current selection.
   * $options is a hash which may contain:
   * multiple => true: allow multiple media items to be selected
   * 'type', 'aspect-width', 'aspect-height', 'minimum-width', 'minimum-height',
   * 'width', 'height': enforce these constraints on type or dimensions
   * type can currently be image, video or pdf
   * 'label': set the reminder message that appears at the top of the media browser
   * to remind the user why they are there and what they are looking for
   * 'cropping' => true: allow the user to crop each selected item. Cropping
   * parameters can be retrieved later with getCroppingInfo()
   * 'croppingInfo' => an array of existing cropping info as returned by
   * getCroppingInfo after a previous successful selection. Allows the user to
   * edit a selection with existing cropping choices
   * @param mixed $actions
   * @param mixed $after
   * @param mixed $currentIds
   * @param mixed $options
   * @return mixed
   */
  public function select($actions, $after, $currentIds = false, $options = array())
  {
    if ($currentIds === false)
    {
      $currentIds = array();
    }
    elseif ($currentIds instanceof aMediaItem)
    {
      $currentIds = array($currentIds);
    }
    elseif (!is_array($currentIds))
    {
      $currentIds = array($currentIds);
    }
    if ($currentIds[0] instanceof aMediaItem)
    {
      $currentIds = aArray::getIds($currentIds);
    }
    aMediaTools::setSelecting($after, $options['multiple'], $ids, $options);
    return $actions->redirect("aMedia/index");
  }
}
