<?php

class aSlideshowSlotActions extends BaseaSlotActions
{
  public function executeEdit(sfRequest $request)
  {
    $this->logMessage("====== in aSlideshowSlotActions::executeEdit", "info");
    $this->editSetup();
    $ids = preg_split('/,/', $request->getParameter('aMediaIds'));
    $this->slot->unlink('MediaItems');
    $items = Doctrine::getTable('aMediaItem')->createQuery('m')->whereIn('m.id', $ids)->andWhere('m.type = "image"')->execute();
    // Be careful to preserve order, the query doesn't
    $itemsById = aArray::listToHashById($items);
    $links = array();
    foreach ($ids as $id)
    {
      if (isset($itemsById[$id]))
      {
        $links[] = $id;
      }
    }
    $this->slot->link('MediaItems', $links);
    // Save just the order in the value field. Use a hash so we can add
    // other metadata later
    $this->slot->value = serialize(array('order' => $links));
    $this->editSave();
  }
}
