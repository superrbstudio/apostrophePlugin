<?php
/**
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaFeedSlotActions extends aSlotActions
{

  /**
   * DOCUMENT ME
   * @param sfRequest $request
   * @return mixed
   */
  public function executeEdit(sfRequest $request)
  {
    $this->editSetup();

    $value = $this->getRequestParameter('slot-form-' . $this->id);
    $this->form = new aFeedForm($this->id, array());
    $this->form->bind($value);
    if ($this->form->isValid())
    {
      // Serializes all of the values returned by the form into the 'value' column of the slot.
      // This is only one of many ways to save data in a slot. You can use custom columns,
      // including foreign key relationships (see schema.yml), or save a single text value 
      // directly in 'value'. serialize() and unserialize() are very useful here and much
      // faster than extra columns
      
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
  