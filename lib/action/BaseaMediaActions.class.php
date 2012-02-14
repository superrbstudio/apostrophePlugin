<?php
/**
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaMediaActions extends aEngineActions
{

  /**
   * DOCUMENT ME
   */
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

  /**
   * Supported for backwards compatibility. See also
   * aMediaSelect::select()
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeSelect(sfWebRequest $request)
  {
    $this->hasPermissionsForSelect();
    
    $after = $request->getParameter('after');
    // Prevent possible header insertion tricks
    $after = preg_replace("/\s+/", " ", $after);
    $multiple = !!$request->getParameter('multiple');
    if ($multiple)
    {
      $selection = preg_split("/\s*,\s*/", $request->getParameter('aMediaIds'));
    } else
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

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeIndex(sfWebRequest $request)
  {
    $params = array();
    $tag = $request->getParameter('tag');
    $type = aMediaTools::getType();
    $type = $type ? $type : $request->getParameter('type');
    // It is permissible to filter more narrowly if the overall type is a metatype (_downloadable)
    if (substr($type, 0, 1) === '_')
    {
      if ($request->getParameter('type'))
      {
        $type = $request->getParameter('type');
      }
    }

    $this->embedAllowed = aMediaTools::getEmbedAllowed();
    $this->uploadAllowed = aMediaTools::getUploadAllowed();

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
    } else
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
    if ($request->hasParameter('max_per_page'))
    {
      $this->getUser()->setAttribute('max_per_page', $request->getParameter('max_per_page'), 'apostrophe_media_prefs');
    }
    $this->max_per_page = $this->getUser()->getAttribute('max_per_page', 20, 'apostrophe_media_prefs');
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
    if ($request->hasParameter('layout'))
    {
      $this->getUser()->setAttribute('layout', $request->getParameter('layout'), 'apostrophe_media_prefs');
    }
    $this->layout = aMediaTools::getLayout($this->getUser()->getAttribute('layout', 'two-up', 'apostrophe_media_prefs'));
    $this->enabled_layouts = aMediaTools::getEnabledLayouts();

    return $this->pageTemplate;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function executeResume()
  {
    return $this->resumeBody(false);
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function executeResumeWithPage()
  {
    return $this->resumeBody(true);
  }

  /**
   * DOCUMENT ME
   * @param mixed $withPage
   * @return mixed
   */
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
    // This allows us to pass additional parameters to resume, like '?add=1'
    $extra = $this->getRequest()->getGetParameters();
    $parameters = array_merge($extra, $parameters);
    return $this->redirect(aUrl::addParams("aMedia/index",
        $parameters));
  }

  /**
   * Accept and store cropping information for a particular image which must already be part of the selection
   * @param sfWebRequest $request
   */
  public function executeCrop(sfWebRequest $request)
  {
    $this->hasPermissionsForSelect();
    
    $selection = aMediaTools::getSelection();
    $id = $request->getParameter('id');
    $index = array_search($id, $selection);
    if ($index === false)
    {
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

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeMultipleAdd(sfWebRequest $request)
  {
    $this->hasPermissionsForSelect();
    
    $id = $request->getParameter('id') + 0;
    $item = Doctrine::getTable("aMediaItem")->find($id);
    $this->forward404Unless($item);
    $selection = aMediaTools::getSelection();
    if (!aMediaTools::isMultiple())
    {
      $selection = array($id);
    } else
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

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   */
  public function executeMultipleRemove(sfWebRequest $request)
  {
    $this->hasPermissionsForSelect();
    
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

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   */
  public function executeUpdateMultiplePreview(sfWebRequest $request)
  {
    $this->hasPermissionsForSelect();
    
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeMultipleOrder(sfWebRequest $request)
  {
    $this->hasPermissionsForSelect();
    
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

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeSelected(sfWebRequest $request)
  {
    $this->hasPermissionsForSelect();
    
    $this->forward404Unless(aMediaTools::isSelecting());
    $selection = aMediaTools::getSelection();
    error_log(json_encode($selection));
    $imageInfo = aMediaTools::getAttribute('imageInfo');
    // Get all the items in preparation for possible cropping
    if (count($selection))
    {
      $items = Doctrine::getTable('aMediaItem')->createQuery('m')->whereIn('m.id', $selection)->execute();
    } else
    {
      $items = array();
    }
    $items = aArray::listToHashById($items);
    $newSelection = array();
    foreach ($selection as $id)
    {
      $nid = $id;
      // Try not to make gratuitous crops
      // Also, don't crash if the item is gone from the db
      if (isset($imageInfo[$id]) && isset($items[$id]))
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
    error_log(json_encode($newSelection));
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
      } else
      {
        // Our image UI lets you trash your single selection. Which makes sense.
        // So implement a way of passing that back. It's up to the
        // receiving action to actually respect it of course
        $after = aUrl::addParams($after,
            array("aMediaUnset" => 1));
        return $this->redirect($after);
      }
    } else
    {
      aMediaTools::clearSelecting();
      $url = aUrl::addParamsNoDelete($after,
          array("aMediaIds" => implode(",", $newSelection)));
      return $this->redirect($url);
    }
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeSelectCancel(sfWebRequest $request)
  {
    $this->hasPermissionsForSelect();
    
    $this->forward404Unless(aMediaTools::isSelecting());
    $after = aUrl::addParams(aMediaTools::getAfter(),
        array("aMediaCancel" => true));
    aMediaTools::clearSelecting();
    return $this->redirect($after);
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeEdit(sfWebRequest $request)
  {
    $this->embedAllowed = aMediaTools::getEmbedAllowed();
    $this->uploadAllowed = aMediaTools::getUploadAllowed();  
    $this->forward404Unless(aMediaTools::userHasUploadPrivilege());
    $item = null;
    $this->slug = false;
    $this->popularTags = PluginTagTable::getPopulars(null, array('sort_by_popularity' => true), false, 10);
    if (sfConfig::get('app_a_all_tags', true))
    {
      $this->allTags = PluginTagTable::getAllTagNameWithCount();
    }
    else
    {
      $this->allTags = array();
    }
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
    if ($this->item->getEmbeddable())
    {
      // Handles the embed field correctly
      $this->form = new aMediaVideoForm($item);
    }
    else
    {
      $this->form = new aMediaEditForm($item);
    }
    $this->form->getWidgetSchema()->setNameFormat('a_media_item_'.$item->id.'_%s');
    
    $this->postMaxSizeExceeded = false;
    // An empty POST is an anomaly indicating that we hit the php.ini max_post_size or similar
    if ($request->isMethod('post') && (!count($request->getPostParameters())))
    {
      $this->postMaxSizeExceeded = true;
    }
    if ((!$this->postMaxSizeExceeded) && $request->isMethod('post'))
    {
      $parameters = $request->getParameter('a_media_item_'.$item->id.'_');
      $values = $request->getParameterHolder()->getAll();
      $value = array();
      $files = array();
      foreach ($values as $k => $v)
      {
        if (preg_match('/^a_media_item_' . $item->id . '_(.*)$/', $k, $matches))
        {
          if($k == $this->form['file']->renderId())
          {
            $files[$matches[1]] = $request->getFiles($this->form['file']->renderId());
          } else
          {
            $value[$matches[1]] = $v;
          }
        }
      }  
      $parameters = $value;
      
      if (isset($parameters['embed']))
      {
        // We need to do some prevalidation of the embed code so we can prestuff fields
        $result = $this->form->classifyEmbed($parameters['embed']);
      }
      
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
        if ($request->isXmlHttpRequest())
        {
            return $this->renderPartial('aMedia/mediaItemMeta', array(
              'mediaItem' => $object,
              'popularTags' => $this->popularTags,
              'allTags' => $this->allTags,
              'layout' => aMediaTools::getLayout($this->getUser()->getAttribute('layout', 'two-up', 'apostrophe_media'))
            ));
        }
        return $this->redirect("aMedia/resumeWithPage");
      }
    }
    if ($request->isXmlHttpRequest())
    {
      return 'Ajax';
    }
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeEditVideo(sfWebRequest $request)
  {
    $this->forward404Unless(aMediaTools::userHasUploadPrivilege());
    $item = null;
    $this->slug = false;
    $this->popularTags = PluginTagTable::getPopulars(null, array('sort_by_popularity' => true), false, 10);
    if (sfConfig::get('app_a_all_tags', true))
    {
      $this->allTags = PluginTagTable::getAllTagNameWithCount();
    }
    else
    {
      $this->allTags = array();
    }
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
    $embed = false;
    $parameters = $request->getParameter('a_media_item');
        
    if ($parameters)
    {
      $files = $request->getFiles('a_media_item');
      
      $this->form = new aMediaVideoForm($item);
      
      if (isset($parameters['embed']))
      {
        // We need to do some prevalidation of the embed code so we can prestuff the
        // file, title, tags and description widgets
        $result = $this->form->classifyEmbed($parameters['embed']);
        if (isset($result['thumbnail']))
        {
          $thumbnail = $result['thumbnail'];
          if ((!isset($parameters['title'])) && (!isset($parameters['tags'])) && (!isset($parameters['description'])) && (!isset($parameters['credit'])))
          {
            $parameters['title'] = $result['serviceInfo']['title'];
            // We want tags to be lower case, and slashes break routes in most server configs. 
            $parameters['tags'] = str_replace('/', '-', aString::strtolower($result['serviceInfo']['tags']));
            $parameters['description'] = aHtml::textToHtml($result['serviceInfo']['description']);
            $parameters['credit'] = $result['serviceInfo']['credit'];
          }
        }
      }

      // On the first pass with a youtube video we just make the service's thumbnail the
      // default thumbnail. We don't force them to use it. This allows more code reuse
      // (Moving this after the bind is necessary to keep it from being overwritten) 
      if (isset($thumbnail))
      {
        // OMG file widgets can't have defaults! Ah, but our persistent file widget can
        $tmpFile = aFiles::getTemporaryFilename();
        file_put_contents($tmpFile, file_get_contents($thumbnail));

        $mimeTypes = aMediaTools::getOption('mime_types');
        // It comes back as a mapping of extensions to types, get the types
        $extensions = array_keys($mimeTypes);
        $mimeTypes = array_values($mimeTypes);
        
        $vfp = new aValidatorFilePersistent(
          array('mime_types' => $mimeTypes,
            'validated_file_class' => 'aValidatedFile',
            'required' => false),
          array('mime_types' => 'The following file types are accepted: ' . implode(', ', $extensions)));

        $guid = aGuid::generate();
        $vfp->clean(
          array(
            'newfile' => 
              array('tmp_name' => $tmpFile), 
            'persistid' => $guid));
        // You can't mess about with widget defaults after a bind, but you
        // *can* tweak the array you're about to bind with
        $parameters['file']['persistid'] = $guid;
      }
      
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
        $thumbnail = $this->form->getValue('file');
        // The base implementation for saving files gets confused when 
        // $file is not set, a situation that our code tolerates as useful 
        // because if you're updating a record containing an image you 
        // often don't need to submit a new one.
        unset($this->form['file']);
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
        
        if (aMediaTools::isSelecting())
        {
          return $this->redirect('aMedia/multipleAdd?id=' . $object->id);
        }

        return $this->redirect("aMedia/resumeWithPage");
      } while (false);
    }
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   */
  public function executeUpload(sfWebRequest $request)
  {  
    // Belongs at the beginning, not the end
    $this->forward404Unless(aMediaTools::userHasUploadPrivilege());

    $this->embedAllowed = aMediaTools::getEmbedAllowed();
    $this->uploadAllowed = aMediaTools::getUploadAllowed();
    
    // This has been simplified. We no longer do real validation in the first pass,
    // we just make sure there is at least one file. Then the validation of the annotation
    // pass can take over to minimize duplicate code
    $this->form = new aMediaUploadMultipleForm();
    $this->mustUploadSomething = false;
    $this->postMaxSizeExceeded = false;
    if (isset($_FILES['a_media_items']['error']['item-0']['file']['newfile']) && $_FILES['a_media_items']['error']['item-0']['file']['newfile'])
    {
      // upload_max_size exceeded
      $this->getUser()->setFlash('aMedia.postMaxSizeExceeded', true);
      $this->postMaxSizeExceeded = true;
    }
    // An empty POST is an anomaly indicating that we hit the php.ini max_post_size or similar
    if ($request->isMethod('post') && (!count($request->getPostParameters())))
    {
      $this->getUser()->setFlash('aMedia.postMaxSizeExceeded', true);
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
          /**
           * Look for a duplicate of this file and silently reuse it - almost silently:
           * repopulate the form with the previous choices
           */
          if (sfConfig::get('app_aMedia_reuse_duplicates'))
          {
            $md5 = @md5_file($file['newfile']['tmp_name']);
            if ($md5 !== false)
            {
              $existing = Doctrine::getTable('aMediaItem')->findDuplicateWithSameOwner($md5);
              if ($existing)
              {
                /**
                 * Restore the 'file' key so we don't lose the new upload, which is still our
                 * guide to whether we're really reusing something come the next pass. Fix the category
                 * and tags keys directly, toArray doesn't handle those
                 */
                $info = $existing->toArray();
                $itemFile = $items["item-$i"]['file'];
                $items["item-$i"] = $info;
                $items["item-$i"]['file'] = $itemFile;
                foreach ($existing->getCategories() as $category)
                {
                  $items["item-$i"]["categories_list"][] = $category->id;
                }
                $items["item-$i"]["tags"] = implode(',', $existing->getTags());
                $good = true;
                $count++;
                error_log(json_encode($items["item-$i"]));
              }
            }
          }
          if ((!isset($items["item-$i"]['title'])) && isset($file['newfile']['tmp_name']) && strlen($file['newfile']['tmp_name']))
          {
            // Humanize the original filename
            $title = aMediaTools::filenameToTitle($file['newfile']['name']);
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
      } else
      {
        $this->getUser()->setFlash('aMedia.mustUploadSomething', true);
        $this->mustUploadSomething = true;
      }
    }
    // For errors we set some flash attributes and return to the index page
    // We use forward() because resume redirects - if we redirect twice
    // we'll lose the flash attributes telling us about the errors
    $this->forward('aMedia', 'resume');
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeEditMultiple(sfWebRequest $request)
  {
    $this->forward404Unless(aMediaTools::userHasUploadPrivilege());
    $this->embedAllowed = aMediaTools::getEmbedAllowed();
    $this->uploadAllowed = aMediaTools::getUploadAllowed();  

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
    $this->totalItems = count($active);

    $this->form = new aMediaEditMultipleForm($active);
    $this->form->bind(
      $request->getParameter('a_media_items'),
      $request->getFiles('a_media_items'));

    $this->popularTags = PluginTagTable::getPopulars(null, array('sort_by_popularity' => true), false, 10);
    if (sfConfig::get('app_a_all_tags', true))
    {
      $this->allTags = PluginTagTable::getAllTagNameWithCount();
    }
    else
    {
      $this->allTags = array();
    }

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
      $added = array();
      foreach ($this->form->getEmbeddedForms() as $key => $itemForm)
      {
        $file = $values[$key]['file'];

        /**
         * Look for a duplicate of this file and silently reuse it
         */
        if (sfConfig::get('app_aMedia_reuse_duplicates'))
        {
          $md5 = @md5_file($file->getTempName());
          if ($md5 !== false)
          {
            $existing = Doctrine::getTable('aMediaItem')->findDuplicateWithSameOwner($md5);
            if ($existing)
            {
              $itemForm->setObject($existing);
            }
          }
        }

        // Called from doSave in the embedded form, these will never be called here if we don't call them ourselves.
        // Modifies $values[$key] by reference
        $itemForm->updateCategoriesList($values[$key]);
        $itemForm->updateObject($values[$key]);
        
        $object = $itemForm->getObject();
        if ($object->getId() && ($object->getOwnerId() !== $this->getUser()->getGuardUser()->getId()))
        {
          // We might be reusing a media item, so we want to preserve the id, but
          // only if the existing media item belongs to us - a guard against attacks
          $this->forward404();
        }

        // updateObject doesn't handle one-to-many relations, only save() does, and we
        // can't do save() in this embedded form, so we need to implement the categories
        // relation ourselves        
        $object->unlink('Categories');
        $object->link('Categories', $values[$key]['categories_list']);

        // Everything except the actual copy which can't succeed
        // until the slug is cast in stone
        $object->preSaveFile($file);
        $object->save();
        $object->saveFile($file);
        $added[] = $object;
      }
      $selection = aMediaTools::getSelection();
      $kept = array();
      foreach ($added as $object)
      {
        // Listing the same item twice doesn't really work in various places,
        // so don't tempt fate
        $id = $object['id'];
        if (!in_array($id, $selection))
        {
          if (!aMediaTools::isMultiple())
          {
            // We wind up with the last one, if they upload more than one 
            // during a single-image select operation (why would they do that?)
            $selection = array($object['id']);
            $kept = array($object);
          }
          else
          {
            $selection[] = $object['id'];
            $kept[] = $object;
          }
        }
      }
      aMediaTools::setSelection($selection);
      $croppableCount = 0;
      foreach ($kept as $object)
      {
        if ($object->getCroppable())
        {
          $croppableCount++;
          aMediaTools::setDefaultCropDimensions($object);
        } 
      }
      // If the item we kept is not croppable and we're performing a single select,
      // finish that operation. If it is croppable we potentially need to hang around and
      // let them crop, don't jump the gun. There's more I could do here 

      // Checking whether the type is limited to images to determine whether it's not OK to 
      // shortcut select is a hack, but it's the same hack we already use to determine whether 
      // to show the cropper.
      $type = aMediaTools::getType();
      if (count($kept) && ($type !== 'image') && aMediaTools::isSelecting() && (!aMediaTools::isMultiple()))
      {
        $after = aMediaTools::getAfter();
        aMediaTools::clearSelecting();
        $after = aUrl::addParams($after,
            array("aMediaId" => $kept[0]['id']));
        return $this->redirect($after);
      }
      return $this->redirect('aMedia/resume');
    }
  }

  /**
   * DOCUMENT ME
   */
  public function executeEmbed()
  {
    $this->embedAllowed = aMediaTools::getEmbedAllowed();
    $this->uploadAllowed = aMediaTools::getUploadAllowed();  

    // It's a really simple form
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
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

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function executeShow()
  {
    $this->embedAllowed = aMediaTools::getEmbedAllowed();
    $this->uploadAllowed = aMediaTools::getUploadAllowed();  
    $this->mediaItem = $this->getItem();
    $this->layout = aMediaTools::getLayout($this->getUser()->getAttribute('layout', 'two-up', 'apostrophe_media'));
    // This sets the gallery image dimensions to the correct dimensions for showSuccess
    // Doing this here seemed like a good way to keep the templates cleaner
    $this->layout['showSuccess'] = true;
    $this->layout['gallery_constraints'] = $this->layout['show_constraints'];

    return $this->pageTemplate;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function executeMeta()
  {
    $mediaItem = $this->getItem();
    $layout = aMediaTools::getLayout($this->getUser()->getAttribute('layout', 'two-up', 'apostrophe_media'));

    return $this->renderPartial('aMedia/mediaItemMeta', array('layout' => $layout, 'mediaItem' => $mediaItem));
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  private function getItem()
  {
    return aMediaTools::getItem($this);
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeRefreshItem(sfWebRequest $request)
  {
    $item = $this->getItem();
    return $this->renderPartial('aMedia/mediaItem',
      array('mediaItem' => $item));
  }

  /**
   * DOCUMENT ME
   * @param sfRequest $request
   */
  public function executeSearchServices(sfRequest $request)
  {
    $this->hasPermissionsForSelect();
    
    $this->form = new aMediaSearchServicesForm();

    $this->embedAllowed = aMediaTools::getEmbedAllowed();
    $this->uploadAllowed = aMediaTools::getUploadAllowed();

    $params = $request->getParameter('aMediaSearchServices');
    // Don't spew a validation error if it's just the initial visit to the page
    if (isset($params['q']))
    {
      $this->form->bind($params);
      if ($this->form->isValid())
      {
        $q = $this->form->getValue('q');
        $this->service = aMediaTools::getEmbedService($this->form->getValue('service'));
        if ($this->service)
        {
          $this->pager = new aEmbedServicePager();
          $this->pager->setQuery($this->service, 'search', $q, $request->getParameter('page', 1), aMediaTools::getOption('video_search_per_page', 9));
          $this->pager->init();
          $this->url = $this->getController()->genUrl('aMedia/searchServices?' . http_build_query(array('aMediaSearchServices' => $params)));
        }
      }
    }
  }

  /**
   * DOCUMENT ME
   */
  protected function setIframeLayout()
  {
    $this->setLayout(sfContext::getInstance()->getConfiguration()->getTemplateDir('aMedia', 'iframe.php') . '/iframe');
  }

  /**
   * DOCUMENT ME
   * @param sfRequest $request
   */
  public function executeNewVideo(sfRequest $request)
  {
    $this->hasPermissionsForSelect();
    
    $this->videoSearchForm = new aMediaSearchServicesForm();
    $this->service = aMediaTools::getEmbedService($request->getParameter('service'));
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function isAdmin()
  {
    $isAdmin = $this->getUser()->hasCredential(aMediaTools::getOption('admin_credential'));
    return $isAdmin;
  }

  /**
   * DOCUMENT ME
   * @param sfRequest $request
   */
  public function executeLink(sfRequest $request)
  {
    $this->hasPermissionsForSelect();
    
    $this->embedAllowed = aMediaTools::getEmbedAllowed();
    $this->uploadAllowed = aMediaTools::getUploadAllowed();
  
    $this->forward404Unless($this->isAdmin());
    $this->form = new aEmbedMediaAccountForm();
    $this->accounts = Doctrine::getTable('aEmbedMediaAccount')->createQuery('a')->orderBy('a.service ASC, a.username ASC')->execute();
  }

  /**
   * DOCUMENT ME
   * @param sfRequest $request
   * @return mixed
   */
  public function executeLinkAddAccount(sfRequest $request)
  {
    $this->forward404Unless($this->isAdmin());
    $this->form = new aEmbedMediaAccountForm();
    $params = $request->getParameter('a_embed_media_account');
    $this->form->bind($params);
    if ($this->form->isValid())
    {
      // Quietly ignore duplicates
      $existing = Doctrine::getTable('aEmbedMediaAccount')->createQuery('a')->where('a.username = ? AND a.service = ?', array($params['username'], $params['service']))->execute();
      if (!count($existing))
      {
        $this->form->save();
      }
    }
    return $this->redirect('aMedia/link');
  }

  /**
   * DOCUMENT ME
   * @param sfRequest $request
   * @return mixed
   */
  public function executeLinkRemoveAccount(sfRequest $request)
  {
    $this->forward404Unless($this->isAdmin());
    $which = $request->getParameter('id');
    $a = Doctrine::getTable('aEmbedMediaAccount')->find($which);
    if ($a)
    {
      $a->delete();
    }
    return $this->redirect('aMedia/link');
  }

  /**
   * DOCUMENT ME
   * @param sfRequest $request
   * @return mixed
   */
  public function executeLinkPreviewAccount(sfRequest $request)
  {
    $this->forward404Unless($this->isAdmin());
    $params = $request->getParameter('a_embed_media_account');
    $service = $params['service'];
    $this->username = $params['username'];
    $this->service = aMediaTools::getEmbedService($service);
    $this->forward404Unless($this->service);
    $info = $this->service->getUserInfo($this->username);
    if (!$info)
    {
      return 'Error';
    }
    $this->name = $info['name'];
    $this->description = $info['description'];
    // Grab their three newest videos
    $this->results = $this->service->browseUser($this->username, 1, 3);
  }

  /**
   * DOCUMENT ME
   */
  protected function hasPermissionsForSelect()
  {
    $this->forward404Unless(aTools::isPotentialEditor() || aMediaTools::userHasUploadPrivilege());
  }

  /**
   * DOCUMENT ME
   */
  public function executeClearSelecting()
  {
    aMediaTools::clearSelecting();
    exit(0);
  }
}
