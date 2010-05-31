<?php

class BaseaButtonSlotActions extends BaseaSlotActions
{
  // Image association is handled by a separate action
  public function executeImage(sfRequest $request)
  {
    if ($request->getParameter('aMediaCancel'))
    {
      return $this->redirectToPage();
    }
    
    $this->logMessage("====== in aImageSlotActions::executeImage", "info");
    $this->editSetup();
    $item = Doctrine::getTable('aMediaItem')->find($request->getParameter('aMediaId'));
    if ((!$item) || ($item->type !== 'image'))
    {
      return $this->redirectToPage();
    }
    $this->slot->unlink('MediaItems');
    $this->slot->link('MediaItems', array($item->id));
    $this->editSave();
  }
  
  // Use the edit view for the URL (and any other well-behaved fields that may arise) 
  public function executeEdit(sfRequest $request)
  {
    $this->logMessage("====== in aButtonSlotActions::executeEdit", "info");
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
