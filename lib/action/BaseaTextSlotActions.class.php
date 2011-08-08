<?php
/**
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaTextSlotActions extends aSlotActions
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
    $this->options['multiline'] = $this->getOption('multiline', true);

    $this->form = new aTextForm($this->id, $this->slot->value, $this->options);
    $this->form->bind($value);
    if ($this->form->isValid())
    {
      // TODO: this might make a nice validator
      $value = $this->form->getValue('value');
      // All "plaintext" slots are actually stored preescaped to be echoed quickly in HTML
      // (although we later decided to add email obfuscation on the fly as a final step).
      // We also convert URLs to links. However only multiline plaintext slots get line breaks 
      // converted to paragraph breaks
      if (!$this->options['multiline'])
      {
        $value = preg_replace("/\s/", " ", $value);
      }
      // We store light markup for "plain text" slots. We DO NOT store the mailto: obfuscation though
      $value = aHtml::textToHtml($value, $this->options['multiline']);
      $maxlength = $this->getOption('maxlength');
      if ($maxlength !== false)
      {
        $value = substr(0, $maxlength);
      }
      $this->slot->value = $value;      
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
