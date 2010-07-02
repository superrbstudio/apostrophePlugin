<?php

class BaseaTextSlotComponents extends BaseaSlotComponents
{
  public function executeEditView()
  {
    $this->setup();
    // Careful, sometimes we get an existing form from a previous validation pass
    if (!isset($this->form))
    {
      $this->form = new aTextForm($this->id, $this->slot->value, $this->options);
    }
  }
  public function executeNormalView()
  {
    $this->setup();
    // Yes, we store basic HTML markup for "plaintext" slots.
    // However we obfuscate the mailto links on the fly as a last step
    // (so it's not as fast as we originally intended, but this is an
    // essential feature that makes transformation of the code difficult).
    $this->value = aHtml::obfuscateMailto($this->value);
  }
}
