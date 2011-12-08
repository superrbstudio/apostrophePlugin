<?php
/**
 * 
 * a components.
 * @package    apostrophe
 * @subpackage a
 * @author     P'unk Ave
 */
class BaseaComponents extends aSlotComponents
{

  /**
   * DOCUMENT ME
   * @param sfRequest $request
   */
  public function executeSubnav(sfRequest $request)
  {

  }

  /**
   * DOCUMENT ME
   */
  public function executeSlot()
  {
    $this->setup();
    $controller = $this->getController();

    // As part of the Great Renaming, slot modules got a Slot suffix,
    // which allows them to be distinguished readily from non-slot modules.
    
    $this->normalModule = $this->type . 'Slot';
    $this->editModule = $this->type . 'Slot';
  }

  /**
   * DOCUMENT ME
   */
  public function executeArea()
  {
    $this->page = aTools::getCurrentPage();
    $this->pageid = $this->page->id;
    $this->slots = $this->page->getArea($this->name, $this->addSlot, sfConfig::get('app_a_new_slots_top', true));
    $aOptions = $this->options;
		$aOptions['arrows'] = $this->getOption('arrows', true); // Option for disabling slot reorder arrows
		$aOptions['history'] = $this->getOption('history', true); // Option for disabling History on specific areas
		$aOptions['delete'] = $this->getOption('delete', false); // Option for enabling Delete on singleton slots
		$aOptions['areaClass'] = $this->getOption('areaClass', false); // Option for enabling Delete on singleton slots		
		$aOptions['areaHideWhenEmpty'] = $this->getOption('areaHideWhenEmpty', false); // Option for enabling Delete on singleton slots		
		$this->options = $aOptions;
    if (!is_null($this->getOption('edit', null)))
    {
      // Editability override, useful for virtual pages where access control depends on something
      // external to the CMS
      $this->editable = $this->getOption('edit');
    }
    else
    {
      $this->editable = $this->page->userHasPrivilege('edit');
    }
    $user = $this->getUser();
    // Clean this up for nicer templates
    $this->refresh = (isset($this->refresh) && $this->refresh);
    $this->preview = (isset($this->preview) && $this->preview);
    $id = $this->page->id;
    $name = $this->name;
    if ($this->refresh)
    {
      if ($user->hasAttribute("area-options-$id-$name", 'apostrophe'))
      {
        $this->options = $user->getAttribute("area-options-$id-$name", array(), 'apostrophe');
      }
      else
      {
        // BZZT: probably a hack attempt
        throw new sfException("executeArea without options");
      }
    }
    else
    {
      // If this area is naturally editable (we have appropriate privileges), make sure we
      // set the explicit edit option so that other components and actions can just check
      // for it rather than redundantly checking page privileges as well
      if ($this->editable)
      {
        $this->options['edit'] = true;
      }
      $user->setAttribute("area-options-$id-$name", $this->options, 'apostrophe');
    }
    $this->infinite = $this->getOption('infinite');
    if (!$this->infinite)
    {
      // Watch out for existing slots of the wrong type, which might contain data
      // that is incompatible with the singleton slot's type. That can happen if you
      // switch slot types in the template, or change from an area to a singleton slot.
      // Also ignore anything after the first slot (again, that can happen if you
      // switch from an area to a singleton slot)
      if (count($this->slots) > 1)
      {
        // Get the first one without being tripped up by the fact that it's a hash
        foreach ($this->slots as $key => $slot)
        {
          break;
        }
        $this->slots = array($key => $slot);
      }
      if (count($this->slots))
      {
        // Get the first one without being tripped up by the fact that it's a hash
        foreach ($this->slots as $key => $slot)
        {
          break;
        }
        if ($slot->type !== $this->options['type'])
        {
          $this->slots = array();
        }
      }
      if (!count($this->slots))
      {
        if (!isset($this->options['type']))
        {
          throw new sfException('Must specify type when embedding a singleton slot');
        }
				$info = $this->page->getNextPermidAndRank($name);
				$permid = $info['permid'];
        $this->slots[$permid] = $this->page->createSlot($this->options['type']);
        $this->slots[$permid]->setEditDefault(false);
      }
    }
  }

  /**
   * DOCUMENT ME
   * @param sfRequest $request
   * @return mixed
   */
  public function executeNavigation(sfRequest $request)
  {
    // What page are we starting from?
    // Navigation on non-CMS pages is relative to the home page
    if (!$this->page = aTools::getCurrentPage())
    {
      $this->page = aPageTable::retrieveBySlug('/');
    }
    if(!$this->activePage = aPageTable::retrieveBySlug($this->activeSlug))
    {
      $this->activePage = $this->page;
    }
    if(!$this->rootPage = aPageTable::retrieveBySlug($this->rootSlug))
    {
      $this->rootPage = $this->activePage;
    }

    // We build different page trees depending on the navigation type that was requested
    if (!$this->type)
    {
      $this->type = 'tree';
    }
    
    $class = 'aNavigation'.ucfirst($this->type);
    
    if (!class_exists($class))
    {
      throw new sfException(sprintf('Navigation type "%s" does not exist.', $class));
    }

    $this->navigation = new $class($this->rootPage, $this->activePage, $this->options);
        
    $this->draggable = $this->page->userHasPrivilege('edit');
    
    // Users can pass class names to the navigation <ul>
    $this->classes = '';
    if (isset($this->options['classes']))
    {
      $this->classes .= $this->options['classes'];
    }
    $this->nest = 0;
    // The type of the navigation also is used for styling
    $this->classes .= ' ' . $this->type;
    $this->navigation = $this->navigation->getItems();
    if(count($this->navigation) == 0)
    {
      return sfView::NONE;
    }
    
  }

  /**
   * 
   * Executes signinForm action
   * @param sfRequest $request A request object
   */
  public function executeSigninForm(sfWebRequest $request)
  {
    $class = sfConfig::get('app_sf_guard_plugin_signin_form', 'sfGuardFormSignin'); 
    $this->form = new $class();
  }
  
  /**
   * Outputs an area with a reasonable default list of slots. You can override
   * various aspects: the slots allowed, the width of media slots, etc. 
   *
   * You must pass 'name' to name the area. You may pass slots' => array(the exact slot names you want),
   * 'plusSlots' => array(additional slot names to add), or 'minusSlots' => array(slot names to exclude).
   * You may also pass 'width', 'height' and 'flexHeight' to specify the behavior of media slots and 
   * 'toolbar' to specify the rich text toolbar. If you want more control than that you should write a complete
   * a_area call with the exact options you want.
   *
   * We use this component to ensure consistency between templates used for different page
   * templates and the blog and events templates (where the minusSlots option is very useful to
   * prevent recursion). 
   *
   * You can override the default list of slots globally by overriding aTools::getStandardSlots.
   */
  public function executeStandardArea(sfWebRequest $request)
  {
    if (!isset($this->slots))
    {
      $this->slots = aTools::standardAreaSlots();
    }
    // array_flip is a handy way to turn a flat array into an associative array so you can set and
    // unset things and then fetch the keys to see what you wound up with
    $this->slots = array_flip($this->slots);
    if (isset($this->plusSlots))
    {
      foreach ($this->plusSlots as $slot)
      {
        $this->slots[$slot] = true;
      }
    }
    if (isset($this->minusSlots))
    {
      foreach ($this->minusSlots as $slot)
      {
        unset($this->slots[$slot]);
      }
    }
    $this->slots = array_keys($this->slots);
    $this->type_options = aTools::standardAreaSlotOptions();
    // Slots do not object to extra options, so we can simplify by applying to all
    if (isset($this->width))
    {
      foreach ($this->slots as $slot)
      {
        $this->type_options[$slot]['width'] = $this->width;
        if (sfConfig::get('app_a_standard_area_enforce_minimum_width', true))
        {
          $this->type_options[$slot]['constraints']['minimum-width'] = $this->width;
        }
        else
        {
          unset($this->type_options[$slot]['constraints']['minimum-width']);
        }
      }
    }
    if (isset($this->height))
    {
      foreach ($this->slots as $slot)
      {
        $this->type_options[$slot]['height'] = $this->height;
        if (sfConfig::get('app_a_standard_area_enforce_minimum_height', true))
        {
          $this->type_options[$slot]['constraints']['minimum-height'] = $this->height;
        }
        else
        {
          unset($this->type_options[$slot]['constraints']['minimum-height']);
        }
      }
    }
    if (isset($this->flexHeight))
    {
      foreach ($this->slots as $slot)
      {
        $this->type_options[$slot]['flexHeight'] = $this->flexHeight;
      }
    }
    if (isset($this->toolbar))
    {
      foreach ($this->slots as $slot)
      {
        $this->type_options[$slot]['toolbar'] = $this->toolbar;
      }
    }
		$this->areaOptions = (!is_null($this->areaOptions)) ? $this->areaOptions : array();
    $defaultAreaOptions = sfConfig::get('app_a_standard_area_options');
    if ($defaultAreaOptions)
    {
      $this->areaOptions = array_merge($defaultAreaOptions, $this->areaOptions);
    }
    foreach ($this->slots as $slot)
    {
      $this->type_options[$slot]['slideshowOptions'] = array(
  			'width' => $this->width,
  			'height' => false
  		);
	  }
	  $forceOptions = sfConfig::get('app_a_standard_area_force_slot_options');
	  if ($forceOptions)
	  {
	    $this->type_options = sfToolkit::arrayDeepMerge($this->type_options, $forceOptions);
	  }
	}
}
