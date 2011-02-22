<?php

class BaseaVideoSlotActions extends aSlotActions
{
  public function executeEdit(sfRequest $request)
  {
    if ($request->getParameter('aMediaCancel'))
    {
      $this->redirectToPage();
    }
    
    $this->logMessage("====== in aVideoSlotActions::executeEdit", "info");
    $this->editSetup();

    $item = Doctrine::getTable('aMediaItem')->find($request->getParameter('aMediaId'));
    if ((!$item) || ($item->type !== 'video'))
    {
      return $this->redirectToPage();
    }
    $this->slot->unlink('MediaItems');
    $this->slot->link('MediaItems', array($item->id));
    $this->editSave();
  }
}
