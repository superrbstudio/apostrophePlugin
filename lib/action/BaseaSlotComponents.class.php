<?php
/**
 * Base class for Apostrophe CMS slot component classes
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaSlotComponents extends sfComponents
{
  static protected $originalOptionsCache = array();

  /**
   * DOCUMENT ME
   */
  protected function setup()
  {
    if (!isset($this->options))
    {
      // Prevents numerous warnings and problems if there are no slot options present
      $this->options = array();
    }
  
    $this->page = aTools::getCurrentPage();
    $this->slug = $this->page->slug;
  
    // TODO: remove this workaround in 1.5. All uses of actual_slug and real-slug need to go away
    // in favor of actual_url, we just don't want to break any old overrides in client projects.
  
    // Gone in 1.5
  
    // $this->realSlug = aTools::getRealPage() ? aTools::getRealPage()->getSlug() : 'global';
  
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
      if (!$this->updating)
      {
        // Slot options can be influenced by variant switching, and that's fine, and the editor might
        // need to know about it to do the right thing, so it's appropriate to reset the slot
        // options in the attribute. However, we also need to know what the original, pristine
        // options from a_slot or a_area were in order to allow variants to be switched without
        // having side effects on each other's option sets

        // To make matters crazier, this method is called as many as three times per slot: by the outer 'slot' component,
        // by the 'normalView' component, and by the 'editView' component. We should cache that work, but for starters make
        // sure we set originalOptionsCache only once per action so we don't wind up storing variant options in it
        // (this was a big bug on FM)
        $key = "$id-$name-$permid";
        if (!isset(aSlotComponents::$originalOptionsCache[$key]))
        {
          $user->setAttribute("slot-original-options-$id-$name-$permid", 
            $this->options, 'apostrophe');
          aSlotComponents::$originalOptionsCache[$key] = $this->options;
        }
        
        // Refactored to get rid of duplicate logic
        $allowedVariants = array_keys(aTools::getVariantsForSlotType($this->type, $this->options));
        $user->setAttribute("slot-allowed-variants-$id-$name-$permid", $allowedVariants, 'apostrophe');
      }
      $user->setAttribute("slot-options-$id-$name-$permid", 
        $this->options, 'apostrophe');
    }
  
    // Calling getEffectiveVariant ensures we default to the behavior of the first one
    // defined, or the first one allowed if there is an allowed_variants option
    $variant = $this->slot->getEffectiveVariant($this->options);
    if ($variant)
    {
      // Allow slot variants to adjust slot options. This shouldn't be used to radically
      // change the slot, just as an adjunct to CSS, styling things in ways CSS can't
      $variants = aTools::getVariantsForSlotType($this->slot->type, $this->options);
      if (isset($variants[$variant]['options']))
      {
        $options = $variants[$variant]['options'];
        $this->options = array_merge($this->options, $options);
      }
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

  /**
   * executeSlot removed. This was meant as a convenience when overriding the slot component, but
   * its presence caused more problems than it solved, and in any case this feature was entirely
   * broken until now. See http://trac.apostrophenow.org/ticket/1200
   */
   
  /**
   * DOCUMENT ME
   * @param mixed $name
   * @param mixed $default
   * @return mixed
   */
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

  /**
   * DOCUMENT ME
   * @param mixed $name
   * @param mixed $default
   * @return mixed
   */
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

  /**
   * DOCUMENT ME
   */
  public function executeEditView()
  {
    $this->setup();
  }

  /**
   * DOCUMENT ME
   */
  public function executeNormalView()
  {
    $this->setup();
  }
}
