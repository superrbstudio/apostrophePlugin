<?php

class aPDFSlotActions extends BaseaSlotActions
{
  public function executeEdit(sfRequest $request)
  {
    $this->logMessage("====== in aPDFSlotActions::executeEdit", "info");
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
