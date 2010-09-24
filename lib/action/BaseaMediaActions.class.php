<?php

class BaseaMediaActions extends aEngineActions
{

	public function preExecute()
	{
	  // Establish engine context
    parent::preExecute();
  	$response = sfContext::getInstance()->getResponse();    
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
	}

  // Supported for backwards compatibility. See also 
  // aMediaSelect::select()
  
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
    aMediaTools::setSelecting($after, $multiple, $selection, $options);
      
    return $this->redirect("aMedia/index");
  }
  
  public function executeIndex(sfRequest $request)
  {
    $params = array();
    $tag = $request->getParameter('tag');
    $type = aMediaTools::getType();
    $type = $type ? $type : $request->getParameter('type');
    $typeInfos = aMediaTools::getTypeInfos($type);
    $this->embedAllowed = false;
    $this->uploadAllowed = false;
    foreach ($typeInfos as $typeInfo)
    {
      if ($typeInfo['embeddable'])
      {
        $this->embedAllowed = true;
      }
      if (count($typeInfo['extensions']))
      {
        $this->uploadAllowed = true;
      }
    }
    $category = $request->getParameter('category');
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
    // Cheap insurance that these are integers
    $aspectWidth = floor(aMediaTools::getAttribute('aspect-width'));
    $aspectHeight = floor(aMediaTools::getAttribute('aspect-height'));

    if ($type === 'image')
    {
      // Now that we provide cropping tools, width and height should only exclude images
      // that are too small to ever be cropped to that size
      $minimumWidth = floor(aMediaTools::getAttribute('minimum-width'));
      $width = floor(aMediaTools::getAttribute('width'));
      $minimumWidth = max($minimumWidth, $width);
      $minimumHeight = floor(aMediaTools::getAttribute('minimum-height'));
      $height = floor(aMediaTools::getAttribute('height'));
      $minimumHeight = max($minimumHeight, $height);
      // Careful, aspect ratio can impose a bound on the other dimension
      if ($minimumWidth && $aspectWidth)
      {
        $minimumHeight = max($minimumHeight, $minimumWidth * $aspectHeight / $aspectWidth);
      }
      if ($minimumHeight && $aspectHeight)
      {
        $minimumWidth = max($minimumWidth, $minimumHeight * $aspectWidth / $aspectHeight);
      }
      // We've updated these with implicit constraints from the aspect ratio, the width and height params, etc.
      aMediaTools::setAttribute('minimum-width', $minimumWidth);
      aMediaTools::setAttribute('minimum-height', $minimumHeight);
      $params['minimum-width'] = $minimumWidth;
      $params['minimum-height'] = $minimumHeight;
    }
    else
    {
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
    }
    // The media module is now an engine module. There is always a page, and that
    // page might have a restricted set of categories associated with it
    $mediaCategories = aTools::getCurrentPage()->Categories;
    if (count($mediaCategories))
    {
      $params['allowed_categories'] = $mediaCategories;
    }

    $query = aMediaItemTable::getBrowseQuery($params);

    $this->pager = new sfDoctrinePager(
      'aMediaItem',
      aMediaTools::getOption('per_page'));
    $page = $request->getParameter('page', 1);
    $this->pager->setQuery($query);
    if($request->hasParameter('max_per_page'))
    {
      $this->getUser()->setAttribute('max_per_page', $request->getParameter('max_per_page'), 'apostrophe_media');
    }
    $this->max_per_page = $this->getUser()->getAttribute('max_per_page', 20, 'apostrophe_media');
    $this->pager->setMaxPerPage($this->max_per_page);
    $this->pager->setPage($page);
    $this->pager->init();
    $this->results = $this->pager->getResults();
    Taggable::preloadTags($this->results);
    // Go to the last page if we are beyond it
    if (($page > 1) && ($page > $this->pager->getLastPage()))
    {
      $page--;
      $params['page'] = $page;
      return $this->redirect('aMedia/index?' . http_build_query($params));
    }
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
      $this->limitSizes = ($minimumWidth || $minimumHeight);
    }
    if($request->hasParameter('layout'))
    {
      $this->getUser()->setAttribute('layout', $request->getParameter('layout'), 'apostrophe_media');
    }
    $this->layout = aMediaTools::getLayout($this->getUser()->getAttribute('layout', 'two-up', 'apostrophe_media'));
    $this->enabled_layouts = aMediaTools::getEnabledLayouts();
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
  
  // Accept and store cropping information for a particular image which must already be part of the selection
  public function executeCrop(sfRequest $request)
  {
    $selection = aMediaTools::getSelection();
    $id = $request->getParameter('id');
    $index = array_search($id, $selection);
    if ($index === false)
    {
      error_log("ID is $id and not found in index which is " . implode(',', $selection));
      $this->forward404();
    }
    $cropLeft = floor($request->getParameter('cropLeft'));
    $cropTop = floor($request->getParameter('cropTop'));
    $cropWidth = floor($request->getParameter('cropWidth'));
    $cropHeight = floor($request->getParameter('cropHeight'));
    $width = floor($request->getParameter('width'));
    $height = floor($request->getParameter('height'));
    $imageInfo = aMediaTools::getAttribute('imageInfo');
    $imageInfo[$id]['cropLeft'] = $cropLeft;
    $imageInfo[$id]['cropTop'] = $cropTop;
    $imageInfo[$id]['cropWidth'] = $cropWidth;
    $imageInfo[$id]['cropHeight'] = $cropHeight;
    $imageInfo[$id]['width'] = $width;
    $imageInfo[$id]['height'] = $height;
    aMediaTools::setAttribute('imageInfo', $imageInfo);
  }
  
  public function executeMultipleAdd(sfRequest $request)
  {
    $id = $request->getParameter('id') + 0;
    $item = Doctrine::getTable("aMediaItem")->find($id);
    $this->forward404Unless($item); 
    $selection = aMediaTools::getSelection();
    if (!aMediaTools::isMultiple())
    {
      $selection = array($id);
    }
    else
    {
      $index = array_search($id, $selection);
      // One occurrence each. If this changes we'll have to rethink
      // the way reordering and deletion work (probably go by index).
      if ($index === false)
      {
        $selection[] = $id;
      }
    }
    aMediaTools::setSelection($selection);
    $imageInfo = aMediaTools::getAttribute('imageInfo');
    // Make no attempt to scrub out a previous crop, which could be handy
    $imageInfo[$id]['width'] = $item->getWidth();
    $imageInfo[$id]['height'] = $item->getHeight();
    aMediaTools::setAttribute('imageInfo', $imageInfo);
    if ($item->getCroppable())
    {
      // If no previous crop info is set, we must set an intial cropping mask
      // so that the cropped media item id gets linked instead of the original
      // media item. This is a little dangerous because JavaScript computes an
      // intial crop mask on the client side.
      aMediaTools::setDefaultCropDimensions($item);
    }
    if ((!aMediaTools::isMultiple()) && aMediaTools::getAttribute('type') !== 'image')
    {
      return $this->redirect('aMedia/selected');
    }
  }

  public function executeMultipleRemove(sfRequest $request)
  {
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
  }

  public function executeUpdateMultiplePreview(sfRequest $request)
  {
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
    $this->forward404Unless(aMediaTools::isSelecting());
    $selection = aMediaTools::getSelection();
    $imageInfo = aMediaTools::getAttribute('imageInfo');
    // Get all the items in preparation for possible cropping
    if (count($selection))
    {
      $items = Doctrine::getTable('aMediaItem')->createQuery('m')->whereIn('m.id', $selection)->execute();
    }
    else
    {
      $items = array();
    }
    $items = aArray::listToHashById($items);
    $newSelection = array();
    foreach ($selection as $id)
    {
      $nid = $id;
      // Try not to make gratuitous crops
      if (isset($imageInfo[$id]))
      {
        $item = $items[$id];
        $i = $imageInfo[$id];
        if ($item->getCroppable() && isset($i['cropLeft']) && (($i['cropLeft'] > 0) || ($i['cropTop'] > 0) || ($i['cropWidth'] != $item->width) || ($i['cropHeight'] != $item->height)))
        {
          // We need to make a crop
          $item = $items[$id];
          $crop = $item->findOrCreateCrop($imageInfo[$id]);
          $crop->save();
          $nid = $crop->id;
        }
        $newSelection[] = $nid;
      }
    }
    // Ooops best to get this before clearing it huh
    $after = aMediaTools::getAfter();
    
    // addParamsNoDelete never attempts to eliminate a field just because
    // its value is empty. This is how we distinguish between cancellation
    // and selecting zero items
    
    if (!aMediaTools::isMultiple())
    {
      // Call this too soon and you lose isMultiple
      aMediaTools::clearSelecting();
      if (count($newSelection))
      {
        $after = aUrl::addParams($after, 
          array("aMediaId" => $newSelection[0]));
        return $this->redirect($after);
      }
      else
      {
        $this->forward404();
      }
    }
    else
    {
      aMediaTools::clearSelecting();
      $url = aUrl::addParamsNoDelete($after,
      array("aMediaIds" => implode(",", $newSelection)));
      return $this->redirect($url);
    }
  }

  public function executeSelectCancel(sfRequest $request)
  {
    $this->forward404Unless(aMediaTools::isSelecting());
    $after = aUrl::addParams(aMediaTools::getAfter(),
      array("aMediaCancel" => true));
    aMediaTools::clearSelecting();
    return $this->redirect($after);
  }

  public function executeEdit(sfRequest $request)
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
    $this->form = new aMediaEditForm($item);
    $this->postMaxSizeExceeded = false;
    // An empty POST is an anomaly indicating that we hit the php.ini max_post_size or similar
    if ($request->isMethod('post') && (!count($request->getPostParameters())))
    {
      $this->postMaxSizeExceeded = true;
    }
    if ((!$this->postMaxSizeExceeded) && $request->isMethod('post'))
    {
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
          $object->preSaveFile($file->getTempName());
        }
        $this->form->save();
        if ($file)
        {
          $object->saveFile($file->getTempName());
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
            $object->preSaveFile($thumbnail->getTempName());
          }
          $this->form->save();
          if ($thumbnail)
          {
            $object->saveFile($thumbnail->getTempName());                     
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
          $object->preSaveFile($thumbnailCopy);
          $object->setServiceUrl($url);
          $this->form->save();
          $object->saveFile($thumbnailCopy);
          unlink($thumbnailCopy);
        }
        return $this->redirect("aMedia/resumeWithPage");
      } while (false);
    }
  }

  public function executeUpload(sfRequest $request)
  {
    // Belongs at the beginning, not the end
    $this->forward404Unless(aMediaTools::userHasUploadPrivilege());
    
    // This has been simplified. We no longer do real validation in the first pass,
    // we just make sure there is at least one file. Then the validation of the annotation
    // pass can take over to minimize duplicate code
    $this->form = new aMediaUploadMultipleForm();
    $this->mustUploadSomething = false;
    $this->postMaxSizeExceeded = false;
    // An empty POST is an anomaly indicating that we hit the php.ini max_post_size or similar
    if ($request->isMethod('post') && (!count($request->getPostParameters())))
    {
      $this->postMaxSizeExceeded = true;
    }
    if ((!$this->postMaxSizeExceeded) && $request->isMethod('post'))
    {
      $count = 0;
      $request->setParameter('first_pass', true);
      // Saving embedded forms is weird. We can get the form objects
      // via getEmbeddedForms(), but those objects were never really
      // bound, so getValue will fail on them. We have to look at the
      // values array of the parent form instead. The widgets and
      // validators of the embedded forms are rolled into it.
      // See:
      // http://thatsquality.com/articles/can-the-symfony-forms-framework-be-domesticated-a-simple-todo-list
      $items = $request->getParameter("a_media_items");
      $files = $request->getFiles("a_media_items");
      for ($i = 0; ($i < aMediaTools::getOption('batch_max')); $i++)
      {
        $values = $this->form->getValues();
        // This is how we check for the presence of a file upload without a full form validation
        $good = false;
        if (isset($files["item-$i"]['file']))
        {
          $file = $files["item-$i"]['file'];
          if (isset($file['newfile']['tmp_name']) && strlen($file['newfile']['tmp_name']))
          {
            // Humanize the original filename
            $title = $file['newfile']['name'];
            $title = preg_replace('/\.\w+$/', '', $title);
            $title = aTools::slugify($title, false, false, ' ');
            $items["item-$i"]['title'] = $title;
            $count++;
            $good = true;
          }
        }
        if (!$good)
        {
          // So the editImagesForm validator won't complain about these
          unset($items["item-$i"]);
        }
      }
      $request->setParameter("a_media_items", $items);
      if ($count)
      {
        // We're not doing stupid iframe tricks anymore, so we can just forward
        $this->forward('aMedia', 'editMultiple');
      }
      else
      {
        $this->mustUploadSomething = true;
      }
    }
  }

  public function executeEditMultiple(sfRequest $request)
  {
    $this->forward404Unless(aMediaTools::userHasUploadPrivilege());

    // I'd put these in the form class, but I can't seem to access them
    // there unless the whole form is valid. I need them as metadata
    // to control the form's behavior, so that won't cut it.
    // Perhaps I could put them in a second form, since there's
    // no actual restriction on multiple form objects inside a 
    // single HTML form element.
    $this->firstPass = $request->getParameter('first_pass');
    $items = $request->getParameter('a_media_items');
    // The active parameter was redundant, just look at the items that are present.
    // This allows successive passes to prune out some items if desired
    $active = array();
    foreach ($items as $itemName => $item)
    {
      if (preg_match('/^item-(\d+)$/', $itemName, $matches))
      {
        $active[] = $matches[1];
      }
    }

    $this->form = new aMediaEditMultipleForm($active);
    $this->form->bind(
      $request->getParameter('a_media_items'),
      $request->getFiles('a_media_items'));
      
    $this->postMaxSizeExceeded = false;
    // An empty POST is an anomaly indicating that we hit the php.ini max_post_size or similar
    if ($request->isMethod('post') && (!count($request->getPostParameters())))
    {
      // This is a bummer because you've lost your annotation work but we really can't
      // resurrect it from an empty POST, short of keeping everything in attributes
      $this->forward('aMedia', 'upload');
    }
      
    if ((!$this->firstPass) && $this->form->isValid())
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
        $object->unlink('Categories');
        $object->link('Categories', $values[$key]['categories_list']);
        
        // Everything except the actual copy which can't succeed
        // until the slug is cast in stone
        $file = $values[$key]['file'];
        
        $format = $file->getExtension();
        if (strlen($format))
        {
          // Starts with a .
          $format = substr($format, 1);
        }
        $object->format = $format;
        $types = aMediaTools::getOption('types');
        foreach ($types as $type => $info)
        {
          $extensions = $info['extensions'];
          if (in_array($format, $extensions))
          {
            $object->type = $type;
          }
        }
        $object->preSaveFile($file->getTempName());
        $object->save();
        $object->saveFile($file->getTempName());
      }
      return $this->redirect('aMedia/resume');
    }
  }

  public function executeEmbed()
  {
    // TODO: rework this to be less video-oriented
    return $this->redirect('aMedia/newVideo');
  }
  
  public function executeDelete()
  {
    $item = $this->getItem();
    if ($item)
    {
      $this->forward404Unless($item->userHasPrivilege('delete'));
    }
    $item->delete(); 
    return $this->redirect("aMedia/resumeWithPage");
  }
  
  public function executeShow()
  {
    $this->mediaItem = $this->getItem();
    $this->layout = aMediaTools::getLayout($this->getUser()->getAttribute('layout', 'two-up', 'apostrophe_media'));
		// This sets the gallery image dimensions to the correct dimensions for showSuccess
		// Doing this here seemed like a good way to keep the templates cleaner
		$this->layout['gallery_constraints'] = $this->layout['show_constraints'];
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
}
