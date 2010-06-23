<?php

/**
 a actions.
 *
 @package    apostrophe
 @subpackage a
 @author     Your name here
 @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class BaseaActions extends sfActions
{
 /**
  Executes index action
  *
  @param sfWebRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward('default', 'module');
  }
  public function executeShow(sfWebRequest $request)
  {
    $slug = $this->getRequestParameter('slug');
    if (substr($slug, 0, 1) !== '/')
    {
      $slug = "/$slug";
    }
    $page = aPageTable::retrieveBySlugWithSlots($slug);
    if (!$page)
    {
      $redirect = Doctrine::getTable('aRedirect')->findOneBySlug($slug);
      if ($redirect)
      {
        $page = aPageTable::retrieveByIdWithSlots($redirect->page_id);        
        return $this->redirect($page->getUrl(), 301);
      }
    }
    aTools::validatePageAccess($this, $page);
    aTools::setPageEnvironment($this, $page);
    $this->page = $page;
    $this->setTemplate($page->template);

    return 'Template';
  }

	public function executeError404(sfWebRequest $request)
	{

	}
  
  protected function retrievePageForEditingByIdParameter($parameter = 'id', $privilege = 'edit|manage')
  {
    return $this->retrievePageForEditingById($this->getRequestParameter($parameter));
  }
  
  protected function retrievePageForEditingById($id, $privilege = 'edit|manage')
  {
    $page = aPageTable::retrieveByIdWithSlots($id);
    $this->validAndEditable($page, $privilege);
    return $page;
  }

  protected function retrievePageForEditingBySlugParameter($parameter = 'slug', $privilege = 'edit|manage')
  {
    return $this->retrievePageForEditingBySlug($this->getRequestParameter($parameter));
  }

  protected function retrievePageForEditingBySlug($slug, $privilege = 'edit|manage')
  {
    $page = aPageTable::retrieveBySlugWithSlots($slug);
    $this->validAndEditable($page, $privilege);
    return $page;
  }

  protected function validAndEditable($page, $privilege = 'edit|manage')
  {
    $this->flunkUnless($page);
    $this->flunkUnless($page->userHasPrivilege($privilege));
  }

  public function executeSort(sfWebRequest $request)
  {
    return $this->sortBodyWrapper('a-navcolumn');
  }
  
  public function executeSortTree(sfWebRequest $request)
  {
    return $this->sortBodyWrapper('a-navcolumn');
  }
  
  public function executeSortTabs(sfWebRequest $request)
  {
    return $this->sortBodyWrapper('a-tab-nav-item', '/');
  }
  
  public function executeSortNav(sfWebRequest $request)
  {
    return $this->sortNavWrapper('a-tab-nav-item');
  }
  
  protected function sortNavWrapper($parameter)
  {
    $request = $this->getRequest();
    $page = $this->retrievePageForEditingByIdParameter('page');
    $page = $page->getNode()->getParent();
    $this->validAndEditable($page, 'edit');
    $this->flunkUnless($page);
    $order = $this->getRequestParameter($parameter);
    $this->flunkUnless(is_array($order));
    $this->sortBody($page, $order);
    return sfView::NONE;
  }

  protected function sortBodyWrapper($parameter, $slug = false)
  {
    $request = $this->getRequest();
    $this->logMessage("ZZ sortBodyWrapper");
    if ($slug !== false)
    {
      $page = aPageTable::retrieveBySlugWithSlots($slug);
      $this->logMessage("ZZ got slug by slots");
      $this->validAndEditable($page, 'edit');
      $this->logMessage("ZZ is valid and editable");
    } 
    else
    {
      $page = $this->retrievePageForEditingByIdParameter('page');
      $this->logMessage("ZZ got page for editing by id");      
    }
    $this->logMessage("ZZ Page is " . $page->id, "info");
    $this->flunkUnless($page);
    if (!$page->getNode()->hasChildren())
    {
      $page = $page->getNode()->getParent();
      $this->logMessage("ZZ bumping up to parent");
      $this->flunkUnless($page);
    }
    $order = $this->getRequestParameter($parameter);
    ob_start();
    var_dump($_REQUEST);
    $this->logMessage("ZZ request is " . ob_get_clean());
    $this->logMessage("ZZ is_array order: " . is_array($order));
    $this->flunkUnless(is_array($order));
    $this->sortBody($page, $order);
    return sfView::NONE;
  }

  protected function sortBody($parent, $order)
  {
    // Lock the tree against race conditions
    $this->lockTree();
    
    $this->logMessage("ZZ PARENT IS " . $parent->slug);
    // ACHTUNG: I've made attempts to rewrite this more efficiently. They resulted in
    // corrupted nested sets. Corrupted nested sets equal corrupted site page hierarchies
    // equal VERY BAD. I suggest leaving this rarely invoked function the way it is.
    
    foreach ($order as $id)
    {
      $child = Doctrine::getTable('aPage')->find($id);
      if (!$child)
      {
        $this->logMessage("ZZ skipping non-page");
        continue;
      }
      // Compare IDs, not the objects. #375 points out that comparing the objects with !=
      // does a recursive compare which is bad news. Comparing them with !== should work, but
      // what if we have two objects representing the same page? Unlikely in Doctrine, but
      // comparing the page ids is guaranteed to do the right thing.
      if ($child->getNode()->getParent()->id != $parent->id)
      {
        $this->logMessage("ZZ skipping non-child");
        continue;
      }
      $this->logMessage("ZZ MOVING $id");
      $child->getNode()->moveAsLastChildOf($parent);
    }
    // Now: did that work consistently?
    $children = $parent->getNode()->getChildren();
    $this->logMessage("ZZ resulting order is " . implode(",", aArray::getIds($children)));
    $this->unlockTree();
  }

  public function executeRename(sfWebRequest $request)
  {
    $page = $this->retrievePageForEditingByIdParameter();
    $this->flunkUnless($page);
    $this->flunkUnless($page->userHasPrivilege('edit|manage'));    
    $form = new aRenameForm($page);
    $form->bind($request->getParameter('aRenameForm'));
    if ($form->isValid())
    {
      $values = $form->getValues();
      // The slugifier needs to see pre-encoding text
      $page->updateLastSlugComponent($values['title']);
      $title = htmlentities($values['title'], ENT_COMPAT, 'UTF-8');
      $page->setTitle($title);
    }
    // Valid or invalid, redirect. You have to work hard to come up with an invalid title
    return $this->redirect($page->getUrl());
  }

  public function executeShowArchived(sfWebRequest $request)
  {
    $page = $this->retrievePageForEditingByIdParameter();
    $this->state = $request->getParameter('state');
    $this->getUser()->setAttribute('show-archived', $this->state, 'apostrophe');
    if (!$this->state)
    {
      while ($page->getArchived())
      {
        $page = $page->getNode()->getParent();
      }
    }
    return $this->redirect($page->getUrl());
  }

  public function executeCreate()
  {
    $this->flunkUnless($this->getRequest()->getMethod() == sfRequest::POST);
    $parent = $this->retrievePageForEditingBySlugParameter('parent', 'manage');
    $title = trim($this->getRequestParameter('title'));
    $this->flunkUnless(strlen($title));

    $pathComponent = aTools::slugify($title, false);

    $base = $parent->getSlug();
    if ($base === '/')
    {
      $base = '';
    }
    $slug = "$base/$pathComponent";

    $page = new aPage();
    $page->setArchived(!sfConfig::get('app_a_default_on', true));

    $page->setSlug($slug);
    $existingPage = aPageTable::retrieveBySlug($slug);
    if ($existingPage) {
      // TODO: an error in addition to displaying the existing page?
      return $this->redirect($existingPage->getUrl());
    } else { 
      $page->getNode()->insertAsFirstChildOf($parent);

      // Figure out what template this new page should use based on
      // the template rules. 
      //
      // The default rule assigns default to everything.

      $rule = aRules::select(
        sfConfig::get('app_a_template_rules', 
        array(
          array('rule' => '*',
            'template' => 'default'))), $slug);
      if (!$rule)
      {
        $template = 'default';
      }
      else
      {
        $template = $rule['template'];
      }
      $page->template = $template;
      // Must save the page BEFORE we call setTitle, which has the side effect of
      // refreshing the page object
      $page->save();
      $page->setTitle(htmlspecialchars($title));
      return $this->redirect($page->getUrl());
    }
  }

  public function executeHistory()
  {
    // Careful: if we don't build the query our way,
    // we'll get *allslots as soon as we peek at ->slots,
    // including slots that are not current etc.
    $page = $this->retrievePageForAreaEditing();
    $all = $this->getRequestParameter('all');
    $this->versions = $page->getAreaVersions($this->name, false, isset($all)? null : 10);
    $this->id = $page->id;
    $this->version = $page->getAreaCurrentVersion($this->name);
    $this->all = $all;
  }
  
  public function executeAddSlot(sfWebRequest $request)
  {
    $page = $this->retrievePageForAreaEditing();
    aTools::setCurrentPage($page);
    $this->type = $this->getRequestParameter('type');
    $this->options = aTools::getAreaOptions($page->id, $this->name);
    aTools::setRealUrl($request->getParameter('actual_url'));
    
    if (!in_array($this->type, array_keys(aTools::getSlotTypesInfo($this->options))))
    {
      $this->forward404();
    }
  }

  public function executeMoveSlot(sfWebRequest $request)
  {
    $page = $this->retrievePageForAreaEditing();
    aTools::setCurrentPage($page);
    $slots = $page->getArea($this->name);
    $permid = $this->getRequestParameter('permid');
    if (count($slots))
    {
      $permids = array_keys($slots);
      $index = array_search($permid, $permids);
      if ($request->getParameter('up'))
      {
        $limit = 0;
        $difference = -1;
      }
      else
      {
        $limit = count($slots) - 1;
        $difference = 1;
      }
      if (($index !== false) && ($index != $limit))
      {
        $t = $permids[$index + $difference];
        $permids[$index + $difference] = $permid;
        $permids[$index] = $t;
        $page->newAreaVersion($this->name, 'sort', 
          array('permids' => $permids));
        $page = aPageTable::retrieveByIdWithSlots(
          $request->getParameter('id'));
        $this->flunkUnless($page);
        aTools::setCurrentPage($page);
      }
    }
  }

  public function executeDeleteSlot(sfWebRequest $request)
  {
    $page = $this->retrievePageForAreaEditing();
    aTools::setCurrentPage($page);
    $this->name = $this->getRequestParameter('name');
    $page->newAreaVersion($this->name, 'delete', 
      array('permid' => $this->getRequestParameter('permid')));
    $page = aPageTable::retrieveByIdWithSlots(
      $request->getParameter('id'));
    $this->flunkUnless($page);
    aTools::setCurrentPage($page);
  }

  // TODO: refactor. This should probably move into BaseaSlotActions and share more code with executeEdit
  
  public function executeSetVariant(sfWebRequest $request)
  {
    $page = $this->retrievePageForAreaEditing();
    aTools::setCurrentPage($page);
    $this->permid = $this->getRequestParameter('permid');
    $variant = $this->getRequestParameter('variant');
    $page->newAreaVersion($this->name, 'variant', 
      array('permid' => $this->permid, 'variant' => $variant));
    
    // Borrowed from BaseaSlotActions::executeEdit
    // Refetch the page to reflect these changes before we
    // rerender the slot
    aTools::setCurrentPage(
      aPageTable::retrieveByIdWithSlots($page->id));
    $slot = $page->getSlot($this->name, $this->permid);
    
    // This was stored when the slot's editing view was rendered. If it
    // isn't present we must refuse to play for security reasons.
    $user = $this->getUser();
    $pageid = $page->id;
    $name = $this->name;
    $permid = $this->permid;
    $lookingFor = "slot-original-options-$pageid-$name-$permid";
    // Must be consistent about not using namespaces!
    $this->options = $user->getAttribute($lookingFor, false, 'apostrophe');
    $this->forward404Unless($this->options !== false);
    
    return $this->renderPartial('a/ajaxUpdateSlot',
      array('name' => $this->name, 
        'pageid' => $page->id,
        'type' => $slot->type, 
        'permid' => $this->permid, 
        'options' => $this->options,
        'editorOpen' => false,
        'variant' => $variant,
        'validationData' => array()));
  }

  public function executeRevert(sfWebRequest $request)
  {
    $version = false;
    $subaction = $request->getParameter('subaction');
    $this->preview = false;
    if ($subaction == 'preview')
    {
      $version = $request->getParameter('version');
      $this->preview = true;
    }
    elseif ($subaction == 'revert')
    {
      $version = $request->getParameter('version');
    }
    $id = $request->getParameter('id');
    $page = aPageTable::retrieveByIdWithSlotsForVersion($id, $version);
    $this->flunkUnless($page);
    $this->name = $this->getRequestParameter('name');
    $name = $this->name;
    $options = $this->getUser()->getAttribute("area-options-$id-$name", null, 'apostrophe');
    $this->flunkUnless(isset($options['edit']) && $options['edit']);
    if ($subaction == 'revert')
    {
      $page->newAreaVersion($this->name, 'revert');
      $page = aPageTable::retrieveByIdWithSlots($id);
    }
    aTools::setCurrentPage($page);
    $this->cancel = ($subaction == 'cancel');
    $this->revert = ($subaction == 'revert');
  }

  // Rights to edit an area are determined at rendering time and then cached in the session.
  // This allows an edit option to be passed to a_slot and a_area which is crucial for the
  // proper functioning of virtual pages that edit areas related to concepts external to the
  // CMS, such as user biographies
  protected function retrievePageForAreaEditing()
  {
    $id = $this->getRequestParameter('id');
    $page = aPageTable::retrieveByIdWithSlots($id);
    $this->flunkUnless($page);
    $name = $this->getRequestParameter('name');
    $options = $this->getUser()->getAttribute("area-options-$id-$name", null, 'apostrophe');
    $this->flunkUnless(isset($options['edit']) && $options['edit']);
    $this->page = $page;
    $this->name = $name;
    return $page;
  }

  public function executeSettings(sfWebRequest $request)
  {
    if ($request->hasParameter('settings'))
    {
      $settings = $request->getParameter('settings');
      $this->page = $this->retrievePageForEditingById($settings['id']);
    }
    else
    {
      $this->page = $this->retrievePageForEditingByIdParameter();
    }
    
    $this->form = new aPageSettingsForm($this->page);

    $mainFormValid = false;
    
    $engine = $this->page->engine;

    // This might make more sense in some kind of read-only form control.
    // TODO: cache the first call that the form makes so this doesn't
    // cause more db traffic.
    $this->inherited = array();
    $this->admin = array();
    $this->addPrivilegeLists('edit');
    $this->addPrivilegeLists('manage');
    
    if ($request->hasParameter('settings'))
    {
      $settings = $request->getParameter('settings');
      $engine = $settings['engine'];
      $this->form->bind($settings);
      if ($this->form->isValid())
      {
        $mainFormValid = true;
      }
    }

    // Don't look at $this->page->engine which may have just changed. Instead look
    // at what was actually submitted and validated as the new engine name
    if ($engine)
    {
      $engineFormClass = $engine . 'EngineForm';
      if (class_exists($engineFormClass))
      {
        // Used for the initial render. We also ajax re-render this bit when they pick a
        // different engine from the dropdown, see below
        $this->engineForm = new $engineFormClass($this->page);
        $this->engineSettingsPartial = $engine . '/settings';
      }
    }
    
    if ($mainFormValid && (!isset($this->engineForm)))
    {
      $this->form->save();
      $this->page->requestSearchUpdate();          
      return 'Redirect';
    }
    
    
    if ($request->hasParameter('enginesettings') && isset($this->engineForm))
    {
      $this->engineForm->bind($request->getParameter("enginesettings"));
      if ($this->engineForm->isValid())
      {
        if ($mainFormValid)
        {
          // Yes, this does save the same object twice in some cases, but Symfony
          // embedded forms are an unreliable alternative with many issues and
          // no proper documentation as yet
          $this->form->save();
          $this->engineForm->save();
          $this->page->requestSearchUpdate();          
          return 'Redirect';
        }
      }
    }
  }
  
  public function executeEngineSettings(sfWebRequest $request)
  {
    $this->page = $this->retrievePageForEditingByIdParameter();
    
    // Output the form for a different engine in response to an AJAX call. This allows
    // the user to see an immediate change in that form when the engine dropdown is changed
    // to a different setting. Note that this means your engine forms must tolerate situations
    // in which they are not actually the selected engine for the page yet and should not
    // actually do anything until they are actually saved
    
    $engine = $request->getParameter('engine');
    // Don't let them inspect for the existence of weird class names that might make the
    // autoloader do unsafe things
    $this->forward404Unless(preg_match('/^\w*/', $engine));
    if (strlen($engine))
    {
      $engineFormClass = $engine . 'EngineForm';
      if (class_exists($engineFormClass))
      {
        $form = new $engineFormClass($this->page);
        return $this->renderPartial($engine . '/settings', array('form' => $form));
      }
    }    
  }
  
  protected function addPrivilegeLists($privilege)
  {
    list($all, $selected, $inherited, $sufficient) = $this->page->getAccessesById($privilege);
    $this->inherited[$privilege] = array();
    foreach ($inherited as $userId)
    {
      $this->inherited[$privilege][] = $all[$userId];
    }
    $this->admin[$privilege] = array();
    foreach ($sufficient as $userId)
    {
      $this->admin[$privilege][] = $all[$userId];
    }
  }

  public function executeDelete()
  {
    $page = $this->retrievePageForEditingByIdParameter('id', 'manage');
    $parent = $page->getParent();
    if (!$parent)
    {
      // You can't delete the home page, I don't care who you are; creates a chicken and egg problem
      return $this->redirect('@homepage');
    }
    // tom@punkave.com: we must delete via the nested set
    // node or we'll corrupt the tree. Nasty detail, that.
    // Note that this implicitly calls $page->delete()
    // (but the reverse was not true and led to problems).
    $page->getNode()->delete(); 
    return $this->redirect($parent->getUrl());
  }
  
  public function executeSearch(sfWebRequest $request)
  {
    // create the array of pages matching the query
    $q = $request->getParameter('q');
    
    if ($request->hasParameter('x'))
    {
      // We like to use input type="image" for presentation reasons, but it generates
      // ugly x and y parameters with click coordinates. Get rid of those and come back.
      return $this->redirect(sfContext::getInstance()->getController()->genUrl('a/search', true) . '?' .
    http_build_query(array("q" => $q)));
    }
    
    $key = strtolower(trim($q));
    $key = preg_replace('/\s+/', ' ', $key);
    $replacements = sfConfig::get('app_a_search_refinements', array());
    if (isset($replacements[$key]))
    {
      $q = $replacements[$key];
    }

    $values = aZendSearch::searchLuceneWithValues(Doctrine::getTable('aPage'), $q, aTools::getUserCulture());

    $nvalues = array();

    foreach ($values as $value)
    {
      // doesn't implement isset
      if (strlen($value->info))
      {
        $info = unserialize($value->info);
        if (!aPageTable::checkPrivilege('view', $info))
        {
          continue;
        }
      }
      $nvalue = $value;      
      if (substr($nvalue->slug, 0, 1) === '@')
      {
        // Virtual page slug is a named Symfony route, it wants search results to go there
        $nvalue->url = $this->getController()->genUrl($nvalue->slug, true);
      }
      else
      {
        $slash = strpos($nvalue->slug, '/');
        if ($slash === false)
        {
          // A virtual page (such as global) taht isn't the least bit interested in
          // being part of search results
          continue;
        }
        if ($slash > 0)
        {
          // A virtual page slug which is a valid Symfony route, such as foo/bar?id=55
          $nvalue->url = $this->getController()->genUrl($nvalue->slug, true);
        }
        else
        {
          // A normal CMS page
          $nvalue->url = aTools::urlForPage($nvalue->slug);
        }
      }
      $nvalue->class = 'aPage';
      $nvalues[] = $nvalue;
    }
    $values = $nvalues;

    if ($this->searchAddResults($values, $q))
    {
      usort($values, "aActions::compareScores");
    }
    $this->pager = new aArrayPager(null, sfConfig::get('app_a_search_results_per_page', 10));    
    $this->pager->setResultArray($values);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->init();
    $this->pagerUrl = "a/search?" .http_build_query(array("q" => $q));
    // setTitle takes care of escaping things
    $this->getResponse()->setTitle(aTools::getOptionI18n('title_prefix') . 'Search for ' . $q . aTools::getOptionI18n('title_suffix'));
    $this->results = $this->pager->getResults();
  }
  
  protected function searchAddResults(&$values, $q)
  {
    // $values is the set of results so far, passed by reference so you can append more.
    // $q is the Zend query the user typed.
    //
    // Override me! Add more items to the $values array here (note that it was passed by reference).
    // Example: $values[] = array('title' => 'Hi there', 'summary' => 'I like my blog', 
    // 'link' => 'http://thissite/wherever', 'class' => 'blog_post', 'score' => 0.8)
    //
    // 'class' is used to set a CSS class (see searchSuccess.php) to distinguish result types.
    //
    // Best when used with results from a aZendSearch::searchLuceneWithValues call.
    //
    // IF YOU CHANGE THE ARRAY you must return true, otherwise it will not be sorted by score.
    return false;
  }
  
  static public function compareScores($i1, $i2)
  {
    // You can't just use - when comparing non-integers. Oops.
    if ($i2->score < $i1->score)
    {
      return -1;
    } 
    elseif ($i2->score > $i1->score)
    {
      return 1;
    }
    else
    {
      return 0;
    }
  }
  
  public function executeReorganize(sfWebRequest $request)
  {
    
    // Reorganizing the tree = escaping your page-specific security limitations.
    // So only full CMS admins can do it.
    $this->flunkUnless($this->getUser()->hasCredential('cms_admin'));
    
    $root = aPageTable::retrieveBySlug('/');
    $this->forward404Unless($root);
    
    $this->treeData = $root->getTreeJSONReady(false);
    // setTitle takes care of escaping things
    $this->getResponse()->setTitle(aTools::getOptionI18n('title_prefix') . 'Reorganize' . aTools::getOptionI18n('title_suffix'));
  }

  public function executeTreeMove($request)
  {
    $this->lockTree();
    try
    {
      $page = $this->retrievePageForEditingByIdParameter('id', 'manage');
      $refPage = $this->retrievePageForEditingByIdParameter('refId', 'manage');
      $type = $request->getParameter('type');
      if ($refPage->slug === '/')
      {
        // Root must not have peers
        if ($type !== 'inside')
        {
          throw new sfException('root must not have peers');
        }
      }
      $this->logMessage("TREEMOVE page slug: " . $page->slug . " ref page slug: " . $refPage->slug . " type: " . $type, "info");
    
      // Refuse to move a page relative to one of its own descendants.
      // Doctrine's NestedSet implementation produces an
      // inconsistent tree in the 'inside' case and we're not too sure about
      // the peer cases either. The javascript tree component we are using does not allow it
      // anyway, but it can be fooled if you have two reorg tabs open
      // or another user is using it at the same time etc. -Tom and Dan
      // http://www.doctrine-project.org/jira/browse/DC-384
      $ancestorsInfo = $refPage->getAncestorsInfo();
      foreach ($ancestorsInfo as $info)
      {
        if ($info['id'] === $page->id)
        {
          $this->logMessage("TREEMOVE balked because page is an ancestor of ref page", "info");
          throw new sfException('page is ancestor of ref page');
        }
      }
      if ($type === 'after')
      {
        $page->getNode()->moveAsNextSiblingOf($refPage);
        $page->forceSlugFromParent();
      }
      elseif ($type === 'before')
      {
        $page->getNode()->moveAsPrevSiblingOf($refPage);
        $page->forceSlugFromParent();
      }
      elseif ($type === 'inside')
      {
        $page->getNode()->moveAsLastChildOf($refPage);
        $page->forceSlugFromParent();
      }
      else
      {
        throw new sfException('Type parameter is bogus');
      }
    } catch (Exception $e)
    {
      $this->unlockTree();
      $this->forward404();
    }
    $this->unlockTree();
    echo("ok");
    return sfView::NONE;
  }
  
  protected function getParentClasses($parents)
  {
    $result = '';
    foreach ($parents as $p)
    {
      $result .= " descendantof-$p";
    }
    if (count($parents))
    {
      $lastParent = aArray::last($parents);
      $result .= " childof-$lastParent";
    }
    if (count($parents) < 2)
    {
      $result .= " toplevel";
    }
    return $result;
  }
  
  protected function generateAfterPageInfo($lastPage, $parents, $minusLevels)
  {
    $pageInfo = array();
    $pageInfo['id'] = 'after-' . $lastPage->getId();
    $pageInfo['title'] = 'after';
    $pageInfo['level'] = $lastPage->getLevel() - $minusLevels;
    $pageInfo['class'] = 'pagetree-after ' . $this->getParentClasses($parents);
    return $pageInfo;
  }
  
  protected function flunkUnless($condition)
  {
    if ($condition)
    {
      return;
    }
    $this->logMessage("ZZ flunked", "info");
    $this->forward('a', 'cleanSignin');
  }
  
  // Do NOT use these as the default signin actions. They are special-purpose
  // ajax/iframe breakers for use in forcing the user back to the login page
  // when they try to do an ajax action after timing out.
  
  public function executeCleanSignin(sfWebRequest $request)
  {
    // Template is a frame/ajax breaker, redirects to phase 2
  }
  
  public function executeCleanSigninPhase2(sfWebRequest $request)
  {
    $this->getRequest()->isXmlHttpRequest();
    $cookies = array_keys($_COOKIE);
    foreach ($cookies as $cookie)
    {
      // Leave the sfGuardPlugin remember me cookie alone
      if ($cookie === sfConfig::get('app_sf_guard_plugin_remember_cookie_name', 'sfRemember'))
      {
        continue;
      }
      // ACHTUNG: only works if we specify the domain ('/' in most cases).
      // This lives in factory.yml... where we can't access it. So unfortunately
      // a redundant setting is needed
      setcookie($cookie, "", time() - 3600, sfConfig::get('app_aToolkit_cleanLogin_cookie_domain', '/'));
    }
    // Push the user back to the home page rather than the login prompt. Otherwise
    // we can find ourselves in an infinite loop if the login prompt helpfully
    // sends them back to an action they are not allowed to carry out
    $url = sfContext::getInstance()->getController()->genUrl('@homepage');
    header("Location: $url");
    exit(0);
  }
  
  public function executePersonalSettings(sfWebRequest $request)
  {
    $this->forward404Unless(sfConfig::get('app_a_personal_settings_enabled', false));
    $this->logMessage("ZZ hello", "info");
    $this->forward404Unless($this->getUser()->isAuthenticated());
    $this->logMessage("ZZ after auth", "info");
    $profile = $this->getUser()->getProfile();
    $this->logMessage("ZZ after fetch profile", "info");
    $this->forward404Unless($profile);
    $this->logMessage("ZZ after profile", "info");
    $this->form = new aPersonalSettingsForm($profile);
    if ($request->getParameter('submit'))
    {
      $this->form->bind($request->getParameter('settings'));
      if ($this->form->isValid())
      {
        $this->form->save();
        return 'Redirect';
      }
    }
  }
  
  public function executeLanguage(sfWebRequest $request)
  {
    $this->form = new aLanguageForm(null, array('languages' => sfConfig::get('app_a_i18n_languages')));
    if ($this->form->process($request))
    {
      // culture has changed
      return $this->redirect('@homepage');
    }

    // the form is not valid (can't happen... but you never know)
    return $this->redirect('@homepage');
  }
  
  // There are potential race conditions in the Doctrine nested set code, and also 
  // in our own code that decides when it's safe to call it. So we need an
  // application-level lock for reorg functions. Dan says there are transactions in
  // Doctrine that should make adding and deleting pages safe, so we don't lock
  // those actions for now, but this code is available for that purpose too if need be
  
  protected $lockfp;
  
  protected function lockTree()
  {
    $dir = aFiles::getWritableDataFolder(array('a', 'locks'));
    $file = "$dir/tree.lck";
    while (true)
    {
      $this->lockfp = fopen($file, 'a');
      if (!$this->lockfp)
      {
        sleep(1);
      }
      else
      {
        break;
      }
    } 
    flock($this->lockfp, LOCK_EX);
  }
  
  protected function unlockTree()
  {
    fclose($this->lockfp);
  }
}

