<?php
class aNewRichTextSlotActions extends BaseaSlotActions
{
  public function executeEdit(sfRequest $request)
  {
    $this->editSetup();

    $value = $this->getRequestParameter('slotform-' . $this->id);
    // Look ma, I injected a dependency!
    // And yet I have to use the context anyway
    $this->form = new aNewRichTextForm($this->id, array(), array('internal_browser' => $this->getController()->genUrl('aNewRichTextSlot/browse')));
    $this->form->bind($value);
    if ($this->form->isValid())
    {
      // Serializes all of the values returned by the form into the 'value' column of the slot.
      // This is only one of many ways to save data in a slot. You can use custom columns,
      // including foreign key relationships (see schema.yml), or save a single text value 
      // directly in 'value'. serialize() and unserialize() are very useful here and much
      // faster than extra columns
      $value = $this->form->getValue('value');
      $this->slot->setValue($value);
      return $this->editSave();
    }
    else
    {
      // Makes $this->form available to the next iteration of the
      // edit view so that validation errors can be seen, if any
      return $this->editRetry();
    }
  }
  
  public function executeBrowse(sfRequest $request)
  {
    // TODO: am I a potential editor? Check first
    $root = aPageTable::retrieveBySlug('/');
    $tree = $root->getTreeInfo();
    
    // TODO: shouldn't home be in the tree already? But it isn't, so let's add it
    $options = array('' => 'Select a Page', '/' => $root->getTitle());
    $this->flattenTree($tree, $options, '&nbsp;&nbsp;');
    foreach ($options as $value => $label)
    {
      // The title is pre-escaped
      echo('<option value="' . htmlspecialchars($value) . '">' . $label . "</option>\n");
    }
    // This is all we want, don't stick around wasting time and resources and 
    // outputting stuff that breaks things
    exit(0);
  }
  
  protected function flattenTree($tree, &$options, $prefix)
  {
    foreach ($tree as $info)
    {
      $options[aTools::urlForPage($info['slug'])] = $prefix . $info['title'];
      if (isset($info['children']) && count($info['children']))
      {
        $this->flattenTree($info['children'], $options, $prefix . '&nbsp;&nbsp;');
      }
    }
  }
}
  