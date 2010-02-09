<?php

// Base class for Apostrophe CMS slot component classes

class BaseaSlotComponents extends sfComponents
{
  protected function setup()
  {
    $this->page = aTools::getCurrentPage();
    $this->slug = $this->page->slug;
    $this->realSlug = aTools::getRealPage()->slug;
    $this->slot = $this->page->getSlot(
          $this->name, $this->permid);
    if ((!$this->slot) || ($this->slot->type !== $this->type))
    {
      $this->slot = $this->page->createSlot($this->type);
    }
    if ($this->getOption('edit'))
    {
      $this->editable = true;
    }
    else
    {
      if (aTools::getAllowSlotEditing())
      {
        $this->editable = $this->page->userHasPrivilege('edit');
      }
      else
      {
        $this->editable = false;
      }
    }
    if ($this->getOption('preview'))
    {
      $this->editable = false;
    }
    if ($this->editable)
    {
      $user = $this->getUser();
      $id = $this->page->getId();
      $name = $this->name;
      $permid = $this->permid;
      // Make sure the options passed to a_slot 
      // can be found again at save time
      $user->setAttribute("slot-options-$id-$name-$permid", 
        $this->options, "a");
    }
    $this->pageid = $this->page->id;
    $this->id = $this->pageid . '-' . $this->name . '-' . $this->permid;
    // The basic slot types, and some custom slot types, are
    // simplified by having this field ready to go
    $this->value = $this->slot->value;
    // Not everyone wants the default 'double click the outline to
    // start editing' behavior 
    $this->outlineEditable =
      $this->editable && $this->getOption('outline_editable', 
        $this->slot->isOutlineEditable());
    // Useful if you're reimplementing that via a button etc
    $id = $this->id;
    $this->showEditorJS = 
      "$('#content-$id').hide(); $('#form-$id').fadeIn();";
    if (isset($this->validationData['form']))
    {
      // Make Symfony 1.2 form validation extra-convenient
      $this->form = $this->validationData['form'];
    }
  }
  
  public function executeSlot()
  {
    // Sadly components have no preExecute method
    $this->setup();
  }
  protected function getOption($name, $default = false)
  {
    if (isset($this->options[$name]))
    {
      return $this->options[$name];
    }
    else
    {
      return $default;
    }
  }
  protected function getValidationData($name, $default = false)
  {
    if (isset($this->validationData[$name]))
    {
      return $this->validationData[$name];
    }
    else
    {
      return $default;
    }
  }
  
  public function executeEditView()
  {
    $this->setup();
  }

  public function executeNormalView()
  {
    $this->setup();
  }
}
