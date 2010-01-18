<?php

class aSlideshowActions extends aBaseActions
{
  public function executeEdit(sfRequest $request)
  {
    $this->logMessage("====== in aSlideshowActions::executeEdit", "info");
    $this->editSetup();
    $items = aMediaAPI::getSelectedItems($request, false, "image");
    if ($items === false)
    {
      // Cancellation or error
      return $this->redirectToPage();
    } 
    $this->slot->value = serialize($items);
    $this->editSave();
  }
}
