<?php
/**
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaTools
{
  // ALL static variables must go here
  
  // We need a separate flag so that even a non-CMS page can
  // restore its state (i.e. set the page back to null)
  static protected $global = false;
  // We now allow fetching of slots from multiple pages, which can be
  // normal pages or outside-of-navigation pages like 'global' that are used
  // solely for this purpose. This allows efficient fetching of only slots that are
  // relevant to your needs, rather than fetching all 'global' slots at once
  static protected $globalCache = array();
  static protected $currentPage = null;
  static protected $pageStack = array();
  static protected $globalButtons = false;
  static protected $allowSlotEditing = true;
  static protected $realUrl = null;
  static public $jsCalls = array();
  
  static public $searchService = null;

  /**
   * Must reset ALL static variables to their initial state
   * @param sfEvent $event
   */
  static public function listenToSimulateNewRequestEvent(sfEvent $event)
  {
    aTools::$global = false;
    aTools::$globalCache = false;
    aTools::$currentPage = null;
    aTools::$pageStack = array();
    aTools::$globalButtons = false;
    aTools::$allowSlotEditing = true;
    aTools::$realUrl = null;
    aTools::$jsCalls = array();
    aNavigation::simulateNewRequest();
  }

  /**
   * DOCUMENT ME
   * @param mixed $culture
   * @return mixed
   */
  static public function cultureOrDefault($culture = false)
  {
    if ($culture)
    {
      return $culture;
    }
    return aTools::getUserCulture();
  }

  /**
   * DOCUMENT ME
   * @param mixed $user
   * @return mixed
   */
  static public function getUserCulture($user = false)
  {
    if ($user == false)
    {
      $culture = false;
      try
      {
        $context = sfContext::getInstance();
      } catch (Exception $e)
      {
        // Not present in tasks
        $context = false;
      }
      if ($context)
      {
        $user = sfContext::getInstance()->getUser();
      }
    }
    if ($user)
    {
      $culture = $user->getCulture();
    }
    if (!$culture)
    {
      $culture = sfConfig::get('sf_default_culture', 'en');
    }
    return $culture;
  }

  /**
   * DOCUMENT ME
   * @param mixed $slug
   * @param mixed $absolute
   * @return mixed
   */
  static public function urlForPage($slug, $absolute = true)
  {
    // sfSimpleCMS found a nice workaround for this
    // By using @a_page we can skip to a shorter URL form
    // and not get tripped up by the default routing rule which could
    // match first if we wrote a/show 
    $routed_url = sfContext::getInstance()->getController()->genUrl('@a_page?slug=-PLACEHOLDER-', $absolute);
    $routed_url = str_replace('-PLACEHOLDER-', $slug, $routed_url);
    // We tend to get double slashes because slugs begin with slashes
    // and the routing engine wants to helpfully add one too. Fix that,
    // but don't break http://
    $matches = array();
    // This is good both for dev controllers and for absolute URLs
    $routed_url = preg_replace('/([^:])\/\//', '$1/', $routed_url);
    // For non-absolute URLs without a controller
    if (!$absolute) 
    {
      $routed_url = preg_replace('/^\/\//', '/', $routed_url);
    }
    return $routed_url;
  }

  /**
   * DOCUMENT ME
   * @param mixed $page
   */
  static public function setCurrentPage($page)
  {
    aTools::$currentPage = $page;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function getCurrentPage()
  {
    return aTools::$currentPage;
  }

  /**
   * Similar to getCurrentPage, but returns null if the current page is an admin page,
   * and therefore not suitable for normal navigation like the breadcrumb and subnav
   * @return mixed
   */
  static public function getCurrentNonAdminPage()
  {
    $page = aTools::getCurrentPage();
    return $page ? ($page->admin ? null : $page) : null;
  }

  /**
   * 
   * We've fetched a page on our own using aPageTable::queryWithSlots and we want
   * to make Apostrophe aware of it so that areas on the current page that live on
   * that virtual page don't generate a superfluous second query. Can accept an array,
   * a collection or a single page object. Hydrates pages if needed (app_a_fasthydrate).
   * Returns an array of page objects for convenience in mapping them back to 
   * other objects they are associated with (necessary to leverage app_a_fasthydrate
   * in code like our blog plugin that associates a page with an object).
   *
   * @param array, Doctrine_Collection, aPage $pages
   */
  static public function cacheVirtualPages($pages)
  {
    $results = array();
    if ((is_object($pages) && ($pages instanceOf Doctrine_Collection)) || is_array($pages))
    {
      foreach($pages as $page)
      {
        $results[] = aTools::cacheVirtualPage($page);
      }
    }
    else
    {
      $results[] = aTools::cacheVirtualPage($pages);
    }
    return $results;
  }

  /**
   * 
   * Caches a page for the current request so that other invocations of a_slot, a_area,
   * etc. can efficiently access it without another query. Also hydrates the page if the
   * data passed in is from an array-hydrated query (part of app_a_fasthydrate). Returns the
   * page object, particularly useful if you need to map it back to an object it is 
   * associated with as part of fast hydration
   */
  static public function cacheVirtualPage($pageInfo)
  {
    if (!is_object($pageInfo))
    {
      // Fast hydration
      $page = new aPage();
      $page->hydrate($pageInfo);
      // Cheap hydration of the slots happens here
      $page->populateSlotCache($pageInfo['Areas']);
    }
    else
    {
      $page = $pageInfo;
    }
    aTools::$globalCache[$page['slug']] = $page;
    return $page;
  }
  
  /**
   * DOCUMENT ME
   * @param mixed $options
   */
  static public function globalSetup($options)
  {
    if (isset($options['global']) && $options['global'])
    {
      if (!isset($options['slug']))
      {
        $options['slug'] = 'global';
      }
    }
    if (isset($options['slug']))
    {
      // Note that we push onto the stack even if the page specified is the same page
      // we're looking at. This doesn't hurt because of caching, and it allows us
      // to keep the stack count properly
      $slug = $options['slug'];
      aTools::$pageStack[] = aTools::getCurrentPage();
      // Caching the global page speeds up pages with two or more global slots
      if (isset(aTools::$globalCache[$slug]))
      {
        $global = aTools::$globalCache[$slug];
      }
      else
      {        
        $global = aPageTable::retrieveBySlugWithSlots($slug);
        if (!$global)
        {
          $global = new aPage();
          $global->slug = $slug;
          $global->save();
        }
        aTools::$globalCache[$slug] = $global;
      }
      aTools::setCurrentPage($global);
      aTools::$global = true;
    }
  }
  
  /**
   * Access to pages cached with cacheVirtualPages and cacheVirtualPage and
   * implicitly cached by earlier slot insertions for a virtual page
   * @param string $slug
   * @return boolean
   */
  static public function isPageCached($slug)
  {
    return isset(aTools::$globalCache[$slug]);
  }
  
  /**
   * Access to pages cached with cacheVirtualPages and cacheVirtualPage and
   * implicitly cached by earlier slot insertions for a virtual page
   * @param string $slug
   * @return aPage
   */
  static public function getCachedPage($slug)
  {
    if (aTools::isPageCached($slug))
    {
      return aTools::$globalCache[$slug];
    }
    else
    {
      return null;
    }
  }

  /**
   * DOCUMENT ME
   */
  static public function globalShutdown()
  {
    if (aTools::$global)
    {
      aTools::setCurrentPage(array_pop(aTools::$pageStack));
      aTools::$global = (count(aTools::$pageStack));
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $groupName
   * @return mixed
   */
  static public function getSlotOptionsGroup($groupName)
  {
    $optionGroups = sfConfig::get('app_a_slot_option_groups', array());
    if (isset($optionGroups[$groupName]))
    {
      return $optionGroups[$groupName];
    }
    throw new sfException("Option group $groupName is not defined in app.yml");
  }

  /**
   * Oops: we can't cache this list because it's different for various areas on the same page.
   * @param mixed $options
   * @return mixed
   */
  static public function getSlotTypesInfo($options)
  {
    $instance = sfContext::getInstance();
    $slotTypes = array_merge(
      array(
         'aText' => 'Plain Text',
         'aRichText' => 'Rich Text',
         'aFeed' => 'Twitter &amp; RSS',
         'aSlideshow' => 'Photo Slideshow',
         'aSmartSlideshow' => 'Smart Slideshow',
         'aButton' => 'Button',
         'aAudio' => 'Audio',
         'aVideo' => 'Video',
         'aFile' => 'File',
         'aRawHTML' => 'Raw HTML'),
      sfConfig::get('app_a_slot_types', array()));
    if (isset($options['allowed_types']))
    {
      $newSlotTypes = array();
      foreach($options['allowed_types'] as $type)
      {
        if (isset($slotTypes[$type]))
        {
          $newSlotTypes[$type] = $slotTypes[$type];
        }
      }
      $slotTypes = $newSlotTypes;
    }
    $info = array();
    
    foreach ($slotTypes as $type => $label)
    {
      $info[$type]['label'] = $label;
      // We COULD cache this. Would it pay to do so?
      $info[$type]['class'] = strtolower(preg_replace('/^a(\w)/', 'a-$1', $type));
    }
    return $info;
  }

  /**
   * Includes classes for buttons for adding each slot type
   * @param mixed $options
   */
  static public function getSlotTypeOptionsAndClasses($options)
  {
    
  }

  /**
   * DOCUMENT ME
   * @param mixed $array
   * @param mixed $name
   * @param mixed $default
   * @return mixed
   */
  static public function getOption($array, $name, $default)
  {
    if (isset($array[$name]))
    {
      return $array[$name];
    }
    return $default;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function getRealPage()
  {
    if (count(aTools::$pageStack))
    {
      $page = aTools::$pageStack[0];
      if ($page)
      {
        return $page;
      }
      else
      {
        return false;
      }
    }
    elseif (aTools::$currentPage)
    {
      return aTools::$currentPage;
    }
    else
    {
      return false;
    }
  }

  /**
   * Fetch options array saved in session
   * @param mixed $pageid
   * @param mixed $name
   * @return mixed
   */
  static public function getAreaOptions($pageid, $name)
  {
    $lookingFor = "area-options-$pageid-$name";
    $options = array();
    $user = sfContext::getInstance()->getUser();
    if ($user->hasAttribute($lookingFor, 'apostrophe'))
    {
      $options = $user->getAttribute(
        $lookingFor, false, 'apostrophe');
    }
    return $options;
  }

  /**
   * Get template choices in the new format, then provide bc with the old format
   * (one level with no engines specified), and also add entries for any engines
   * listed in the old way that don't have templates specified in the new way
   * @return mixed
   */
  static public function getTemplates()
  {
    if (sfConfig::get('app_a_get_templates_method'))
    {
      $method = sfConfig::get('app_a_get_templates_method');

      return call_user_func($method);
    }
    $templates = sfConfig::get('app_a_templates', array(
      'a' => array(
        'default' => 'Default Page',
        'home' => 'Home Page')));
    // Provide bc 
    $newTemplates = $templates;
    foreach ($templates as $key => $value)
    {
      if (!is_array($value))
      {
        $newTemplates['a'][$key] = $value;
        unset($newTemplates[$key]);
      }
    }
    $templates = $newTemplates;
    $engines = aTools::getEngines();
    foreach ($engines as $name => $label)
    {
      if (!strlen($name))
      {
        // Ignore the "template-based" engine option
        continue;
      }
      if (!isset($templates[$name]))
      {
        $templates[$name] = array('default' => $label);
      }
    }
    return $templates;
  }

  /**
   * Flat name => label array for use in select elements
   * @return mixed
   */
  static public function getTemplateChoices()
  {
    $templates = aTools::getTemplates();
    $choices = array();
    foreach ($templates as $engine => $etemplates)
    {
      foreach ($etemplates as $name => $label)
      {
        $choices["$engine:$name"] = $label;
      }
    }
    return $choices;
  }

  /**
   * Used to provide bc with the old app_a_engines way of listing engine choices
   * @return mixed
   */
  static public function getEngines()
  {
    if (sfConfig::get('app_a_get_engines_method'))
    {
      $method = sfConfig::get('app_a_get_engines_method');

      return call_user_func($method);
    }
    return sfConfig::get('app_a_engines', array(
      '' => 'Template-Based'));
  }

  /**
   * Fetch an internationalized option from app.yml. Example:
   * all:
   * a:
   * @param mixed $option
   * @param mixed $default
   * @param mixed $culture
   * @return mixed
   */
  static public function getOptionI18n($option, $default = false, $culture = false)
  {
    $culture = aTools::cultureOrDefault($culture);
    $values = sfConfig::get('app_a_'.$option, array());
    if (!is_array($values))
    {
      // Convenience for single-language sites
      return $values;
    }
    if (isset($values[$culture]))
    {
      return $values[$culture];  
    } 
    return $default; 
  }

  /**
   * DOCUMENT ME
   * @param sfEvent $event
   */
  static public function getGlobalButtonsInternal(sfEvent $event)
  {
    // If we needed a context object we could get it from $event->getSubject(),
    // but this is a simple static thing
    
    // Add the users button only if the user has the admin credential.
    // This is typically only given to admins and superadmins.
    $user = sfContext::getInstance()->getUser();
    if ($user->hasCredential('admin'))
    {
      $extraAdminButtons = sfConfig::get('app_a_extra_admin_buttons', 
        array('users' => array('label' => 'Users', 'action' => 'aUserAdmin/index', 'class' => 'a-users'),
          'categories' => array('label' => 'Categories', 'action' => 'aCategoryAdmin/index', 'class' => 'a-categories'),
          'tags' => array('label' => 'Tags', 'action' => 'aTagAdmin/index', 'class' => 'a-tags'),
          'reorganize' => array('label' => 'Reorganize', 'action' => 'a/reorganize', 'class' => 'a-reorganize')        
        ));
      if (is_array($extraAdminButtons))
      {
        foreach ($extraAdminButtons as $name => $data)
        {
          aTools::addGlobalButtons(array(new aGlobalButton(
            $name, $data['label'], $data['action'], isset($data['class']) ? $data['class'] : '')));
        }
      }
    }
  }

  /**
   * To be called only in response to a a.getGlobalButtons event
   * @param mixed $array
   */
  static public function addGlobalButtons($array)
  {
    foreach ($array as $button)
    {
      aTools::$globalButtons[$button->getName()] = $button;
    }
  }

  /**
   * Returns global buttons as a flat array, either in alpha order or, if app_a_global_button_order is
   * specified, in that order. This is used to implement the default behavior. However see also
   * aTools::getGlobalButtonsByName() which is much nicer if you want to aggressively customize
   * the admin bar
   * @return mixed
   */
  static public function getGlobalButtons()
  {
    $buttonsByName = aTools::getGlobalButtonsByName();
    $buttonsOrder = sfConfig::get('app_a_global_button_order', false);
    if ($buttonsOrder === false)
    {
      ksort($buttonsByName);
      $orderedButtons = array_values($buttonsByName);
    }
    else
    {
      $orderedButtons = array();
      foreach ($buttonsOrder as $name)
      {
        if (isset($buttonsByName[$name]))
        {
          $orderedButtons[] = $buttonsByName[$name];
        }
      }
    }
    
    return $orderedButtons;
  }

  /**
   * Returns global buttons as an associative array by button name.
   * Ignores app_a_global_button_order. For use by those who prefer to
   * override the _globalTools partial. Note that you will NOT get the
   * same buttons for every user! An admin has more buttons than a
   * mere editor and so on. Use isset()
   * @return mixed
   */
  static public function getGlobalButtonsByName()
  {
    if (aTools::$globalButtons === false)
    {
      aTools::$globalButtons = array();
      // We could pass parameters here but it's a simple static thing in this case 
      // so the recipients just call back to addGlobalButtons
      sfContext::getInstance()->getEventDispatcher()->notify(new sfEvent(null, 'a.getGlobalButtons', array()));
    }
    $labelOverrides = sfConfig::get('app_a_global_button_labels', null);
    if (is_array($labelOverrides))
    {
      foreach ($labelOverrides as $key => $label)
      {
        if (isset(aTools::$globalButtons[$key]))
        {
          if (is_array($label))
          {
            // i18n
            aTools::$globalButtons[$key]->setLabel($label[aTools::getUserCulture()]);
          }
          else
          {
            aTools::$globalButtons[$key]->setLabel($label);
          }
        }
      }
    }
    return aTools::$globalButtons;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function globalToolsPrivilege()
  {
    // if you can edit the page, there are tools for you in the apostrophe
    if (aTools::getCurrentPage() && aTools::getCurrentPage()->userHasPrivilege('edit'))
    {
      return true;
    }
    // if you are the site admin, there are ALWAYS tools for you in the apostrophe
    $user = sfContext::getInstance()->getUser();
    return $user->hasCredential('cms_admin');
  }

  /**
   * These methods allow slot editing to be turned off even for people with
   * full and appropriate privileges.
   * Most of the time being able to edit a global slot on a non-CMS page is a
   * good thing, especially if that's the only place the global slot appears.
   * But sometimes, as in the case where you're editing other types of data,
   * it's just a source of confusion to have those buttons displayed.
   * (Suppressing editing of slots on normal CMS pages is of course a bad idea,
   * because how else would you ever edit them?)
   * @param mixed $value
   */
  static public function setAllowSlotEditing($value)
  {
    aTools::$allowSlotEditing = $value;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function getAllowSlotEditing()
  {
    return aTools::$allowSlotEditing;
  }

  /**
   * Kick the user out to appropriate places if they don't have the proper
   * privileges to be here. a::executeShow and aEngineActions::preExecute
   * both use this
   * @param sfAction $action
   * @param mixed $page
   * @return mixed
   */
  static public function validatePageAccess(sfAction $action, $page)
  {
    $action->forward404Unless($page);
    if (!$page->userHasPrivilege('view'))
    {
      // forward rather than login because referrers don't always
      // work. Hopefully the login action will capture the original
      // URI to bring the user back here afterwards.

      if ($action->getUser()->isAuthenticated())
      {
        return $action->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));
      }
      else
      {
        return $action->forward(sfConfig::get('sf_login_module'), sfConfig::get('sf_login_action'));

      }
    }
    if ($page->archived && (!$page->userHasPrivilege('edit')))
    {
      $action->forward404();
    }    
  }

  /**
   * Establish the page title, set the layout, and add the javascripts that are
   * necessary to manage pages. a::executeShow and aEngineActions::preExecute
   * both use this. TODO: is this redundant now that aHelper does it?
   * @param sfAction $action
   * @param aPage $page
   */
  static public function setPageEnvironment(sfAction $action, aPage $page)
  {
    // Title is pre-escaped as valid HTML
    $prefix = aTools::getOptionI18n('title_prefix');
    $suffix = aTools::getOptionI18n('title_suffix');
    $action->getResponse()->setTitle($prefix . $page->getTitle() . $suffix, false);
    // Necessary to allow the use of
    // aTools::getCurrentPage() in the layout.
    // In Symfony 1.1+, you can't see $action->page from
    // the layout.
    aTools::setCurrentPage($page);
    // Borrowed from sfSimpleCMS
    if(sfConfig::get('app_a_use_bundled_layout', true))
    {
      $action->setLayout(sfContext::getInstance()->getConfiguration()->getTemplateDir('a', 'layout.php').'/layout');
    }

    // Loading the a helper at this point guarantees not only
    // helper functions but also necessary JavaScript and CSS
    sfContext::getInstance()->getConfiguration()->loadHelpers('a');     
  }

  /**
   * DOCUMENT ME
   * @param mixed $page
   * @param mixed $info
   * @return mixed
   */
  static public function pageIsDescendantOfInfo($page, $info)
  {
    return ($page->lft > $info['lft']) && ($page->rgt < $info['rgt']);
  }

  /**
   * Same rules found in aPage::userHasPrivilege(), but without checking for
   * a particular page, so we return true even for users who are just *potential* editors
   * when granted privileges at an appropriate point in the page tree. This is useful for
   * deciding whether the tabs control should show archived pages or not. (Showing those to
   * a few editors who can't edit them is not a major problem, and checking the privs on
   * each and every one is an unacceptable performance hit)
   * @param mixed $user
   * @return mixed
   */
  static public function isPotentialEditor($user = false)
  {
    if ($user === false)
    {
      $user = sfContext::getInstance()->getUser();
    }
    // Rule 1: admin can do anything
    // Work around a bug in some releases of sfDoctrineGuard: users sometimes
    // still have credentials even though they are not logged in
    if ($user->isAuthenticated() && $user->hasCredential('cms_admin'))
    {
      return true;
    }

    // The editor permission, which (like the editor group) makes you a candidate to edit
    // if actually granted that privilege somewhere in the tree (via membership in a group
    // that has the editor permission), is generally received from a group. In older installs the 
    // editor group itself won't have it, so we still check by other means (see below). 
    if ($user->isAuthenticated() && $user->hasCredential(sfConfig::get('app_a_group_editor_permission', 'editor')))
    {
      return true;
    }
    
    $sufficientCredentials = sfConfig::get("app_a_edit_sufficient_credentials", false);
    $sufficientGroup = sfConfig::get("app_a_edit_sufficient_group", false);
    $candidateGroup = sfConfig::get("app_a_edit_candidate_group", false);
    // By default users must log in to do anything except view
    $loginRequired = sfConfig::get("app_a_edit_login_required", true);
    
    if ($loginRequired)
    {
      if (!$user->isAuthenticated())
      {
        return false;
      }
      // Rule 3: if there are no sufficient credentials and there is no
      // required or sufficient group, then login alone is sufficient. Common 
      // on sites with one admin
      if (($sufficientCredentials === false) && ($candidateGroup === false) && ($sufficientGroup === false))
      {
        // Logging in is the only requirement
        return true; 
      }
      // Rule 4: if the user has sufficient credentials... that's sufficient!
      // Many sites will want to simply say 'editors can edit everything' etc
      if ($sufficientCredentials && 
        ($user->hasCredential($sufficientCredentials)))
      {
        
        return true;
      }
      if ($sufficientGroup && 
        ($user->hasGroup($sufficientGroup)))
      {
        return true;
      }

      // Rule 5: if there is a candidate group, make sure the user is a member
      if ($candidateGroup && 
        (!$user->hasGroup($candidateGroup)))
      {
        return false;
      }
      return true;
    }
    else
    {
      // No login required
      return true;
    }      
  }

  /**
   * DOCUMENT ME
   * @param mixed $type
   * @param mixed $options
   * @return mixed
   */
  static public function getVariantsForSlotType($type, $options = array())
  {
    // 1. By default, all variants of the slot are allowed.
    // 2. If app_a_allowed_variants is set and a specific list of allowed variants
    // is provided for this slot type, those variants are allowed.
    // 3. If app_a_allowed_variants is set and a specific list is not present for this slot type,
    // no variants are allowed for this slot type.
    // 4. An allowed_variants option in an a_slot or a_area call overrides all of the above.
    
    // This makes it easy to define lots of variants, then disable them by default for 
    // templates that don't explicitly enable them. This is useful because variants are often
    // specific to the dimensions or other particulars of a particular template

    if (sfConfig::has('app_a_allowed_slot_variants'))
    {
      $allowedVariantsAll = sfConfig::get('app_a_allowed_slot_variants', array());
      $allowedVariants = array();
      if (isset($allowedVariantsAll[$type]))
      {
        $allowedVariants = $allowedVariantsAll[$type];
      }
    }
    if (isset($options['allowed_variants']))
    {
      $allowedVariants = $options['allowed_variants'];
    }
    
    $variants = sfConfig::get('app_a_slot_variants');
    if (!is_array($variants))
    {
      return array();
    }
    if (!isset($variants[$type]))
    {
      return array();
    }
    $variants = $variants[$type];
    if (isset($allowedVariants))
    {
      // Don't call array_flip since we seem to have decorated values coming in ):
      // (TODO: find that and make it stop)
      $allowed = array();
      foreach ($allowedVariants as $name)
      {
        $allowed[$name] = true;
      }
      $keep = array();
      foreach ($variants as $name => $value)
      {
        if (isset($allowed[$name]))
        {
          $keep[$name] = $value;
        }
      }
      $variants = $keep;
    }
    return $variants;
  }

  /**
   * DOCUMENT ME
   */
  static protected function i18nDummy()
  {
    __('Reorganize', null, 'apostrophe');
    __('Users', null, 'apostrophe');
    __('Plain Text', null, 'apostrophe');
    __('Rich Text', null, 'apostrophe');
    __('RSS Feed', null, 'apostrophe');
    __('Image', null, 'apostrophe');
    __('Slideshow', null, 'apostrophe');
    __('Button', null, 'apostrophe');
    __('Video', null, 'apostrophe');
    __('PDF', null, 'apostrophe');
    __('Raw HTML', null, 'apostrophe');    
    __('Template-Based', null, 'apostrophe');
    __('Users', null, 'apostrophe');
    __('Reorganize', null, 'apostrophe');
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function getRealUrl()
  {
    if (isset(aTools::$realUrl))
    {
      return aTools::$realUrl;
    }
    return sfContext::getInstance()->getRequest()->getUri();
  }

  /**
   * DOCUMENT ME
   * @param mixed $url
   */
  static public function setRealUrl($url)
  {
    aTools::$realUrl = $url;
  }

  /**
   * Returns a regexp fragment that matches a valid slug in a UTF8-aware way.
   * Does not reject slugs with consecutive dashes or slashes. DOES accept the %
   * sign because URLs generated by url_for arrive with the UTF8 characters
   * %-encoded. You should anchor it with ^ and $ if your goal is to match one slug as the whole string
   * @param mixed $allowSlashes
   * @return mixed
   */
  static public function getSlugRegexpFragment($allowSlashes = false)
  {
    // Looks like the 'u' modifier is purely for allowing UTF8 in the pattern *itself*. So we
    // shouldn't need it to achieve 
    if (function_exists('mb_strtolower'))
    {
      // UTF-8 capable replacement for \W. Works fine for English and also for Greek, etc.
      // ALlow % as well to work with preescaped UTF8, which is common in URLs
      $alnum = '\p{L}\p{N}_%';
      $modifier = '';
    }
    else
    {
      $alnum = '\w';
      $modifier = '';
    }
    if ($allowSlashes)
    {
      $alnum .= '\/';
    }
    $regexp = "[$alnum\-]+";
    return $regexp;
  }

  /**
   * UTF-8 where available. If your UTF-8 gets munged make sure your PHP has the
   * mbstring extension. allowSlashes will allow / but will reduce duplicate / and
   * remove any / at the end. Everything that isn't a letter or a number
   * (or a slash, when allowed) is converted to a -. Consecutive -'s are reduced and leading and
   * trailing -'s are removed
   * $betweenWords must not contain characters that have special meaning in a regexp.
   * Usually it is - (the default) or ' '
   * @param mixed $path
   * @param mixed $allowSlashes
   * @param mixed $allowUnderscores
   * @param mixed $betweenWords
   * @return mixed
   */
  static public function slugify($path, $allowSlashes = false, $allowUnderscores = true, $betweenWords = '-')
  {
    // This is the inverse of the method above
    if (function_exists('mb_strtolower'))
    {
      // UTF-8 capable replacement for \W. Works fine for English and also for Greek, etc.
      // ... Except when PCRE is built without unicode properties and PHP can't tell! We'll
      // put that in servercheck.php
      $alnum = '\p{L}\p{N}' . ($allowUnderscores ? '_' : '');
      $modifier = 'u';
    }
    else
    {
      $alnum = $allowUnderscores ? '\w' : '[A-Za-z0-9]';
      $modifier = '';
    }
    if ($allowSlashes)
    {
      $alnum .= '\/';
    }
    // Removing - here expands flexibility and shouldn't hurt because it's the replacement anyway
    $regexp = "/[^$alnum]+/$modifier";
    $path = aString::strtolower(preg_replace($regexp, $betweenWords, $path));  
    if ($allowSlashes)
    {
      // No multiple consecutive /
      $path = preg_replace("/\/+/$modifier", "/", $path);
      // No trailing / unless it's the homepage
      if ($path !== '/')
      {
        $path = preg_replace("/\/$/$modifier", '', $path);
      }
    }
    // No consecutive dashes
    $path = preg_replace("/$betweenWords+/$modifier", $betweenWords, $path);
    // Leading and trailing dashes are silly. This has the effect of trim()
    // among other sensible things
    $path = preg_replace("/^-*(.*?)-*$/$modifier", '$1', $path);     
    return $path;
  }

  // MUST BE KEPT UP TO DATE
  static protected $cssByName = array(
    'reset' => '/apostrophePlugin/css/a-reset.css',
    'forms' => '/apostrophePlugin/css/a-forms.css',
    'buttons' => '/apostrophePlugin/css/a-buttons.css',
    'components' => '/apostrophePlugin/css/a-components.css',
    'area-slots' => '/apostrophePlugin/css/a-area-slots.css',
    'engines' => '/apostrophePlugin/css/a-engines.css',
    'admin' => '/apostrophePlugin/css/a-admin.css',
    'colors' => '/apostrophePlugin/css/a-colors.css',
    'utility' => '/apostrophePlugin/css/a-utility.css',
    'jquery-ui' => '/apostrophePlugin/css/ui-apostrophe/jquery-ui.css'
  );

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function addStylesheetsIfDesired()
  {
    if (!sfConfig::get('app_a_use_bundled_stylesheets', true))
    {
      return;
    }
    $response = sfContext::getInstance()->getResponse();
    $preferences = sfConfig::get('app_a_use_bundled_stylesheets', array());
    foreach (aTools::$cssByName as $stylesheet => $default)
    {
      $good = true;
      if (isset($preferences[$stylesheet]))
      {
        $good = $preferences[$stylesheet];
      }
      if ($good)
      {
        if ($good === true)
        {
          $winner = $default;
        }
        else
        {
          $winner = $good;
        }
        $options = array();
        $group = sfConfig::get('app_a_asset_group');
        if (!is_null($group))
        {
          $options['data-asset-group'] = $group;
        }
				// Our stylesheets now load first to avoid chicken and egg problems
        $response->addStylesheet($winner, 'first', $options);
      }
    }
  }
  
  // MUST BE KEPT UP TO DATE
  static protected $jsByName = array(
    'jquery' => '/apostrophePlugin/js/jquery-1.4.3.min.js',
    'main' => '/apostrophePlugin/js/a.js',
    'controls' => '/apostrophePlugin/js/aControls.js',
    'json2' => '/apostrophePlugin/js/json2.js',
    'jquery-autogrow' => '/apostrophePlugin/js/plugins/jquery.simpleautogrow.js',
    'jquery-hover-intent' => '/apostrophePlugin/js/plugins/jquery.hoverIntent.js',
    'jquery-scrollto' => '/apostrophePlugin/js/plugins/jquery.scrollTo-1.4.2-min.js', 
    'jquery-ui' => '/apostrophePlugin/js/plugins/jquery-ui-1.8.11.custom.min.js',
    'jquery-jplayer' => '/apostrophePlugin/js/plugins/jquery.jplayer.js', 
    'tagahead' => '/sfDoctrineActAsTaggablePlugin/js/pkTagahead.js'
  );

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function addJavascriptsIfDesired()
  {
    if (!sfConfig::get('app_a_use_bundled_javascripts', true))
    {
      return;
    }
    $response = sfContext::getInstance()->getResponse();
    $preferences = sfConfig::get('app_a_use_bundled_javascripts', array());
    foreach (aTools::$jsByName as $javascript => $default)
    {
      $good = true;
      if (isset($preferences[$javascript]))
      {
        $good = $preferences[$javascript];
      }
      if ($good)
      {
        if ($good === true)
        {
          $winner = $default;
        }
        else
        {
          $winner = $good;
        }
        $group = sfConfig::get('app_a_asset_group');
        $options = array();
        if (!is_null($group))
        {
          $options['data-asset-group'] = $group;
        }
				// We now load our javascripts first so that jQuery has always been loaded.
				// Typical developers, including our own team, take a dim view of having to
				// load their stuff explicitly "last" just to have jQuery
        $response->addJavascript($winner, 'first', $options);
      }
      else
      {
        // They don't want it at all
      }
    }
  }
  
  static protected $locks = array();

  /**
   * Lock names must be \w+
   * @param mixed $name
   */
  static public function lock($name, $wait = true)
  {
    $lockType = sfConfig::get('app_a_lock_type', 'flock');
    if ($lockType === 'mysql')
    {
      $info = aTools::getLockDbInfo();
      $dbName = $info['dbName'];
      $sql = $info['sql'];
      $key = "$dbName-$name";
      if ($wait)
      {
        $timeout = 30;
      }
      else
      {
        $timeout = 0;
      }
      $locked = $sql->queryOneScalar('SELECT GET_LOCK(:key, :timeout)', array('key' => $key, 'timeout' => $timeout));
      if (!$locked)
      {
        if (!$wait)
        {
          return false;
        }
        else
        {
          throw new sfException("Unable to obtain a lock after 30 seconds. MySQL must be very busy.");
        }
      }
      aTools::$locks[] = $name;
      return true;
    }
    else
    {
      $dir = aFiles::getWritableDataFolder(array('a', 'locks'));
      if (!preg_match('/^\w+$/', $name))
      {
        throw new sfException("Lock name is empty or contains non-word characters");
      }
      $file = "$dir/$name.lck";
      $tries = 0;
      while (true)
      {
        $fp = fopen($file, 'a');
        if ($fp)
        {
          $flags = LOCK_EX;
          if (!$wait)
          {
            $flags |= LOCK_NB;
          }
          if (flock($fp, $flags))
          {
            break;
          }
        }
        if (!$wait)
        {
          return false;
        }
        $tries++;
        if ($tries == 30)
        {
          throw new sfException("Unable to obtain a lock after 30 seconds. Set app_a_lockType to mysql or make sure $dir is on a filesystem that supports flock() calls.");
        }
        sleep(1);
      } 
      aTools::$locks[] = $fp;
      return true;
    }
  }

  static protected $lockSql;
  static protected $dbName;
  
  /**
   * Connect to the lock database and return an array containing
   * dbName (the name of the *main* database for this site) and
   * sql (an aMysql object ready to talk to the *lock* database, which
   * can be separate). We need dbName to namespace our locks properly
   *
   * If app_a_lock_db is unspecified we use the default database
   * for locks too. IF THIS DATABASE IS A MASTER/SLAVE SETUP, LOCKS WILL NOT 
   * BE PROPERLY MANAGED AND YOUR PAGE TREE WILL NOT BE SAFE. If this is a problem 
   * for you, set up a separate mysql instance that is NOT master/slave as a lockserver 
   * and specify the credentials for it in app_a_lock_db, giving the keys
   * host, user and password. No database name is required.
   *
   * Results are cached between calls during the same PHP request. Otherwise
   * we'd have separate connections to the lockdb and be unable to release what
   * we locked
   */
   
  static public function getLockDbInfo()
  {
    if (!isset(aTools::$lockSql))
    {
      $mainSql = new aMysql();
      $dbName = $mainSql->queryOneScalar('SELECT DATABASE()');
      if (!strlen($dbName))
      {
        throw new sfException('Unable to get name of main database for lock namespacing purposes');
      }
      $params = sfConfig::get('app_a_lock_db');
      if ($params)
      {
        $pdo = new PDO('mysql:host=' . $params['host'], $params['user'], $params['password']);
        if (!$pdo)
        {
          throw new sfException('Unable to connect to lock db specified by app_a_lock_db');
        }
        $sql = new aMysql($pdo);
      }
      else
      {
        // OK, use the main database. This is NOT SAFE if you use MySQL replication 
        $sql = new $mainSql;
      }
      aTools::$dbName = $dbName;
      aTools::$lockSql = $sql;
    }
    return array('dbName' => aTools::$dbName, 'sql' => aTools::$lockSql);
  }

  /**
   * Release the most recent lock
   */
  static public function unlock()
  {
    $lockType = sfConfig::get('app_a_lock_type', 'flock');
    
    if ($lockType === 'mysql')
    {
      if (count(aTools::$locks))
      {
        $name = array_pop(aTools::$locks);
        $info = aTools::getLockDbInfo();
        $dbName = $info['dbName'];
        $sql = $info['sql'];
        $key = "$dbName-$name";
        $sql->queryOneScalar('SELECT RELEASE_LOCK(:key)', array('key' => $key));
      }
    }
    else
    {
      if (count(aTools::$locks))
      {
        $fp = array_pop(aTools::$locks);
        fclose($fp);
      }
      else
      {
        // It's OK to call with no lock, this greatly simplifies methods like flunkUnless()
        // If you are using multiple names you are responsible for making sure you unlock consistently
      }
    }
  }
  
  static public function standardAreaSlots()
  {
    // The plaintext slot is deeply unexciting, do not offer it by default in a standard area.
    // Raw HTML is problematic but generally obligatory in practice
    return sfConfig::get('app_a_standard_area_slots', array('aRichText', 'aVideo', 'aSlideshow', 'aSmartSlideshow', 'aFile', 'aAudio', 'aFeed', 'aButton', 'aBlog', 'aEvent', 'aRawHTML'));
  }
  
  static public function standardAreaSlotOptions()
  {
    $standardOptions = sfConfig::get('app_a_standard_area_slot_options', array(
  		'aRichText' => array(
  		  'tool' => 'Sidebar',
  			// 'allowed-tags' => array(),
  			// 'allowed-attributes' => array('a' => array('href', 'name', 'target'),'img' => array('src')),
  			// 'allowed-styles' => array('color','font-weight','font-style'),
  		),
  		'aVideo' => array(
  			'width' => 480,
  			'height' => false,
  			'resizeType' => 's',
  			'flexHeight' => true,
  			'title' => false,
  			'description' => false,
  		),
  		'aImage' => array(
  			'width' => 480,
  			'height' => false,
  			'resizeType' => 's',
  			'flexHeight' => true,
  			'constraints' => array('minimum-width' => 480)
  		),
  		'aSlideshow' => array(
  			'width' => 480,
  			'height' => false,
  			'resizeType' => 's',
  			'flexHeight' => true,
  			'constraints' => array('minimum-width' => 480),
  			'arrows' => true,
  			'interval' => false,
  			'random' => false,
  			'title' => false,
  			'description' => false,
  			'credit' => false,
  			'position' => false,
  			'itemTemplate' => 'slideshowItem',
  			'allowed_variants' => array('normal','autoplay'), 
  		),
  		'aSmartSlideshow' => array(
  			'width' => 480,
  			'height' => false,
  			'resizeType' => 's',
  			'flexHeight' => true,
  			'constraints' => array('minimum-width' => 480),
  			'arrows' => true,
  			'interval' => false,
  			'random' => false,
  			'title' => false,
  			'description' => false,
  			'credit' => false,
  			'position' => false,
  			'itemTemplate' => 'slideshowItem',
  		),
  		'aFile' => array(
  		),
  		'aAudio' => array(
  			'width' => 480,
  			'title' => true,
  			'description' => true,
  			'download' => true,
  			'playerTemplate' => 'default',
  		),
  		'aFeed' => array(
  			'posts' => 5,
  			'links' => true,
  			'dateFormat' => false,
  			'itemTemplate' => 'aFeedItem',
  			// 'markup' => '<strong><em><p><br><ul><li><a>',
  			// 'attributes' => false,
  			// 'styles' => false,
  		),
  		'aButton' => array(
  			'width' => 480,
  			'flexHeight' => true,
  			'resizeType' => 's',
  			'constraints' => array('minimum-width' => 480),
  			'rollover' => true,
  			'title' => true,
  			'description' => false
  		),
  		'aBlog' => array(
  			// 'excerptLength' => 100, 
  			// 'aBlogMeta' => true,
  			// 'maxImages' => 1, 
  			'slideshowOptions' => array(
  				'width' => 480,
  				'height' => false
  			),
  		),
  		'aEvent' => array(
  			// 'excerptLength' => 100, 
  			// 'aBlogMeta' => true,
  			// 'maxImages' => 1, 
  			'slideshowOptions' => array(
  				'width' => 480,
  				'height' => false
  			),
  		),
  		'aRawHTML' => array(
  		)));
  	$extraOptions = sfConfig::get('app_a_standard_area_extra_slot_options', array());
  	$standardOptions = array_merge($standardOptions, $extraOptions);
  	return $standardOptions;
  }
}
