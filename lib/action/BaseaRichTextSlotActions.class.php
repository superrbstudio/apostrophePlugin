<?php
/**
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaRichTextSlotActions extends aSlotActions
{

  /**
   * DOCUMENT ME
   * @param sfRequest $request
   * @return mixed
   */
  public function executeEdit(sfRequest $request)
  {
    $this->editSetup();

    // Work around FCK's incompatibility with AJAX and bracketed field names
    // (it insists on making the ID bracketed too which won't work for AJAX)
    
    // Don't forget, there's a CSRF field out there too. We need to grep through
    // the submitted fields and get all of the relevant ones, reinventing what
    // PHP's bracket syntax would do for us if FCK were compatible with it
    
    $values = $request->getParameterHolder()->getAll();
    $value = array();
    foreach ($values as $k => $v)
    {
      if (preg_match('/^slot-form-' . $this->id . '-(.*)$/', $k, $matches))
      {
        $value[$matches[1]] = $v;
      }
    }
    
    // HTML is carefully filtered to allow only elements, attributes and styles that
    // make sense in the context of a rich text slot, and you can adjust that.
    // See aHtml::simplify(). You can do slot-specific overrides by setting the
    // allowed-tags, allowed-attributes and allowed-styles options
    
    $this->form = new aRichTextForm($this->id, $this->options);
    $this->form->bind($value);
    if ($this->form->isValid())
    {
      // The form validator took care of validating well-formed HTML
      // and removing elements, attributes and styles we don't permit 
      $this->slot->value = $this->form->getValue('value');
      return $this->editSave();
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
