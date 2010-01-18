<?php

class aButtonActions extends aBaseActions
{
  // Image association is handled by a separate action
  public function executeImage(sfRequest $request)
  {
    $this->logMessage("====== in aImageActions::executeImage", "info");
    $this->editSetup();
    $item = aMediaAPI::getSelectedItem($request, "image");
    if ($item === false)
    {
      // Cancellation or error
      return $this->redirectToPage();
    } 
    $value = $this->slot->getArrayValue();
    $value['image'] = $item;
    $this->slot->setArrayValue($value);
    $this->editSave();
  }
  
  // Use the edit view for the URL (and any other well-behaved fields that may arise) 
  public function executeEdit(sfRequest $request)
  {
    $this->logMessage("====== in aImageActions::executeEdit", "info");
    $this->editSetup();
    $value = $this->getRequestParameter('slotform-' . $this->id);
    $this->form = new aButtonForm($this->id);
    $this->form->bind($value);
    if ($this->form->isValid())
    {
      $url = $this->form->getValue('url');
      $value = $this->slot->getArrayValue();
      $value['url'] = $url;
      $value['title'] = $this->form->getValue('title');
      $this->slot->setArrayValue($value);
      $result = $this->editSave();
      return $result;
    }
    else
    {
      // Makes $this->form available to the next iteration of the
      // edit view so that validation errors can be seen
      return $this->editRetry();
    }    
  }
}
