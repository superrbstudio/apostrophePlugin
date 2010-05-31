<?php

// See lib/base in this plugin for the actual code. You can extend that
// class in your own application level override of this file

class aMediaComponents extends BaseaMediaComponents
{
  public function executeSelectMultiple()
  {
    if (!aMediaTools::isMultiple())
    {
      throw new Exception("multiple list component, but multiple is off"); 
    }
    $selection = aMediaTools::getSelection();
    if (!is_array($selection))
    {
      throw new Exception("selection is not an array");
    }
    // Work around the fact that whereIn doesn't evaluate to AND FALSE
    // when the array is empty (it just does nothing; which is an
    // interesting variation on MySQL giving you an ERROR when the 
    // list is empty, sigh)
    if (count($selection))
    {
      // Work around the unsorted results of whereIn. You can also
      // do that with a FIELD function
      $unsortedItems = Doctrine_Query::create()->
        from('aMediaItem i')->
        whereIn('i.id', $selection)->
        execute();
      $itemsById = array();
      foreach ($unsortedItems as $item)
      {
        $itemsById[$item->getId()] = $item;
      }
      $this->items = array();
      foreach ($selection as $id)
      {
        if (isset($itemsById[$id]))
        {
          $this->items[] = $itemsById[$id];
        }
      }
    }
    else
    {
      $this->items = array();
    }
  }
}