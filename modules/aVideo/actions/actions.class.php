<?php

class aVideoActions extends aBaseActions
{
  public function executeEdit(sfRequest $request)
  {
    $this->logMessage("====== in aVideoActions::executeEdit", "info");
    $this->editSetup();
    $item = aMediaAPI::getSelectedItem($request, "video");
    if ($item === false)
    {
      // Cancellation or error
      return $this->redirectToPage();
    } 
    $this->slot->value = serialize($item);
    $this->editSave();
  }
}
