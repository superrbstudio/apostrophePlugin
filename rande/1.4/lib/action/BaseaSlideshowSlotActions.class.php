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
    $ids = preg_split('/,/', $request->getParameter('aMediaIds'));
    $q = Doctrine::getTable('aMediaItem')->createQuery('m')->select('m.*')->whereIn('m.id', $ids)->andWhere('m.type = "image"');
    // Let the query preserve order for us
    $items = aDoctrine::orderByList($q, $ids)->execute();
    $this->slot->unlink('MediaItems');
    $links = aArray::getIds($items);
    $this->slot->link('MediaItems', $links);
    // Save just the order in the value field. Use a hash so we can add
    // other metadata later
    $this->slot->value = serialize(array('order' => $links));
    $this->editSave();
  }
}
