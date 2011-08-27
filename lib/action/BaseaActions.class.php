<?php
/**
 * 
 * a actions.
 * @package    apostrophe
 * @subpackage a
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class BaseaActions extends sfActions
{

  /**
   * 
   * Executes index action
   * @param sfWebRequest $request A request object
   */
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward('default', 'module');
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeShow(sfWebRequest $request)
  {
    $slug = $this->getRequestParameter('slug');
    
    // remove trailing slashes from $slug
    $pattern = '/\/$/';
    if (preg_match($pattern, $slug) && ($slug != '/'))
    {
      sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
      
      $new_slug = preg_replace($pattern, '', $slug);
      $slug = addcslashes($slug, '/');
      $new_uri = preg_replace( '/' . $slug . '/' , $new_slug, $request->getUri());
    
      $this->redirect($new_uri);
    }
    
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

    $tagstring = implode(',', $page->getTags());
    if (strlen($tagstring))
    {
      $this->getResponse()->addMeta('keywords', htmlspecialchars($tagstring));
    }
    if (strlen($page->getMetaDescription()))
    {
      $this->getResponse()->addMeta('description', $page->getMetaDescription());
    }

    return 'Template';
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   */
  public function executeError404(sfWebRequest $request)
  {
    // Apostrophe Bundled 404 
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   */
  public function executeSecure(sfWebRequest $request)
  {
    // Apostrophe Bundled Secure
  }

  /**
   * DOCUMENT ME
   * @param mixed $parameter
   * @param mixed $privilege
   * @return mixed
   */
  protected function retrievePageForEditingByIdParameter($parameter = 'id', $privilege = 'edit')
  {
    return $this->retrievePageForEditingById($this->getRequestParameter($parameter), $privilege);
  }

  /**
   * DOCUMENT ME
   * @param mixed $id
   * @param mixed $privilege
   * @return mixed
   */
  protected function retrievePageForEditingById($id, $privilege = 'edit')
  {
    $page = aPageTable::retrieveByIdWithSlots($id);
    $this->validAndEditable($page, $privilege);
    return $page;
  }

  /**
   * DOCUMENT ME
   * @param mixed $parameter
   * @param mixed $privilege
   * @return mixed
   */
  protected function retrievePageForEditingBySlugParameter($parameter = 'slug', $privilege = 'edit')
  {
    return $this->retrievePageForEditingBySlug($this->getRequestParameter($parameter), $privilege);
  }

  /**
   * DOCUMENT ME
   * @param mixed $slug
   * @param mixed $privilege
   * @return mixed
   */
  protected function retrievePageForEditingBySlug($slug, $privilege = 'edit')
  {
    $page = aPageTable::retrieveBySlugWithSlots($slug);
    $this->validAndEditable($page, $privilege);
    return $page;
  }

  /**
   * DOCUMENT ME
   * @param mixed $page
   * @param mixed $privilege
   */
  protected function validAndEditable($page, $privilege = 'edit')
  {
    $this->flunkUnless($page);
    $this->flunkUnless($page->userHasPrivilege($privilege));
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeSort(sfWebRequest $request)
  {
    return $this->sortBodyWrapper('a-navcolumn');
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeSortTree(sfWebRequest $request)
  {
    return $this->sortBodyWrapper('a-navcolumn');
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeSortTabs(sfWebRequest $request)
  {
    return $this->sortBodyWrapper('a-tab-nav-item', '/');
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeSortNav(sfWebRequest $request)
  {
    return $this->sortNavWrapper('a-tab-nav-item');
  }

  /**
   * DOCUMENT ME
   * @param mixed $parameter
   * @return mixed
   */
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

  /**
   * DOCUMENT ME
   * @param mixed $parameter
   * @param mixed $slug
   * @return mixed
   */
  protected function sortBodyWrapper($parameter, $slug = false)
  {
    $request = $this->getRequest();
    if ($slug !== false)
    {
      $page = aPageTable::retrieveBySlugWithSlots($slug);
      $this->validAndEditable($page, 'edit');
    } 
    else
    {
      $page = $this->retrievePageForEditingByIdParameter('page');
    }
    $this->flunkUnless($page);
    if (!$page->getNode()->hasChildren())
    {
      $page = $page->getNode()->getParent();
      $this->flunkUnless($page);
    }
    $order = $this->getRequestParameter($parameter);
    $this->flunkUnless(is_array($order));
    $this->sortBody($page, $order);
    return sfView::NONE;
  }

  /**
   * DOCUMENT ME
   * @param mixed $parent
   * @param mixed $order
   */
  protected function sortBody($parent, $order)
  {
    // Lock the tree against race conditions
    $this->lockTree();
    
    // ACHTUNG: I've made attempts to rewrite this more efficiently. They resulted in
    // corrupted nested sets. Corrupted nested sets equal corrupted site page hierarchies
    // equal VERY BAD. I suggest leaving this rarely invoked function the way it is.
    
    foreach ($order as $id)
    {
      $child = Doctrine::getTable('aPage')->find($id);
      if (!$child)
      {
        continue;
      }
      // Compare IDs, not the objects. #375 points out that comparing the objects with !=
      // does a recursive compare which is bad news. Comparing them with !== should work, but
      // what if we have two objects representing the same page? Unlikely in Doctrine, but
      // comparing the page ids is guaranteed to do the right thing.
      if ($child->getNode()->getParent()->id != $parent->id)
      {
        continue;
      }
      $child->getNode()->moveAsLastChildOf($parent);
    }
    // Now: did that work consistently?
    $children = $parent->getNode()->getChildren();
    $this->unlockTree();
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeRename(sfWebRequest $request)
  {
    $page = $this->retrievePageForEditingByIdParameter();
    $this->flunkUnless($page);
    $this->flunkUnless($page->userHasPrivilege('edit'));    
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

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
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

  /**
   * DOCUMENT ME
   */
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

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   */
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

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   */
  public function executeMoveSlot(sfWebRequest $request)
  {
    $page = $this->retrievePageForAreaEditing();
    aTools::setCurrentPage($page);
    $slots = $page->getArea($this->name);
    $permid = $this->getRequestParameter('permid');
    $this->options = aTools::getAreaOptions($page->id, $this->name);
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

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   */
  public function executeDeleteSlot(sfWebRequest $request)
  {
    $page = $this->retrievePageForAreaEditing();
    aTools::setCurrentPage($page);
    $this->name = $this->getRequestParameter('name');
    $this->options = aTools::getAreaOptions($page->id, $this->name);
    $page->newAreaVersion($this->name, 'delete', 
      array('permid' => $this->getRequestParameter('permid')));
    $page = aPageTable::retrieveByIdWithSlots(
      $request->getParameter('id'));
    $this->flunkUnless($page);
    aTools::setCurrentPage($page);
  }

  /**
   * TODO: refactor. This should probably move into aSlotActions and share more code with executeEdit
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeSetVariant(sfWebRequest $request)
  {
    $page = $this->retrievePageForAreaEditing();
    aTools::setCurrentPage($page);
    $this->permid = $this->getRequestParameter('permid');
    $variant = $this->getRequestParameter('variant');
    $page->newAreaVersion($this->name, 'variant', 
      array('permid' => $this->permid, 'variant' => $variant));
    
    // Borrowed from aSlotActions::executeEdit
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
        'validationData' => array(),
        'slot' => $slot));
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   */
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

  /**
   * Rights to edit an area are determined at rendering time and then cached in the session.
   * This allows an edit option to be passed to a_slot and a_area which is crucial for the
   * proper functioning of virtual pages that edit areas related to concepts external to the
   * CMS, such as user biographies
   * @return mixed
   */
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

  /**
   * A REST API to aTools::slugify(), used when suggesting page slugs for new pages.
   * "Can't you just reimplement it in JavaScript?" No.
   * some of the major browsers (*cough* IE) can't manipulate Unicode in regular expressions.
   * Also two implementations mean our code will drift apart and introduce bugs
   * Returns a suitable slug for a new page component (i.e. based on a title).
   * The browser appends this to the slug of the parent page to create its suggestion
   * @param sfWebRequest $request
   */
  public function executeSlugify(sfWebRequest $request)
  {
    $slug = $request->getParameter('slug');
    $this->slug = aTools::slugify($slug, false);
    $this->setLayout(false);
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeSettings(sfWebRequest $request)
  {
    $this->lockTree();
    $new = $request->getParameter('new');
    $this->parent = null;
    if ($new)
    {
      $this->page = new aPage();
      
      $this->parent = $this->retrievePageForEditingBySlugParameter('parent', 'manage');
      $event = new sfEvent($this->parent, 'a.filterNewPage', array());
      $this->dispatcher->filter($event, $this->page);
      $this->page = $event->getReturnValue();
    }
    else
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
    }
    
    // get the form and page tags
    $this->stem = $this->page->isNew() ? 'a-create-page' : 'a-page-settings';
    $this->form = new aPageSettingsForm($this->page, $this->parent);
    
    $event = new sfEvent($this->page, 'a.filterPageSettingsForm', array('parent' => $this->parent));
    $this->dispatcher->filter($event, $this->form);
    $this->form = $event->getReturnValue();
    $mainFormValid = false;
    
    $engine = $this->page->engine;

    if ($request->hasParameter('settings'))
    {
      $settings = $request->getParameter('settings');
      if (isset($settings['joinedtemplate']))
      {
        list($engine, $template) = preg_split('/:/', $settings['joinedtemplate']);
        if ($engine === 'a')
        {
          $engine = '';
        }
      }
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

      // $pathComponent = aTools::slugify($this->form->getValue('title'), false);
      // 
      // $base = $parent->getSlug();
      // if ($base === '/')
      // {
      //   $base = '';
      // }
      // $slug = "$base/$pathComponent";

      // $page = new aPage();
      // // Allow both the old pkContextCMS name and a more intuitive name for this option
      // 
      // $page->setSlug($slug);
      // $existingPage = aPageTable::retrieveBySlug($slug);
      
      $this->unlockTree();  
      
      return 'Redirect';
    }
    
    
    if ($request->hasParameter('enginesettings') && isset($this->engineForm))
    {
      // If it's a new page we need the page id so we can save the engine's setting
      $request->setParameter("enginesettings[pageid]", $this->page->id);
      $this->engineForm->bind($request->getParameter("enginesettings"));
      if ($this->engineForm->isValid())
      {
        if ($mainFormValid)
        {
          // Yes, this does save the same object twice in some cases, but Symfony
          // embedded forms are an unreliable alternative with many issues and
          // no proper documentation as yet
          $this->form->save();
          
          if ($new)
          {
            // If the page was new, we won't be able to save the
            // engine form if it's a conventional subclass of aPageForm;
            // they don't like being saved consecutively for the 
            // same new object. Make a new form and bind it to exactly
            // the same data
            $this->engineForm = new $engineFormClass($this->page);
            $this->engineForm->bind($request->getParameter("enginesettings"));
            $this->forward404Unless($this->engineForm->isValid());
          }
          
          $this->engineForm->save();
          $this->page->requestSearchUpdate();          
          $this->unlockTree();  
          return 'Redirect';
        }
      }
    }
    // The slug stem is what we try to append the title to when creating a new slug
    if ($new)
    {
      // TODO: make this UTF8-aware but not no-UTF8-support-hostile, etc.
      $this->slugStem = preg_replace('/\/$/', '', $this->parent->slug);
    }
    else
    {
      if (preg_match('/^(.*?)\/[^\/]*$/', $this->page->slug, $matches))
      {
        $this->slugStem = $matches[1];
      }
      else
      {
        $this->slugStem = $this->page->slug;
      }
    }
    $this->unlockTree();  
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   */
  public function executeEngineSettings(sfWebRequest $request)
  {
    $id = $request->getParameter('id');
    if (!$id)
    {
      // In 1.5 you can design engine settings for a page that isn't there yet
      $this->flunkUnless(aTools::isPotentialEditor());
      $this->page = new aPage();
    }
    else
    {
      $this->page = $this->retrievePageForEditingByIdParameter();
    }
    
    // Output the form for a different engine in response to an AJAX call. This allows
    // the user to see an immediate change in that form when the engine dropdown is changed
    // to a different setting. Note that this means your engine forms must tolerate situations
    // in which they are not actually the selected engine for the page yet and should not
    // actually do anything until they are actually saved. Also they must cooperate if the
    // page is a new page and not make abt assumptions about where the new page will be
    // or what it will be called
    
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
        $this->form = $form;
        $this->partial = $engine . '/settings';
      }
    }    
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function executeDelete()
  {
    $this->lockTree();
    $page = $this->retrievePageForEditingByIdParameter('id', 'delete');
    $parent = $page->getParent();
    if (!$parent)
    {
      $this->unlockTree();
      // You can't delete the home page, I don't care who you are; creates a chicken and egg problem
      return $this->redirect('@homepage');
    }
    // tom@punkave.com: we must delete via the nested set
    // node or we'll corrupt the tree. Nasty detail, that.
    // Note that this implicitly calls $page->delete()
    // (but the reverse was not true and led to problems).
    $page->getNode()->delete(); 
    $this->unlockTree();
    
    return $this->redirect($parent->getUrl());
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeSearch(sfWebRequest $request)
  {
    $now = date('YmdHis');
    
    // create the array of pages matching the query
    $q = $request->getParameter('q');
    
    if ($request->hasParameter('x'))
    {
      // We sometimes like to use input type="image" for presentation reasons, but it generates
      // ugly x and y parameters with click coordinates. Get rid of those and come back.
      return $this->redirect(sfContext::getInstance()->getController()->genUrl('a/search', true) . '?' . http_build_query(array("q" => $q)));
    }
    
    $key = strtolower(trim($q));
    $key = preg_replace('/\s+/', ' ', $key);
    $replacements = sfConfig::get('app_a_search_refinements', array());
    if (isset($replacements[$key]))
    {
      $q = $replacements[$key];
    }

    if (aTools::$searchService)
    {
      // Search services are incompatible with addSearchResults. Achieving compatibility
      // with addSearchResults would require that we discard the benefits of a simple
      // query and start hydrating everything out to the 1000th result in order to merge, 
      // and merging results from unrelated types usually does not work that well anyway.
      // There is a simple alternative: see the a/searchAfter and a/searchBefore partials
      // for a great place to override and add sidebars to your page search that search
      // for other types of information and provide teaser links to get more results. 
      //
      // Another solution: mirror (or store) your content in an Apostrophe virtual page whose
      // slug is a valid Symfony URL: @mymodule_search_redirect?id=52 
      // 
      // If the slug is a valid Symfony URL it will be included in search results and
      // linked to that URL with link_to().
      //
      // This is how blog posts and events get merged into search results - their content
      // lives in pages to begin with.
      
      $table = Doctrine::getTable('aPage');
      $query = $table->createQuery('p')->select('p.*');
      // Restrict page visibility as appropriate
      $table->addViewPermissionsToQuery($query);
      // Now add search
      $query = aTools::$searchService->addSearchToQuery($query, $q, array('item_model' => 'aPage', 'culture' => aTools::getUserCulture()));
      // We're interested in regular pages (start with /) and virtual pages
      // whose slugs are valid Symfony URLs (contain / or start with @)
      $query->addWhere('p.slug LIKE "%/%" OR p.slug LIKE "@%"');
      
      // Now add pagination (notice we're still building one query)
      $this->pager = new sfDoctrinePager('aPage', sfConfig::get('app_a_search_results_per_page', 10));    
      $this->pager->setQuery($query);
      $this->pager->setPage($request->getParameter('page', 1));
      $this->pager->init();
    }
    else
    {
      try
      {
        $values = aZendSearch::searchLuceneWithValues(Doctrine::getTable('aPage'), $q, aTools::getUserCulture());
      } catch (Exception $e)
      {
        // Lucene search error. TODO: display it nicely if they are always safe things to display. For now: just don't crash
        $values = array();
      }

      // The truth is that Zend cannot do all of our filtering for us, especially
      // permissions-based. So we can do some other filtering as well, although it
      // would be bad not to have Zend take care of the really big cuts (if 99% are
      // not being prefiltered by Zend, and we have a Zend max results of 1000, then 
      // we are reduced to working with a maximum of 10 real results).
    
      $nvalues = array();

      $index = Doctrine::getTable('aPage')->getLuceneIndex();
    
      foreach ($values as $value)
      {
        $document = $index->getDocument($value->id);
  //      $published_at = $value->published_at;
        // New way: don't touch anything but $hit->id directly and you won't force a persistent
        // use of memory for the lazy loaded columns http://zendframework.com/issues/browse/ZF-8267
        $published_at = $document->getFieldValue('published_at');
        if ($published_at > $now)
        {
          continue;
        }

        // 1.5: the names under which we store columns in Zend Lucene have changed to
        // avoid conflict with also indexing them
        $info = unserialize($document->getFieldValue('info_stored'));
      
        if (!aPageTable::checkPrivilege('view', $info))
        {
          continue;
        }
      
        $slug = $document->getFieldValue('slug_stored');
        if ((substr($slug, 0, 1) !== '@') && (strpos($slug, '/') === false))
        {
          // A virtual page slug which is not a route is not interested in being part of search results
          continue;
        }
        $nvalues[] = $value;
      }

      $values = $nvalues;

      if ($this->searchAddResults($values, $q))
      {
        foreach ($values as $value)
        {
          if (get_class($value) === 'stdClass')
          {
            // bc with existing implementations of searchAddResults
            if (!isset($value->slug_stored))
            {
              if (isset($value->slug))
              {
                $value->slug_stored = $value->slug;
              }
              else
              {
                $value->slug_stored = null;
              }
            }
            if (!isset($value->title_stored))
            {
              $value->title_stored = $value->title;
            }
            if (!isset($value->summary_stored))
            {
              $value->summary_stored = $value->summary;
            }
            if (!isset($value->engine_stored))
            {
              if (isset($value->engine))
              {
                $value->engine_stored = $value->engine;
              }
              else
              {
                $value->engine_stored = null;
              }
            }
          }
        }
        // $value = new stdClass();
        // $value->url = $url;
        // $value->title = $title;
        // $value->score = $scores[$id];
        // $value->summary = $summary;
        // $value->class = 'Article';
        // $values[] = $value;
      
        usort($values, "aActions::compareScores");
      }
      $this->pager = new aArrayPager(null, sfConfig::get('app_a_search_results_per_page', 10));    
      $this->pager->setResultArray($values);
      $this->pager->setPage($request->getParameter('page', 1));
      $this->pager->init();
    }
  
    $this->pagerUrl = "a/search?" . http_build_query(array("q" => $q));
    // setTitle takes care of escaping things
    $this->getResponse()->setTitle(aTools::getOptionI18n('title_prefix') . 'Search for ' . $q . aTools::getOptionI18n('title_suffix'));

    // Now that we have paginated and obtained the short list of results we really
    // care about it's OK to use the lazy load features of Lucene for the last mile
    $results = $this->pager->getResults();
    $nresults = array();
    foreach ($results as $value)
    {
      if (aTools::$searchService)
      {
        // bc with the object-style syntax used in search template overrides,
        // which are numerous. If we didn't care about bc with the old zend
        // stuff there would be less copying of stuff here
        $info = aTools::$searchService->getInfoForResult($value);
        $nvalue = new stdclass();
        $nvalue->engine_stored = $info['engine_stored'];
        $nvalue->slug_stored = $info['slug_stored'];
        $nvalue->title_stored = $info['title_stored'];
        $nvalue->summary_stored = $info['summary_stored'];
      }
      else
      {
        $nvalue = $value;
      }
      $nvalue->slug = $nvalue->slug_stored;
      $nvalue->title = $nvalue->title_stored;
      $nvalue->summary = $nvalue->summary_stored;

      // Search results engine helper should only be triggered for virtual pages
      // with an engine associated with them. Regular engine pages are, well,
      // pages, and don't need special treatment. For some reason this is cropping
      // up with search services but I can't figure out why it didn't with Lucene
      if (strlen($nvalue->engine_stored) && (!preg_match('/^\//', $nvalue->slug)))
      {
        $helperClass = $nvalue->engine_stored . 'SearchHelper';
        if (class_exists($helperClass))
        {
          $searchHelper = new $helperClass;
          $nvalue->partial = $searchHelper->getPartial();
        }
      }
      
      if (!isset($nvalue->url))
      {
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
            // A virtual page (such as global) that isn't the least bit interested in
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
      }
      $nvalue->class = 'aPage';
      $nresults[] = $nvalue;
    }
    $this->results = $nresults;
  }

  /**
   * DOCUMENT ME
   * @param mixed $values
   * @param mixed $q
   * @return mixed
   */
  protected function searchAddResults(&$values, $q)
  {
    // $values is the set of results so far, passed by reference so you can append more.
    // $q is the Zend query the user typed.
    //
    // Override me! Add more items to the $values array here (note that it was passed by reference).
    
    // $value = new stdClass();
    // $value->url = $url;
    // $value->title = $article->getTitle();
    // $value->score = $articleScores[$article->getId()];
    // $value->summary = $article->getSearchSummary();
    // $value->class = 'HandbookArticle';
    // $values[] = $value;
    // $changed = true;
    
    // Example: 
    //
    // $value = new stdClass();
    // $value->url = $url;
    // $value->title = $title;
    // $value->score = $scores[$id];
    // $value->summary = $summary;
    // $value->class = 'Article';
    // $values[] = $value;
    //
    // 'class' is used to set a CSS class (see searchSuccess.php) to distinguish result types.
    //
    // Best when used with results from a aZendSearch::searchLuceneWithScores call. That call gives
    // you access to the scores so you can pass them along to Apostrophe.
    //
    // IF YOU CHANGE THE $values ARRAY you must return true, otherwise it will not be sorted by score.
    return false;
  }

  /**
   * DOCUMENT ME
   * @param mixed $i1
   * @param mixed $i2
   * @return mixed
   */
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

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   */
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

  /**
   * DOCUMENT ME
   * @param mixed $request
   * @return mixed
   */
  public function executeTreeMove($request)
  {
    $this->lockTree();
    $moved = false;
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
          throw new sfException('page is ancestor of ref page');
        }
      }
      if ($type === 'after')
      {
        $page->getNode()->moveAsNextSiblingOf($refPage);
        $page->forceSlugFromParent();
        $moved = true;
      }
      elseif ($type === 'before')
      {
        $page->getNode()->moveAsPrevSiblingOf($refPage);
        $page->forceSlugFromParent();
        $moved = true;
      }
      elseif ($type === 'inside')
      {
        if (strlen($refPage->engine))
        {
          throw new sfException('Attempt to make a page a child of an engine page');
        }
        $page->getNode()->moveAsLastChildOf($refPage);
        $page->forceSlugFromParent();
        $moved = true;
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
    // Notify an event before we unlock, gives project level code a chance
    // to safely do anything specialized
    if ($moved)
    {
      $event = new sfEvent($page, 'a.afterTreeMove', array());
      $this->dispatcher->notify($event);
    }
    $this->unlockTree();
    echo("ok");
    exit(0);
  }
  
  /**
   * Delete the page with the id specified by 'id' and return an AJAX response
   * compatible with the reorganize feature's jstree implementation
   * @param mixed $request
   * @return mixed
   */
  public function executeTreeDelete($request)
  {
    $this->lockTree();
    try
    {
      $page = $this->retrievePageForEditingByIdParameter('id', 'manage');
      if ($page->level < 1)
      {
        // Refuse to delete root, non-tree pages, etc.
        throw new Exception('Attempt to delete root or non-tree page');
      }
      // tom@punkave.com: we must delete via the nested set
      // node or we'll corrupt the tree. Nasty detail, that.
      // Note that this implicitly calls $page->delete()
      // (but the reverse was not true and led to problems).
      $page->getNode()->delete(); 
    } catch (Exception $e)
    {
      $this->unlockTree();
      $this->forward404();
    }
    $this->unlockTree();
    echo("ok");
    exit(0);
  }

  /**
   * DOCUMENT ME
   * @param mixed $parents
   * @return mixed
   */
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

  /**
   * DOCUMENT ME
   * @param mixed $lastPage
   * @param mixed $parents
   * @param mixed $minusLevels
   * @return mixed
   */
  protected function generateAfterPageInfo($lastPage, $parents, $minusLevels)
  {
    $pageInfo = array();
    $pageInfo['id'] = 'after-' . $lastPage->getId();
    $pageInfo['title'] = 'after';
    $pageInfo['level'] = $lastPage->getLevel() - $minusLevels;
    $pageInfo['class'] = 'pagetree-after ' . $this->getParentClasses($parents);
    return $pageInfo;
  }

  /**
   * DOCUMENT ME
   * @param mixed $condition
   * @return mixed
   */
  protected function flunkUnless($condition)
  {
    if ($condition)
    {
      return;
    }
    $this->unlockTree();
    $this->forward('a', 'cleanSignin');
  }

  /**
   * Do NOT use these as the default signin actions. They are special-purpose
   * ajax/iframe breakers for use in forcing the user back to the login page
   * when they try to do an ajax action after timing out.
   * @param sfWebRequest $request
   */
  public function executeCleanSignin(sfWebRequest $request)
  {
    // Template is a frame/ajax breaker, redirects to phase 2
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   */
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

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
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

  /**
   * These have been refactored
   */
  protected function lockTree()
  {
    aTools::lock('tree');
  }

  /**
   * It's OK to call this if there is no lock.
   * Eases its use in calls like flunkUnless
   */
  protected function unlockTree()
  {
    aTools::unlock();
  }
}

