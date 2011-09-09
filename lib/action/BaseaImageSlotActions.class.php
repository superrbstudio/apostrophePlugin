<?php
/**
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaImageSlotActions extends aSlotActions
{

  /**
   * DOCUMENT ME
   * @param sfRequest $request
   * @return mixed
   */
  public function executeEdit(sfRequest $request)
  {
    if ($request->getParameter('aMediaCancel'))
    {
      $this->redirectToPage();
    }
    
    $this->logMessage("====== in aImageSlotActions::executeEdit", "info");
    $this->editSetup();

    $item = Doctrine::getTable('aMediaItem')->find($request->getParameter('aMediaId'));
    $this->slot->unlink('MediaItems');
    if ($item && ($item->type === 'image'))
    {
      $this->slot->link('MediaItems', array($item->id));
    }
    else
    {
      // They reverted to not having an image, on purpose (trashcan icon). Let it stay cleared.
    }
    $this->editSave();
  }
}
