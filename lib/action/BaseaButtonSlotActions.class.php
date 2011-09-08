<?php
/**
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaButtonSlotActions extends aSlotActions
{

  /**
   * Image association is handled by a separate action
   * @param sfRequest $request
   * @return mixed
   */
  public function executeImage(sfRequest $request)
  {
    if ($request->getParameter('aMediaCancel'))
    {
      return $this->redirectToPage();
    }
    
    $this->logMessage("====== in aButtonSlotActions::executeImage", "info");
    $this->editSetup();
    $item = Doctrine::getTable('aMediaItem')->find($request->getParameter('aMediaId'));
    $this->slot->unlink('MediaItems');
    // It's not a bug to have no media item selected - allow the trashcan to work to remove it
    if ($item && ($item->type === 'image'))
    {
      $this->slot->link('MediaItems', array($item->id));
    }
    $this->editSave();
  }

  /**
   * Use the edit view for the URL (and any other well-behaved fields that may arise)
   * @param sfRequest $request
   * @return mixed
   */
  public function executeEdit(sfRequest $request)
  {
    $this->logMessage("====== in aButtonSlotActions::executeEdit", "info");
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
		
		// Trim whitespace off the front & end of the URL to avoid failing validation on a perfectly acceptable URL
		$value['url'] = trim($value['url']); 

    $this->form = new aButtonForm($this->id, $this->options);
    $this->form->bind($value);
    if ($this->form->isValid())
    {
      $url = $this->form->getValue('url');
      $value = $this->slot->getArrayValue();
      $value['url'] = $url;
      $value['title'] = $this->form->getValue('title');
      $value['description'] = $this->form->getValue('description');
      $this->slot->setArrayValue($value);
      $result = $this->editSave();
      return $result;
    }
    else
    {
      // Makes $this->form available to the next iteration of the
      // edit view so that validation errors can be seen
      return $this->editRetry();
    }
  }
}
