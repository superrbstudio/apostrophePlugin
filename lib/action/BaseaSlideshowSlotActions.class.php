<?php
/**
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaSlideshowSlotActions extends aSlotActions
{

  /**
   * DOCUMENT ME
   * @param sfRequest $request
   * @return mixed
   */
  public function executeEdit(sfRequest $request)
  {
    $this->logMessage("====== in aSlideshowSlotActions::executeEdit", "info");
    if ($request->getParameter('aMediaCancel'))
    {
      return $this->redirectToPage();
    }
    
    $this->editSetup();
    
    if ($request->hasParameter('aMediaIds'))
    {
      $ids = preg_split('/,/', $request->getParameter('aMediaIds'));
      $q = Doctrine::getTable('aMediaItem')->createQuery('m')->select('m.*')->whereIn('m.id', $ids)->andWhere('m.type = "image"');
      // Let the query preserve order for us
      $items = aDoctrine::orderByList($q, $ids)->execute();
      $this->slot->unlink('MediaItems');
      $links = aArray::getIds($items);
      $this->slot->link('MediaItems', $links);

      // This isn't a normal form submission, but the act of selecting items for a
      // slideshow implies we picked the 'selected' radio button, so just save 'form' as if
      // that choice had been saved normally
      
      // Allow subclasses to store other stuff in here - don't just trash what is already in other keys
      // of the value array
      $value = $this->slot->getArrayValue();
      $value['form'] = array('type' => 'selected');
      $value['order'] = $links;
      $this->slot->setArrayValue($value);
      $this->afterSetMediaIds();
      return $this->editSave();
    }
  }
  
  protected function afterSetMediaIds()
  {
    // In case a subclass wants to stop doing something else when media ids are stored
  }
}
