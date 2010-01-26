<?php

/**
 * fvtest actions.
 *
 * @package    trinity
 * @subpackage fvtest
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class fvtestActions extends aBaseActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeEdit(sfRequest $request)
  {
    $this->editSetup();
    $this->form = new FvtestForm();
    $this->form->setId($this->id);
    $this->form->bind($request->getParameter("Fvtest-" . $this->id));
    $this->logMessage("FORM: bound", "info");
    if ($this->form->isValid())
    {
      $this->logMessage("FORM: is valid", "info");
      $this->slot->value = $this->form->getValue('count');
      return $this->editSave();
    }
    else
    {
      // Automatically passes $this->form on to the 
      // next iteration of the edit form so that
      // validation errors can be seen
      $this->logMessage("FORM: cancel", "info");
      return $this->editRetry();
    }
  }
}
