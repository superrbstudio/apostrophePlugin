<?php

class aMediaBrowserSlotActions extends BaseaSlotActions
{
  public function executeEdit(sfRequest $request)
  {
    $this->logMessage("====== in aMediaBrowserSlotActions::executeEdit", "info");
    $this->editSetup();
    $this->form = new aMediaBrowserEditForm();
    $this->form->setId($this->id);
    $this->form->bind($request->getParameter("a-mediabrowser-edit-form-" . $this->id));
    if ($this->form->isValid())
    {
      $value = array();
      $tag = $this->form->getValue('tag');
      $search = $this->form->getValue('search');
      $type = $this->form->getValue('type');
      $this->logMessage("ZZ tag: $tag search: $search type: $type\n", "info");
      if (!empty($tag))
      {
        $value['tag'] = $tag;
      }
      if (!empty($search))
      {
        $value['search'] = $search;
      }
      if (!empty($type))
      {
        $value['type'] = $type;
      }
      $this->slot->value = serialize($value);
      return $this->editSave();
    }
    else
    {
      // Automatically passes $this->form on to the 
      // next iteration of the edit form so that
      // validation errors can be seen
      return $this->editRetry();
    }    
  }
}
