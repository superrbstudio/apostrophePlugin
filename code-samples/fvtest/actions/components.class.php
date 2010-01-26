<?php

class fvtestComponents extends aBaseComponents
{
  public function executeEditView()
  {
    $this->setup();
    // If there is already a form object reuse it! It contains
    // validation errors from the user's first attempt to submit it.
    if (!isset($this->form))
    {
      $this->form = new FvtestForm();

      // Be sure to show the current value. 

      // "Can I avoid the need for this call by using a Doctrine form?"
      // Unfortunately, no: Doctrine forms for model classes that
      // use column aggregation inheritance have ALL of the columns
      // of ALL of the classes by default. You'd need to manually
      // specify which ones you care about, all over again. I recommend
      // building forms for custom slot types manually at least until
      // there is improvement in this area of Symfony/Doctrine integration.

      $this->form->setDefault('count', $this->slot->value);

      // Necessary to prevent HTML id collisions between multiple slots
      // on the same page (see the setId method of the FvtestForm class)
      $this->form->setId($this->id);
    }
  }
}
