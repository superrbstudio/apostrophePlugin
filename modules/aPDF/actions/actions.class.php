<?php

class aPDFActions extends aBaseActions
{
  public function executeEdit(sfRequest $request)
  {
    $this->logMessage("====== in aPDFActions::executeEdit", "info");
    $this->editSetup();
    $item = aMediaAPI::getSelectedItem($request, "pdf");
    if ($item === false)
    {
      // Cancellation or error
      return $this->redirectToPage();
    } 
    $this->slot->value = serialize($item);
    $this->editSave();
  }
}
