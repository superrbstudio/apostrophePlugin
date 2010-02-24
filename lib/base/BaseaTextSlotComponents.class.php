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
    // We don't recommend doing this at the FCK level,
    // let it happen here instead so what is stored in the
    // db can be clean markup
    $this->value = aHtml::obfuscateMailto($this->value);
  }
}
