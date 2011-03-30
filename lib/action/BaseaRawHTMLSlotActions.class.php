<?php
/**
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaRawHTMLSlotActions extends aSlotActions
{

  /**
   * DOCUMENT ME
   * @param sfRequest $request
   * @return mixed
   */
  public function executeEdit(sfRequest $request)
  {
    $this->editSetup();
    
    // Very raw, very unfiltered, that's the point. Don't use this
    // slot in designs where you can avoid it. But sometimes clients
    // need to paste foreign HTML for Constant Contact forms
    // and the like. 
    
    // For foreign media embeds, consider apostrophePlugin and
    // apostrophePlugin instead, in particular the optional
    // embed feature which allows carefully filtered embed codes
    // for foreign Flash players etc. It doesn't work everywhere
    // but it's safer than this slot.
    
    // If safemode=1 is in the query string this slot does not render.
    // A good failsafe if the client pastes bad markup/bad styles that
    // break the rendering of the page to the point where you can't
    // easily edit it.
    
    $value = $this->getRequestParameter('slot-form-' . $this->id);
    $this->form = new aRawHTMLForm($this->id);
    $this->form->bind($value);
    if ($this->form->isValid())
    {
      $this->slot->value = $this->form->getValue('value');
      $result = $this->editSave();
      return $result;
    }
    else
    {
      // Makes $this->form available to the next iteration of the
      // edit view so that validation errors can be seen (although there
      // aren't any in this case)
      return $this->editRetry();
    }
  }
}
