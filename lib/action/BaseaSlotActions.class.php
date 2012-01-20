<?php
/**
 * Base class for Apostrophe CMS slot action classes
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaSlotActions extends sfActions
{
  protected $validationData = array();
  protected $newSlot = false;

  /**
   * DOCUMENT ME
   * @return mixed
   */
  protected function editSetup()
  {
    return $this->setup(true);
  }

  /**
   * DOCUMENT ME
   * @param mixed $editing
   * @return mixed
   */
  protected function setup($editing = false)
  {
    $this->reopen = false;
    // Sometimes it's more convenient to call with a pageid rather than a slug
    if ($this->hasRequestParameter('slug'))
    {
      $this->slug = $this->getRequestParameter('slug');
      $this->page = aPageTable::retrieveBySlugWithSlots($this->slug);
    }
    else
    {
      $this->pageid = $this->getRequestParameter('id');
      $this->page = aPageTable::retrieveByIdWithSlots($this->pageid);
    }
    
    $this->name = $this->getRequestParameter('slot');
    $this->value = $this->getRequestParameter('value');
    $this->permid = $this->getRequestParameter('permid');
    
    $this->forward404Unless($this->page);    
    $this->pageid = $this->page->getId();
    aTools::setCurrentPage($this->page);
    
    $this->user = sfContext::getInstance()->getUser();
    
    // Used to name value parameters, among other things
    $this->id = $this->pageid . '-' . $this->name . '-' . $this->permid;

    // This was stored when the slot's editing view was rendered. If it
    // isn't present we must refuse to play for security reasons.
    $user = $this->getUser();
    $pageid = $this->pageid;
    $name = $this->name;
    $permid = $this->permid;
    $lookingFor = "slot-options-$pageid-$name-$permid";
    $this->options = $user->getAttribute(
      $lookingFor, false, 'apostrophe');
    // We ought to check for this, although we also check your privileges
    // on the page
    $this->forward404Unless($this->options !== false);
    if ($editing)
    {
      if (!($this->getOption('edit') || $this->page->userHasPrivilege('edit')))
      {
        return $this->redirect(
          sfConfig::get('secure_module') . '/' .
          sfConfig::get('secure_action'));
      }
    } 
    else
    {
      if (!($this->getOption('edit') || $this->page->userHasPrivilege('view')))
      {
        return $this->redirect(
          sfConfig::get('login_module') . '/' .
          sfConfig::get('login_action'));
      }
    }
      
    $this->forward404Unless($this->options !== false);
    // Clever no?
    $this->type = str_replace("SlotActions", "", get_class($this));
    $slot = $this->page->getSlot($this->name, $this->permid);
    if ($slot && ($slot->type !== $this->type))
    {
      // Ignore a slot of the wrong type (template edits can cause this)
      $slot = false;
    }
    // Copy the slot- we'll be making a new version of it,
    // if we do decide to save that is. 
    if ($slot)
    {
      $this->slot = $slot->copy();
    }
    else
    {
      $this->slot = $this->page->createSlot($this->type);
      $this->newSlot = true;
    }
  }

  /**
   * Save a new version of the area with the updated or created slot in question, refreshing the page
   * so that it will render properly. If the request parameter 'noajax' is present, redirect to the
   * page, otherwise call editAjax to trigger an ajax refresh of the slot. 
   *
   * If $options['quiet'] is specified and true, don't try to redirect or refresh the slot, just do the 
   * backend storage of the new version of the slot. If $options['refresh'] is specified and false,
   * don't refresh the page and its slots
   *
   * @return mixed
   */
  protected function editSave($options = array())
  {
    $quiet = isset($options['quiet']) && $options['quiet'];
    $refresh = (!isset($options['refresh'])) || $options['refresh'];
    
    // A simple hook to let other code know when slot editing has taken place and 
    // a new slot is about to be saved. Since objects are passed by reference it's
    // possible to change the slot object, but this is mainly for logging purposes
    $event = new sfEvent($this->slot, 'a.slotEdited', array('page' => $this->page));
    $this->dispatcher->notify($event);
    
    $this->slot->save();
    $this->page->newAreaVersion(
      $this->name, 
      $this->newSlot ? 'add' : 'update', 
      array('permid' => $this->permid, 'slot' => $this->slot,  'top' => sfConfig::get('app_a_new_slots_top', true)));
    // Refetch the page to reflect these changes before we
    // rerender the slot
    if ($refresh)
    {
      aTools::setCurrentPage(
        aPageTable::retrieveByIdWithSlots($this->page->id));
    }
    if (!$quiet)
    {
      if ($this->getRequestParameter('noajax'))
      {
        return $this->redirectToPage();
      }
      else
      {
        return $this->editAjax(false);
      }
    }
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  protected function redirectToPage()
  {
    // Used for non-AJAX edits of global slots so that we can
    // redirect back to the real page after the edit succeeds
    if ($this->hasRequestParameter('actual_url'))
    {
      return $this->redirect($this->getRequestParameter('actual_url'));
    }
    elseif ($this->hasRequestParameter('actual_slug'))
    {
      return $this->redirect(aTools::urlForPage(
        $this->getRequestParameter('actual_slug')));
    }
    else
    {
      return $this->redirect($this->page->getUrl());
    }
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  protected function editRetry()
  {
    if (isset($this->form))
    {
      $this->validationData['form'] = $this->form;
    }
    $result = $this->editAjax(true);
    return $result;
  }

  /**
   * Re-render the slot in an AJAX response. If $editorOpen is true the
   * edit view will be visible, otherwise the normal view
   * @param bool $editorOpen
   * @return partial
   */
  protected function editAjax($editorOpen)
  {
    // Symfony 1.2+ can return partials rather than templates...
    // which gets us out of the "we need a template from some other
    // module" bind
    
    $variant = $this->slot->getEffectiveVariant($this->options);
    return $this->renderPartial("a/ajaxUpdateSlot",
      array("name" => $this->name, 
        "type" => $this->type,
        "permid" => $this->permid, 
        "options" => $this->options,
        "editorOpen" => $editorOpen,
        "pageid" => $this->page->id,
        // If we don't specify the variant we won't get the default variant on 
        // a newly saved slot with an edit view
        "variant" => $variant,
        // Having the actual slot makes it possible to check if it's new and
        // shut off a-new-slot
        "slot" => $this->slot,
        "validationData" => $this->validationData));
  }

  /**
   * DOCUMENT ME
   * @param sfRequest $request
   * @return mixed
   */
  public function executeEdit(sfRequest $request)
  {
    // When writing your own custom slot classes, you override this
    // to store information in different database fields, look at different
    // request fields, validate the value more critically etc. Call
    // $this->editSetup() to get $this->slot prepopulated for you with
    // a slot of the appropriate type. Always return the result of 
    // $this->editSave() when you are done! 
    $this->editSetup();
    $this->slot->value = $this->getRequestParameter('value-' . $this->id);
    return $this->editSave();
  }

  /**
   * DOCUMENT ME
   * @param sfRequest $request
   * @return mixed
   */
  public function executeAjaxEditView(sfRequest $request)
  {
    // Refresh the view 
    $this->editSetup();
    aTools::setRealUrl($request->getParameter('realUrl'));
    return $this->editAjax(true);
  }

  /**
   * DOCUMENT ME
   * @param mixed $option
   * @param mixed $default
   * @return mixed
   */
  protected function getOption($option, $default = false)
  {
    if (isset($this->options[$option]))
    {
      return $this->options[$option];
    }
    else
    {
      return $default;
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $key
   * @param mixed $val
   */
  protected function setValidationData($key, $val)
  {
    $this->validationData[$key] = $val;
  }
}
