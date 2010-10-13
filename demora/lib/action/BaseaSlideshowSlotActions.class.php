<?php

class BaseaSlideshowSlotActions extends BaseaSlotActions
{
  public function executeEdit(sfRequest $request)
  {
    $this->logMessage("====== in aSlideshowSlotActions::executeEdit", "info");
    if ($request->getParameter('aMediaCancel'))
    {
      return $this->redirectToPage();
    }
    
    $this->editSetup();
    
    if ($request->hasParameter('aMediaIds'))
    {
      $ids = preg_split('/,/', $request->getParameter('aMediaIds'));
      $q = Doctrine::getTable('aMediaItem')->createQuery('m')->select('m.*')->whereIn('m.id', $ids)->andWhere('m.type = "image"');
      // Let the query preserve order for us
      $items = aDoctrine::orderByList($q, $ids)->execute();
      $this->slot->unlink('MediaItems');
      $links = aArray::getIds($items);
      $this->slot->link('MediaItems', $links);
      // This isn't a normal form submission, but the act of selecting items for a
      // slideshow implies we picked the 'selected' radio button, so just save 'form' as if
      // that choice had been saved normally
      $this->slot->value = serialize(array('form' => array('type' => 'selected'), 'order' => $links));
      return $this->editSave();
    }
    else
    {
      // A normal form submission, used for the tags and categories option
      $value = $this->getRequestParameter('slot-form-' . $this->id);
      $this->form = new aSlideshowForm($this->id);
      $this->form->bind($value);
      if ($this->form->isValid())
      {
        $this->slot->setArrayValue(array('form' => $this->form->getValues()));
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
}
