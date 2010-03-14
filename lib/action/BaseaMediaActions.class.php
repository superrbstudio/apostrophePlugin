<?php

class BaseaMediaActions extends aEngineActions
{

	public function preExecute()
	{
	  // Establish engine context
    parent::preExecute();
    
    // If this is the admin engine page for media, and you have no media uploading privileges
    // or page editing privileges, then you have no business being here. If it is not the admin page,
    // then the site admin has decided to add a public media engine page, and it's fine for anyone 
    // to be here
    if (aTools::getCurrentPage()->admin)
    {
      if (!(aTools::isPotentialEditor() || aMediaTools::userHasUploadPrivilege()))
			{
				$this->forward(sfConfig::get('sf_login_module'), sfConfig::get('sf_login_action'));
			}
    }
    
 		//$this->getResponse()->addStylesheet('/apostrophePlugin/css/aToolkit.css', 'first'); // Merged into a.css 2/3/2010
   	$this->getResponse()->addStylesheet('/apostrophePlugin/css/a.css', 'first');	
    $this->getResponse()->addJavascript('/apostrophePlugin/js/aControls.js');	
    $this->getResponse()->addJavascript('/apostrophePlugin/js/aUI.js');
    $this->getResponse()->addJavascript('/apostrophePlugin/js/jquery.hotkeys-0.7.9.min.js');		
	}

  public function executeSelect(sfRequest $request)
  {
    $after = $request->getParameter('after');
    // Prevent possible header insertion tricks
    $after = preg_replace("/\s+/", " ", $after);
    $multiple = !!$request->getParameter('multiple');
    if ($multiple)
    {
      $selection = preg_split("/\s*,\s*/", $request->getParameter('aMediaIds'));
    }
    else
    {
      $selection = array($request->getParameter('aMediaId') + 0);
    } 
    $items = aMediaItemTable::retrieveByIds($selection);
    $ids = array();
    foreach ($items as $item)
    {
      $ids[] = $item->getId();
    }
    $options = array();
    $optional = array('type', 'aspect-width', 'aspect-height',
      'minimum-width', 'minimum-height', 'width', 'height', 'label');
    foreach ($optional as $option)
    {
      if ($request->hasParameter($option))
      {
        $options[$option] = $request->getParameter($option);
      }
    }
    aMediaTools::setSelecting($after, $multiple, $ids, $options);
      
    return $this->redirect("aMedia/index");
  }
  
  public function executeIndex(sfRequest $request)
  {
    $params = array();
    $tag = $request->getParameter('tag');
    $type = $request->getParameter('type');
    $category = $request->getParameter('category');
    if (aMediaTools::getType())
    {
      $type = aMediaTools::getType();
    }
    $search = $request->getParameter('search');
    if ($request->isMethod('post'))
    {
      // Give the routing engine a shot at making the URL pretty.
      // We use addParams because it automatically deletes any
      // params with empty values. (To be fair, http_build_query can't
      // do that because some crappy web application might actually
      // use checkboxes with empty values, and that's not
      // technically wrong. We have the luxury of saying "reasonable
      // people who work here don't do that.")
      return $this->redirect(aUrl::addParams("aMedia/index",
        array("tag" => $tag, "search" => $search, "type" => $type)));
    }
    if (!empty($tag))
    {
      $params['tag'] = $tag;
    }
    if (!empty($search))    
    {
      $params['search'] = $search;      
    }
    if (!empty($type))
    {
      $params['type'] = $type;
    }
    if (!empty($category))
    {
      $params['category'] = $category;
    }
    $user = $this->getUser();
    if ($user->isAuthenticated() && method_exists($user, "getGuardUser"))
    {
      $params['user'] = $user->getGuardUser()->getUsername();
    }
    // Cheap insurance that these are integers
    $aspectWidth = floor(aMediaTools::getAttribute('aspect-width'));
    $aspectHeight = floor(aMediaTools::getAttribute('aspect-height'));
    // TODO: performance of these is not awesome (it's a linear search). 
    // It would be more awesome with the right kind of indexing. For the 
    // aspect ratio test to be more efficient we'd have to store the lowest 
    // common denominator aspect ratio and index that.
    if ($aspectWidth && $aspectHeight)
    {
      $params['aspect-width'] = $aspectWidth;
      $params['aspect-height'] = $aspectHeight;
    }

    $minimumWidth = floor(aMediaTools::getAttribute('minimum-width'));
    if ($minimumWidth)
    {
      $params['minimum-width'] = $minimumWidth;
    }
    $minimumHeight = floor(aMediaTools::getAttribute('minimum-height'));
    if ($minimumHeight)
    {
      $params['minimum-height'] = $minimumHeight;
    }
    $width = floor(aMediaTools::getAttribute('width'));
    if ($width)
    {
      $params['width'] = $width;
    }
    $height = floor(aMediaTools::getAttribute('height'));
    if ($height)
    {
      $params['height'] = $height;
    }

    // The media module is now an engine module. There is always a page, and that
    // page might have a restricted set of categories associated with it
    $mediaCategories = aTools::getCurrentPage()->MediaCategories;
    if (count($mediaCategories))
    {
      $params['allowed_categories'] = $mediaCategories;
    }

    $query = aMediaItemTable::getBrowseQuery($params);

    $this->pager = new sfDoctrinePager(
      'aMediaItem',
      aMediaTools::getOption('per_page'));
    $this->pager->setQuery($query);
    $page = $request->getParameter('page', 1);
    $this->pager->setPage($page);
    $this->pager->init();
    $this->results = $this->pager->getResults();
    aMediaTools::setSearchParameters(
      array("tag" => $tag, "type" => $type, 
        "search" => $search, "page" => $page, 'category' => $category));

    $this->pagerUrl = "aMedia/index?" .
      http_build_query($params);
    if (aMediaTools::isSelecting())
    {
      $this->selecting = true;
      if (aMediaTools::getAttribute("label"))
      {
        $this->label = aMediaTools::getAttribute("label");
      }
      $this->limitSizes = false;
      if ($aspectWidth || $aspectHeight || $minimumWidth || $minimumHeight ||
        $width || $height)
      {
        $this->limitSizes = true;
      }
    }
  }
  
  public function executeResume()
  {
    return $this->resumeBody(false);
  }

  public function executeResumeWithPage()
  {
    return $this->resumeBody(true);
  }

  protected function resumeBody($withPage = false)
  {
    $parameters = aMediaTools::getSearchParameters();
    if (!$withPage)
    {
      if (isset($parameters['page']))
      {
        unset($parameters['page']);
      }
    }
    if (isset($parameters['page']))
    {
      // keep the URL clean
      if ($parameters['page'] == 1)
      {
        unset($parameters['page']);
      }
    }
    return $this->redirect(aUrl::addParams("aMedia/index",
      $parameters));
  }

  public function executeMultipleAdd(sfRequest $request)
  {
    $this->forward404Unless(aMediaTools::isMultiple());
    $id = $request->getParameter('id') + 0;
    $item = Doctrine::getTable("aMediaItem")->find($id);
    $this->forward404Unless($item); 
    $selection = aMediaTools::getSelection();
    $index = array_search($id, $selection);
    // One occurrence each. If this changes we'll have to rethink
    // the way reordering and deletion work (probably go by index).
    if ($index === false)
    {
      $selection[] = $id;
    }
    aMediaTools::setSelection($selection);
    return $this->renderComponent("aMedia", "multipleList");
  }

  public function executeMultipleRemove(sfRequest $request)
  {
    $this->forward404Unless(aMediaTools::isMultiple());
    $id = $request->getParameter('id');
    $item = Doctrine::getTable("aMediaItem")->find($id);
    $this->forward404Unless($item); 
    $selection = aMediaTools::getSelection();
    $index = array_search($id, $selection);
    if ($index !== false)
    {
      array_splice($selection, $index, 1);
    }
    aMediaTools::setSelection($selection);
    return $this->renderComponent("aMedia", "multipleList");
  }

  public function executeMultipleOrder(sfRequest $request)
  {
    $this->logMessage("*****MULTIPLE ORDER", "info");
    $order = $request->getParameter('a-media-selection-list-item');
    $oldSelection = aMediaTools::getSelection();
    $keys = array_flip($oldSelection);
    $selection = array();
    foreach ($order as $id)
    {
      $id += 0;
      $this->logMessage(">>>>>ID is $id", "info");
      $item = Doctrine::getTable("aMediaItem")->find($id);
      if ($item)
      {
        $selection[] = $item->getId();
      }
      $this->forward404Unless(isset($keys[$item->getId()]));
      $this->logMessage(">>>KEEPING " . $item->getId(), "info");
    }
    $this->logMessage(">>>SUCCEEDED: " . implode(", ", $selection), "info");
    aMediaTools::setSelection($selection);
    return $this->renderComponent("aMedia", "multipleList");
  }
  public function executeSelected(sfRequest $request)
  {
    $controller = $this->getController();
    $this->forward404Unless(aMediaTools::isSelecting());
    if (aMediaTools::isMultiple())
    {
      $selection = aMediaTools::getSelection();
      // Ooops best to get this before clearing it huh
      $after = aMediaTools::getAfter();
      // Oops I forgot to call this in the multiple case
      aMediaTools::clearSelecting();
      // I thought about submitting this like a multiple select,
      // but there's no clean way to implement that feature in
      // addParam, and it wastes URL space anyway
      // (remember the 1024-byte limit)
      
      // addParamsNoDelete never attempts to eliminate a field just because
      // its value is empty. This is how we distinguish between cancellation
      // and selecting zero items
      return $this->redirect(
        aUrl::addParamsNoDelete($after,
        array("aMediaIds" => implode(",", $selection))));
    }
    // Single select
    $id = $request->getParameter('id');
    $item = Doctrine::getTable("aMediaItem")->find($id);
    $this->forward404Unless($item); 
    $after = aMediaTools::getAfter();
    $after = aUrl::addParams($after, 
      array("aMediaId" => $id));
    aMediaTools::clearSelecting();
    return $this->redirect($after);
  }

  public function executeSelectCancel(sfRequest $request)
  {
    $this->forward404Unless(aMediaTools::isSelecting());
    $after = aUrl::addParams(aMediaTools::getAfter(),
      array("aMediaCancel" => true));
    aMediaTools::clearSelecting();
    return $this->redirect($after);
  }

  public function executeEditImage(sfRequest $request)
  {
    $this->forward404Unless(aMediaTools::userHasUploadPrivilege());
    $item = null;
    $this->slug = false;
    if ($request->hasParameter('slug'))
    {
      $item = $this->getItem();
      $this->slug = $item->getSlug();
    }
    if ($item)
    {
      $this->forward404Unless($item->userHasPrivilege('edit'));
    }
    $this->item = $item;
    $this->form = new aMediaImageForm($item);
    if ($request->isMethod('post'))
    {
      $this->firstPass = $request->getParameter('first_pass');
      $parameters = $request->getParameter('a_media_item');
      $files = $request->getFiles('a_media_item');
      $this->form->bind($parameters, $files);
      if ($this->form->isValid())
      {
        $file = $this->form->getValue('file');
        // The base implementation for saving files gets confused when 
        // $file is not set, a situation that our code tolerates as useful 
        // because if you're updating a record containing an image you 
        // often don't need to submit a new one.
        unset($this->form['file']);
        $object = $this->form->getObject();
        if ($file)
        {
          // Everything except the actual copy which can't succeed
          // until the slug is cast in stone
          $object->preSaveImage($file->getTempName());
        }
        $this->form->save();
        if ($file)
        {
          $object->saveImage($file->getTempName());
        }
        return $this->redirect("aMedia/resumeWithPage");
      }
    }
  }

  public function executeEditPdf(sfRequest $request)
  {
    $this->forward404Unless(aMediaTools::userHasUploadPrivilege());
    $item = null;
    $this->slug = false;
    if ($request->hasParameter('slug'))
    {
      $item = $this->getItem();
      $this->slug = $item->getSlug();
    }
    if ($item)
    {
      $this->forward404Unless($item->userHasPrivilege('edit'));
    }
    $this->item = $item;
    $this->form = new aMediaPdfForm($item);
    if ($request->isMethod('post'))
    {
      $this->firstPass = $request->getParameter('first_pass');
      $parameters = $request->getParameter('a_media_item');
      $files = $request->getFiles('a_media_item');
      $this->form->bind($parameters, $files);
      if ($this->form->isValid())
      {
        $file = $this->form->getValue('file');
        unset($this->form['file']);
        $object = $this->form->getObject();
        if ($file)
        {
          // The base implementation for saving files gets confused when 
          // $file is not set, a situation that our code tolerates as useful 
          // because if you're updating a record containing a PDF you 
          // often don't need to submit a new one.
          
          // This actually has to be shimmed in at a much lower level as an option if
          // gs is not available. We can't just use a fake thumbnail as an 'original' as we
          // do for foreign video because that would break 'download original'
          // copy(sfConfig::get('sf_root_dir') . '/plugins/apostrophePlugin/web/images/a-media-pdf-btn-small.png', $previewFile);
          
          // Everything except the actual copy which can't succeed
          // until the slug is cast in stone
          $object->preSaveImage($file->getTempName());
        }
        $this->form->save();
        if ($file)
        {
          $object->saveImage($file->getTempName());
        }
        return $this->redirect("aMedia/resumeWithPage");
      }
    }
  }

  public function executeEditVideo(sfRequest $request)
  {
    $this->forward404Unless(aMediaTools::userHasUploadPrivilege());
    $item = null;
    $this->slug = false;
    if ($request->hasParameter('slug'))
    {
      $item = $this->getItem();
      $this->slug = $item->getSlug();
    }
    if ($item)
    {
      $this->forward404Unless($item->userHasPrivilege('edit'));
    }
    $this->item = $item;
    $subclass = 'aMediaVideoYoutubeForm';
    $embed = false;
    $parameters = $request->getParameter('a_media_item');
    if (aMediaTools::getOption('embed_codes') && 
      (($item && strlen($item->embed)) || (isset($parameters['embed']))))
    {
      $subclass = 'aMediaVideoEmbedForm';
      $embed = true;
    }
    $this->form = new $subclass($item);
    if ($parameters)
    {
      $files = $request->getFiles('a_media_item');
      $this->form->bind($parameters, $files);

      do
      {
        // first_pass forces the user to interact with the form
        // at least once. Used when we're coming from a
        // YouTube search and we already technically have a
        // valid form but want the user to think about whether
        // the title is adequate and perhaps add a description,
        // tags, etc.
        if (($this->hasRequestParameter('first_pass')) || 
          (!$this->form->isValid()))
        {
          break;
        }
        // TODO: this is pretty awful factoring, I should have separate actions
        // and migrate more of this code into the model layer
        if ($embed)
        {
          $embed = $this->form->getValue("embed");
          $thumbnail = $this->form->getValue('thumbnail');
          // The base implementation for saving files gets confused when 
          // $file is not set, a situation that our code tolerates as useful 
          // because if you're updating a record containing an image you 
          // often don't need to submit a new one.
          unset($this->form['thumbnail']);
          $object = $this->form->getObject();
          if ($thumbnail)
          {
            $object->preSaveImage($thumbnail->getTempName());
          }
          $this->form->save();
          if ($thumbnail)
          {
            $object->saveImage($thumbnail->getTempName());                     
          }
        }
        else
        {
          $url = $this->form->getValue("service_url");
          // TODO: migrate this into the model and a 
          // YouTube-specific support class
          if (!preg_match("/youtube.com.*\?.*v=([\w\-\+]+)/", 
            $url, $matches))
          {
            $this->serviceError = true;
            break;
          }
          // YouTube thumbnails are always JPEG
          $format = 'jpg';
          $videoid = $matches[1];
          $feed = "http://gdata.youtube.com/feeds/api/videos/$videoid";
          $entry = simplexml_load_file($feed);
          // get nodes in media: namespace for media information
          $media = $entry->children('http://search.yahoo.com/mrss/');
            
          // get a more canonical video player URL
          $attrs = $media->group->player->attributes();
          $canonicalUrl = $attrs['url']; 
          // get biggest video thumbnail
          foreach ($media->group->thumbnail as $thumbnail)
          {
            $attrs = $thumbnail->attributes();
            if ((!isset($widest)) || (($attrs['width']  + 0) > 
              ($widest['width'] + 0)))
            {
              $widest = $attrs;
            }
          }
          // The YouTube API doesn't report the original width and height of
          // the video stream, so we use the largest thumbnail, which in practice
          // is the same thing on YouTube.
          if (isset($widest))
          {
            $thumbnail = $widest['url']; 
            // Turn them into actual numbers instead of weird XML wrapper things
            $width = $widest['width'] + 0;
            $height = $widest['height'] + 0;
          }
          if (!isset($thumbnail))
          {
            $this->serviceError = true;
            break;
          }
          // Grab a local copy of the thumbnail, and get the pain
          // over with all at once in a predictable way if 
          // the service provider fails to give it to us.
       
          $thumbnailCopy = aFiles::getTemporaryFilename();
          if (!copy($thumbnail, $thumbnailCopy))
          {
            $this->serviceError = true;
            break;
          }
          $object = $this->form->getObject();
          $new = !$object->getId();
          $object->preSaveImage($thumbnailCopy);
          $object->setServiceUrl($url);
          $this->form->save();
          $object->saveImage($thumbnailCopy);
          unlink($thumbnailCopy);
        }
        return $this->redirect("aMedia/resumeWithPage");
      } while (false);
    }
  }

  public function executeUploadImages(sfRequest $request)
  {
    // Belongs at the beginning, not the end
    $this->forward404Unless(aMediaTools::userHasUploadPrivilege());
    $this->form = new aMediaUploadImagesForm();
    if ($request->isMethod('post'))
    {
      $this->form->bind(
        $request->getParameter('a_media_items'),
        $request->getFiles('a_media_items'));
      if ($this->form->isValid())
      {
        $request->setParameter('first_pass', true);
        $active = array();
        // Saving embedded forms is weird. We can get the form objects
        // via getEmbeddedForms(), but those objects were never really
        // bound, so getValue will fail on them. We have to look at the
        // values array of the parent form instead. The widgets and
        // validators of the embedded forms are rolled into it.
        // See:
        // http://thatsquality.com/articles/can-the-symfony-forms-framework-be-domesticated-a-simple-todo-list
        for ($i = 0; ($i < aMediaTools::getOption('batch_max')); $i++)
        {
          $values = $this->form->getValues();
          if ($values["item-$i"]['file'])
          {
            $active[] = $i;
          }
          else
          {
            // So the editImagesForm validator won't complain about these
            $items = $request->getParameter("a_media_items");
            unset($items["item-$i"]);
            $request->setParameter("a_media_items", $items);
          }
        }
        $request->setParameter('active', implode(",", $active));
        // We'd like to just do this...
        // $this->forward('aMedia', 'editImages');
        // But we need to break out of the iframe, and 
        // modern browsers ignore Window-target: _top which
        // would otherwise be perfect for this.
        // Fortunately, the persistent file upload widget can tolerate
        // a GET-method redirect very nicely as long as we pass the
        // persistids. So we make the current parameters available
        // to a template that breaks out of the iframe via
        // JavaScript and passes the prameters on.
        $this->parameters = $request->getParameterHolder('a_media_items')->getAll();
        // If I don't do this I just get redirected back to myself
        unset($this->parameters['module']);
        unset($this->parameters['action']);
        return 'Redirect';
      }
    }
  }

  public function executeEditImages(sfRequest $request)
  {
    $this->forward404Unless(aMediaTools::userHasUploadPrivilege());

    // I'd put these in the form class, but I can't seem to access them
    // there unless the whole form is valid. I need them as metadata
    // to control the form's behavior, so that won't cut it.
    // Perhaps I could put them in a second form, since there's
    // no actual restriction on multiple form objects inside a 
    // single HTML form element.
    $this->firstPass = $request->getParameter('first_pass');
    $active = $request->getParameter('active');
    $this->forward404Unless(preg_match("/^\d+[\d\,]*$/", $active));
    $this->active = explode(",", $request->getParameter('active'));

    $this->form = new aMediaEditImagesForm($this->active);
    $this->form->bind(
      $request->getParameter('a_media_items'),
      $request->getFiles('a_media_items'));
    if ($this->form->isValid())
    {
      $values = $this->form->getValues();
      // This is NOT automatic since this isn't a Doctrine form. http://thatsquality.com/articles/can-the-symfony-forms-framework-be-domesticated-a-simple-todo-list
      foreach ($this->form->getEmbeddedForms() as $key => $itemForm)
      {
        $itemForm->updateObject($values[$key]);
        $object = $itemForm->getObject();
        if ($object->getId())
        {
          // We're creating new objects only here, but the embedded form 
          // supports an id for an existing object, which is useful in
          // other contexts. Prevent hackers from stuffing in changes
          // to media items they don't own.
          $this->forward404();
        }

        // updateObject doesn't handle one-to-many relations, only save() does, and we
        // can't do save() in this embedded form, so we need to implement the categories
        // relation ourselves        
        $object->unlink('MediaCategories');
        $object->link('MediaCategories', $values[$key]['media_categories_list']);
        
        // Everything except the actual copy which can't succeed
        // until the slug is cast in stone
        $file = $values[$key]['file'];
        $object->preSaveImage($file->getTempName());
        $object->save();
        $object->saveImage($file->getTempName());
      }
      return $this->redirect('aMedia/resume');
    }
  }

  public function executeDelete()
  {
    $item = $this->getItem();
    if ($item)
    {
      $this->forward404Unless($item->userHasPrivilege('delete'));
    }
    $item->delete(); 
    return $this->redirect("aMedia/resume");
  }
  
  public function executeShow()
  {
    $this->mediaItem = $this->getItem();
  }
  
  private function getItem()
  {
    return aMediaTools::getItem($this);
  }

  public function executeRefreshItem(sfRequest $request)
  {
    $item = $this->getItem();
    return $this->renderPartial('aMedia/mediaItem',
      array('mediaItem' => $item));
  }

  public function executeVideoSearch(sfRequest $request)
  {
    $this->form = new aMediaVideoSearchForm();
    $this->form->bind($request->getParameter('videoSearch'));
    $this->results = false;
    if ($this->form->isValid())
    {
      $q = $this->form->getValue('q');
      $this->results = aYoutube::search($q); 
    }
    $this->setLayout(false);
  }
  
  protected function setIframeLayout()
  {
    $this->setLayout(sfContext::getInstance()->getConfiguration()->getTemplateDir('aMedia', 'iframe.php').'/iframe');
  }

  public function executeNewVideo()
  {
    $this->videoSearchForm = new aMediaVideoSearchForm();
  }
  
  // AJAX media categories admin methods. Simple and sweet. I love renderPartial
  
  public function executeEditCategories(sfWebRequest $request)
  {
    $this->forward404Unless($this->getUser()->hasCredential('media_admin'));
    return $this->renderEditCategory();
  }

  public function executeDeleteCategory(sfWebRequest $request)
  {
    $this->forward404Unless($this->getUser()->hasCredential('media_admin'));
    $slug = $request->getParameter('slug');
    $category = Doctrine::getTable('aMediaCategory')->createQuery('c')->where('c.slug = ?', array($slug))->fetchOne();
    if ($category)
    {
      $category->delete();
    }
    return $this->renderEditCategory();
  }
  
  public function executeAddCategory(sfWebRequest $request)
  {
    $this->forward404Unless($this->getUser()->hasCredential('media_admin'));
    $form = new aMediaCategoryForm();
    $form->bind($request->getParameter('a_media_category'));
    if ($form->isValid())
    {
      $form->save();
    }
    return $this->renderEditCategory($form);
  }
  
  protected function renderEditCategory($form = null)
  {
    if (!$form)
    {
      $form = new aMediaCategoryForm();
    }
    $categoriesInfo = Doctrine::getTable('aMediaCategory')->findAllAlphaInfo(true);
    return $this->renderPartial('editCategories', array('categoriesInfo' => $categoriesInfo, 'form' => $form));
  }
}
