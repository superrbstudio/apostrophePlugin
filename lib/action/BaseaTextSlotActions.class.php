<?php

class BaseaTextSlotActions extends BaseaSlotActions
{
  public function executeEdit(sfRequest $request)
  {
    $this->editSetup();
    
    $value = $this->getRequestParameter('slotform-' . $this->id);
    $this->form = new aTextForm($this->id, $this->slot->value, $this->options);
    $this->form->bind($value);
    if ($this->form->isValid())
    {
      // TODO: this might make a nice validator
      $value = $this->form->getValue('value');
      if (!$this->getOption('multiline'))
      {
        $value = preg_replace("/\s/", " ", $value);
      }
      // We store light markup for "plain text" slots. We don't score
      // the mailto: obfuscation though
      $value = aHtml::textToHtml($value);
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
