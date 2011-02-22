<?php
class aNewRichTextSlotComponents extends BaseaSlotComponents
{
  public function executeEditView()
  {
    // Must be at the start of both view components
    $this->setup();
    
    // Careful, don't clobber a form object provided to us with validation errors
    // from an earlier pass
    if (!isset($this->form))
    {
      $this->form = new aNewRichTextForm($this->id, array('value' => $this->slot->getValue()),
        array('internal_browser' => $this->getController()->genUrl('aNewRichTextSlot/browse')));
    }
  }
  public function executeNormalView()
  {
    $this->setup();
    $this->value = $this->slot->getValue();
  }
}
