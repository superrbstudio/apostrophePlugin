<?php
/**
 * A typical Doctrine route collection CRUD action class framework, with the addition of support for subforms that
 * edit subsets of the object's fields via AJAX. Note that the name of your module determines the name of the
 * variable. TODO: a list of allowed subforms (although the existence of the class is
 * a good first pass at that).
 * TODO: think about whether $singular and $list are worth the trouble. It's nice to
 * refer to things as 'event' and 'events' rather than 'item' and 'items' in templates,
 * but this code would be more readable if we dumped the metavariables
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aSubCrudActions extends sfActions
{
  
  // These must be public to allow poor-man's mix-ins like aRosterTools to work. 
  // You can set them explicitly in your subclass initialize() if the default guesses
  // do not work for you (see initialize() below).
  
  // The module we're in (this is always set correctly by initialize() below)
  public $module;
  // The singular, lowercase name of the type we're editing; for model class Event this is typically 'event'.
  // $this->$singular is often set by methods here and in aRosterTools, allowing $this->event to be referenced
  // in subclass code for convenience. By default, the module name with the first character lowercased
  public $singular;
  // The plural name, by default event_list if singular is event
  public $list;
  // The model class name, by default ucfirst() of $singular
  public $model;

  /**
   * DOCUMENT ME
   * @param mixed $context
   * @param mixed $moduleName
   * @param mixed $actionName
   */
  public function initialize($context, $moduleName, $actionName)
  {
    parent::initialize($context, $moduleName, $actionName);
    $this->module = $moduleName;
    if (!isset($this->singular))
    {
      // 5.2.x doesn't have lcfirst(), that arrives in 5.3.0
      $this->singular = strtolower(substr($this->module, 0, 1)) . substr($this->module, 1);
    }
    $this->list = $this->singular . "_list";
    if (!isset($this->model))
    {
      $this->model = ucfirst($this->singular);
    }
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   */
  public function executeIndex(sfWebRequest $request)
  {
    $list = $this->list;
    $this->$list = $this->getRoute()->getObjects();
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   */
  public function executeShow(sfWebRequest $request)
  {
    $singular = $this->singular;
    $this->$singular = $this->getRoute()->getObject();
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeEdit(sfWebRequest $request)
  {
    $this->getForm($request);
    
    return 'Ajax';
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeUpdate(sfWebRequest $request)
  {
    $this->getForm($request);
    
    if ($this->processForm($request, $this->form))
    {
      return $this->renderPartial($this->module . '/' . $this->form->subtype);
    }

    $this->setTemplate('edit');

    return 'Ajax';
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   */
  public function executeDelete(sfWebRequest $request)
  {
    $request->checkCSRFProtection();

    $this->getRoute()->getObject()->delete();

    $this->redirect($this->module . '/index');
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   */
  public function executeNew(sfWebRequest $request)
  {
    $className = $this->model . 'CreateForm';
    $this->form = new $className();
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeCreate(sfWebRequest $request)
  {
    $className = $this->model . 'CreateForm';
    $this->form = new $className();

    if ($this->processForm($request, $this->form))
    {
      $singular = $this->singular;
      return $this->redirect($this->generateUrl($this->module . '_show', $this->$singular));
    }

    $this->setTemplate('new');
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @param sfForm $form
   * @return mixed
   */
  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()));
    if ($form->isValid())
    {
      $singular = $this->singular;
      $this->$singular = $form->save();
      // Without this, one-to-many relationships don't show the
      // effects of the changes we just made when we render the partial
      // for the static view
      $this->$singular->refreshRelated();
      return true;
    }
    
    return false;
  }

  /**
   * DOCUMENT ME
   * @param mixed $request
   * @return mixed
   */
  protected function getForm($request)
  {
    if ($request->hasParameter('form'))
    {
      $class = aSubCrudTools::getFormClass($this->model, $request->getParameter('form'));
      
      // Custom form getters in the subform classes allow for dependency objection in a way 
      // that permits a chunk to operate on a relation class (like EventUser) or an unrelated class (like sfGuardUserProfile)
      // rather than directly on the object itself (like Event)
      $object = $this->getRoute()->getObject();
      
      if (method_exists($class, 'getForm'))
      { 
        $this->form = call_user_func(array($class, 'getForm'), $object, $request);
      }
      else
      {
        $this->form = new $class($object);
      }

      if (method_exists($this->form, 'userCanEdit') && (!$this->form->userCanEdit()))
      {
        aSignin::signin();
      }
      
      return;
    }
    throw new sfException('No form parameter.');
  }
}
