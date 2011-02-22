<?php

class BaseaSmartSlideshowSlotActions extends aSlotActions
{
  public function executeEdit(sfRequest $request)
  {
    $this->logMessage("====== in aSmartSlideshowSlotActions::executeEdit", "info");
    if ($request->getParameter('aMediaCancel'))
    {
      return $this->redirectToPage();
    }
    
    $this->editSetup();
    
    $value = $this->getRequestParameter('slot-form-' . $this->id);
    $this->form = new aSmartSlideshowForm($this->id);
    $this->form->bind($value);
    if ($this->form->isValid())
    {
      $this->slot->setArrayValue($this->form->getValues());
      return $this->editSave();
    }
    else
    {
      // Makes $this->form available to the next iteration of the
      // edit view so that validation errors can be seen, if any
      return $this->editRetry();
    }
  }
}
