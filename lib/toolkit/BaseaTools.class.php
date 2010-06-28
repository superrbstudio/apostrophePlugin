<?php

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
  
  // Must reset ALL static variables to their initial state
  static public function listenToSimulateNewRequestEvent(sfEvent $event)
  {
    self::$global = false;
    self::$globalCache = false;
    self::$currentPage = null;
    self::$pageStack = array();
    self::$globalButtons = false;
    self::$allowSlotEditing = true;
    aNavigation::simulateNewRequest();
  }
  
  static public function cultureOrDefault($culture = false)
  {
    if ($culture)
    {
      return $culture;
    }
    return self::getUserCulture();
  }
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
  
  static public function setCurrentPage($page)
  {
    self::$currentPage = $page;
  }
  
  static public function getCurrentPage()
  {
    return self::$currentPage;
  }

  // Similar to getCurrentPage, but returns null if the current page is an admin page,
  // and therefore not suitable for normal navigation like the breadcrumb and subnav
  static public function getCurrentNonAdminPage()
  {
    $page = self::getCurrentPage();
    return $page ? ($page->admin ? null : $page) : null;
  }

  /**
   * We've fetched a page on our own using aPageTable::queryWithSlots and we want
   * to make Apostrophe aware of it so that areas on the current page that live on
   * that virtual page don't generate a superfluous second query
   *
   * @param array, Doctrine_Collection, aPage $pages
   */
  static public function cacheVirtualPages($pages)
  {
    if(get_class($pages) == 'Doctrine_Collection' || is_array($pages))
    {
      foreach($pages as $page)
      {
        self::$globalCache[$page['slug']] = $page;
      }
    }
    else
    {
      self::$globalCache[$pages['slug']] = $pages;
    }
  }

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
      self::$pageStack[] = self::getCurrentPage();
      // Caching the global page speeds up pages with two or more global slots
      if (isset(self::$globalCache[$slug]))
      {
        $global = self::$globalCache[$slug];
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
        self::$globalCache[$slug] = $global;
      }
      self::setCurrentPage($global);
      self::$global = true;
    }
  }

  static public function globalShutdown()
  {
    if (self::$global)
    {
      self::setCurrentPage(array_pop(self::$pageStack));
      self::$global = (count(self::$pageStack));
    }
  }

  static public function getSlotOptionsGroup($groupName)
  {
    $optionGroups = sfConfig::get('app_a_slot_option_groups', 
      array());
    if (isset($optionGroups[$groupName]))
    {
      return $optionGroups[$groupName];
    }
    throw new sfException("Option group $groupName is not defined in app.yml");
  }

  // Oops: we can't cache this list because it's different for various areas on the same page.
  
  static public function getSlotTypesInfo($options)
  {
    $instance = sfContext::getInstance();
    $slotTypes = array_merge(
      array(
         'aText' => 'Plain Text',
         'aRichText' => 'Rich Text',
         'aFeed' => 'RSS Feed',
         'aImage' => 'Image',
         'aSlideshow' => 'Slideshow',
         'aButton' => 'Button',
         'aVideo' => 'Video',
         'aPDF' => 'PDF',
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
  
  // Includes classes for buttons for adding each slot type
  static public function getSlotTypeOptionsAndClasses($options)
  {
    
  }
  
  static public function getOption($array, $name, $default)
  {
    if (isset($array[$name]))
    {
      return $array[$name];
    }
    return $default;
  }
  static public function getRealPage()
  {
    if (count(self::$pageStack))
    {
      $page = self::$pageStack[0];
      if ($page)
      {
        return $page;
      }
      else
      {
        return false;
      }
    }
    elseif (self::$currentPage)
    {
      return self::$currentPage;
    }
    else
    {
      return false;
    }
  }
  // Fetch options array saved in session
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
  
  static public function getTemplates()
  {
    if (sfConfig::get('app_a_get_templates_method'))
    {
      $method = sfConfig::get('app_a_get_templates_method');

      return call_user_func($method);
    }
    return sfConfig::get('app_a_templates', array(
      'default' => 'Default Page',
      'home' => 'Home Page'));
  }
  
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
  
  // Fetch an internationalized option from app.yml. Example:
  // all:
  //   a:
  
  static public function getOptionI18n($option, $default = false, $culture = false)
  {
    $culture = self::cultureOrDefault($culture);
    $values = sfConfig::get("app_a_$option", array());
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
  
  static public function getGlobalButtonsInternal(sfEvent $event)
  {
    // If we needed a context object we could get it from $event->getSubject(),
    // but this is a simple static thing
    
    // Add the users button only if the user has the admin credential.
    // This is typically only given to admins and superadmins.
    // TODO: there is also the cms_admin credential, should I differentiate here?
    $user = sfContext::getInstance()->getUser();
    if ($user->hasCredential('admin'))
    {
      $extraAdminButtons = sfConfig::get('app_a_extra_admin_buttons', 
        array('users' => array('label' => 'Users', 'action' => 'aUserAdmin/index', 'class' => 'a-users'),
          'reorganize' => array('label' => 'Reorganize', 'action' => 'a/reorganize', 'class' => 'a-reorganize')        
        ));
      // Eventually this one too. Reorganize will probably get moved into it
      // ('Settings', 'a/globalSettings', 'a-settings')

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
  
  // To be called only in response to a a.getGlobalButtons event 
  static public function addGlobalButtons($array)
  {
    self::$globalButtons = array_merge(self::$globalButtons, $array);
  }
  
  static public function getGlobalButtons()
  {
    if (self::$globalButtons !== false)
    {
      return self::$globalButtons;
    }
    $buttonsOrder = sfConfig::get('app_a_global_button_order', false);
    self::$globalButtons = array();
    // We could pass parameters here but it's a simple static thing in this case 
    // so the recipients just call back to addGlobalButtons
    sfContext::getInstance()->getEventDispatcher()->notify(new sfEvent(null, 'a.getGlobalButtons', array()));
    
    $buttonsByName = array();
    foreach (self::$globalButtons as $button)
    {
      $buttonsByName[$button->getName()] = $button;
    }
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
    
    self::$globalButtons = $orderedButtons;
    return $orderedButtons;
  }
  
  static public function globalToolsPrivilege()
  {
    // if you can edit the page, there are tools for you in the apostrophe
    if (self::getCurrentPage() && self::getCurrentPage()->userHasPrivilege('edit'))
    {
      return true;
    }
    // if you are the site admin, there are ALWAYS tools for you in the apostrophe
    $user = sfContext::getInstance()->getUser();
    return $user->hasCredential('cms_admin');
  }
  
  // These methods allow slot editing to be turned off even for people with
  // full and appropriate privileges.
  
  // Most of the time being able to edit a global slot on a non-CMS page is a
  // good thing, especially if that's the only place the global slot appears.
  // But sometimes, as in the case where you're editing other types of data,
  // it's just a source of confusion to have those buttons displayed. 
  
  // (Suppressing editing of slots on normal CMS pages is of course a bad idea,
  // because how else would you ever edit them?)
  
  static public function setAllowSlotEditing($value)
  {
    self::$allowSlotEditing = $value;
  }
  static public function getAllowSlotEditing()
  {
    return self::$allowSlotEditing;
  }
  
  // Kick the user out to appropriate places if they don't have the proper 
  // privileges to be here. a::executeShow and aEngineActions::preExecute
  // both use this 
  
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
    if ($page->archived && (!$page->userHasPrivilege('edit|manage')))
    {
      $action->forward404();
    }    
  }

  // Establish the page title, set the layout, and add the javascripts that are
  // necessary to manage pages. a::executeShow and aEngineActions::preExecute
  // both use this. TODO: is this redundant now that aHelper does it?
  
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
  
  static public function pageIsDescendantOfInfo($page, $info)
  {
    return ($page->lft > $info['lft']) && ($page->rgt < $info['rgt']);
  }
  
  // Same rules found in aPage::userHasPrivilege(), but without checking for
  // a particular page, so we return true even for users who are just *potential* editors
  // when granted privileges at an appropriate point in the page tree. This is useful for
  // deciding whether the tabs control should show archived pages or not. (Showing those to
  // a few editors who can't edit them is not a major problem, and checking the privs on
  // each and every one is an unacceptable performance hit) 
  
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
  
    // Rule 6: when minimum but not sufficient credentials are present,
    // check for an explicit grant of privileges to this user, on
    // this page or on any ancestor page.
    $result = $this->userHasExplicitPrivilege($privilege);
    if ($result)
    {
      return true;
    }
  }
  
  static public function getVariantsForSlotType($type, $options = array())
  {
    // 1. By default, all variants of the slot are allowed.
    // 2. If app_a_allowed_variants is set and a specific list of allowed variants
    // is provided for this slot type, those variants are allowed.
    // 3. If app_a_allowed_variatns is set and a specific list is not present for this slot type,
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
      $allowed = array_flip($allowedVariants);
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
  
  static public function getRealUrl()
  {
    if(isset(self::$realUrl))
    {
      return self::$realUrl;
    }
    return sfContext::getInstance()->getRequest()->getUri();
  }
  
  static public function setRealUrl($url)
  {
    self::$realUrl = $url;
  }
  
  // Returns a regexp fragment that matches a valid slug in a UTF8-aware way.
  // Does not reject slugs with consecutive dashes or slashes. DOES accept the %
  // sign because URLs generated by url_for arrive with the UTF8 characters
  // %-encoded. You should anchor it with ^ and $ if your goal is to match one slug as the whole string
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
  
  // UTF-8 where available. If your UTF-8 gets munged make sure your PHP has the
  // mbstring extension. allowSlashes will allow / but will reduce duplicate / and
  // remove any / at the end. Everything that isn't a letter or a number 
  // (or a slash, when allowed) is converted to a -. Consecutive -'s are reduced and leading and
  // trailing -'s are removed
  
  static public function slugify($path, $allowSlashes = false)
  {
    // This is the inverse of the method above
    if (function_exists('mb_strtolower'))
    {
      // UTF-8 capable replacement for \W. Works fine for English and also for Greek, etc.
      $alnum = '\p{L}\p{N}_';
      $modifier = 'u';
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
    $regexp = "/[^$alnum\-]+/$modifier";
    $path = aTools::strtolower(preg_replace("/[^$alnum\-]+/$modifier", '-', $path));  
    if ($allowSlashes)
    {
      // No multiple consecutive /
      $path = preg_replace("/\/+/$modifier", "/", $path);
      // No trailing /
      $path = preg_replace("/\/$/$modifier", '', $path);
    }
    // No consecutive dashes
    $path = preg_replace("/-+/$modifier", '-', $path);
    // Leading and trailing dashes are silly. This has the effect of trim()
    // among other sensible things
    $path = preg_replace("/^-*(.*?)-*$/$modifier", '$1', $path);     
    return $path;
  }

  static public function strtolower($s)
  {
    if (function_exists('mb_strtolower'))
    {
      return mb_strtolower($s, 'UTF-8');
    }
    else
    {
      return strtolower($s);
    }
  }
  
}
