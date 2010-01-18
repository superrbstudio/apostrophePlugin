<?php

class aImageActions extends aBaseActions
{
  public function executeEdit(sfRequest $request)
  {
    $this->logMessage("====== in aImageActions::executeEdit", "info");
    $this->editSetup();
    $item = aMediaAPI::getSelectedItem($request, "image");
    if ($item === false)
    {
      // Cancellation or error
      return $this->redirectToPage();
    } 
    $this->slot->value = serialize($item);
    $this->editSave();
  }
}
