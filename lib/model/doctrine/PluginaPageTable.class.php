<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PluginaPageTable extends Doctrine_Table
{
  // Caches. Note that these are not compatible with functional tests, which don't
  // provide a good way to clean up and pretend it's a new request at the PHP level
  
  static public $ancestorsInfo = array();
  static public $peersInfo = array();
  static public $pagesInfo = array();
  static public $childrenInfo = array();
  
	// We always join with all of the current slots for the proper culture in this simplest page-getter method.
	// Otherwise we wreck the slot cache for slots on the page, etc., can't see titles or see the wrong versions
	// and cultures of slots. This is inefficient in some situations, but the
	// right response to that is to recognize when you're about to fetch a page
	// that has already been fetched and just reuse it. I can't make that call
	// for you at the model level

  static public function retrieveBySlug($slug, $culture = null)
  {
    return aPageTable::retrieveBySlugWithSlots($slug, $culture);
  }

	// CAREFUL: if you are not absolutely positive that you won't need other slots for this
	// page (ie it is NOT the current page), then don't use this. Use retrieveBySlugWithSlots

  // If culture is null you get the current user's culture,
  // or sf_default_culture if none is set or we're running in a task context
  static public function retrieveBySlugWithTitles($slug, $culture = null)
  {
    if (is_null($culture))
    {
      $culture = aTools::getUserCulture();
    }
    $query = aPageTable::queryWithTitles($culture);
    $page = $query->
      andWhere('p.slug = ?', $slug)->
      fetchOne();
    // In case Doctrine is clever and returns the same page object
    if ($page)
    {
      $page->clearSlotCache();
      $page->setCulture($culture);
    }
    return $page;
  }

  // If culture is null you get the current user's culture,
  // or sf_default_culture if none is set or we're running in a task context
  static public function retrieveBySlugWithSlots($slug, $culture = null)
  {
    if (is_null($culture))
    {
      $culture = aTools::getUserCulture();
    }
    $query = aPageTable::queryWithSlots(false, $culture);
    $page = $query->
      andWhere('p.slug = ?', $slug)->
      fetchOne();
    // In case Doctrine is clever and returns the same page object
    if ($page)
    {
      $page->clearSlotCache();
      $page->setCulture($culture);
    }
    return $page;
  }
  // If culture is null you get the current user's culture,
  // or sf_default_culture if none is set or we're running in a task context

  static public function queryWithTitles($culture = null)
  {
    return aPageTable::queryWithSlot('title', $culture);
  }

  // This is a slot name, like 'title'
  static public function queryWithSlot($slot, $culture = null)
  {
    if (is_null($culture))
    {
      $culture = aTools::getUserCulture();
    }
    $query = aPageTable::queryWithSlots(false, $culture)
      ->andWhere('a.name = ?', $slot);

    return $query;
  }

  // This is a slot type, like 'aRichText'
  static public function queryWithSlotType($slotType, $culture = null)
  {
    if (is_null($culture))
    {
      $culture = aTools::getUserCulture();
    }
    $query = aPageTable::queryWithSlots(false, $culture)
      ->andWhere('s.type = ?', $slotType);

    return $query;
  }

  // If culture is null you get the current user's culture,
  // or sf_default_culture if none is set or we're running in a task context

  static public function retrieveByIdWithSlots($id, $culture = null)
  {
    return aPageTable::retrieveByIdWithSlotsForVersion($id, false, $culture);
  }
  // If culture is null you get the current user's culture,
  // or sf_default_culture if none is set or we're running in a task context

  static public function retrieveByIdWithSlotsForVersion($id, $version, $culture = null)
  {
    if (is_null($culture))
    {
      $culture = aTools::getUserCulture();
    }
    $page = aPageTable::queryWithSlots($version, $culture)->
      andWhere('p.id = ?', array($id))->
      fetchOne();
    // In case Doctrine is clever and returns the same page object
    if ($page)
    {
      $page->clearSlotCache();
      // Thanks to Quentin Dugauthier for spotting that there were
      // still instances of this not being inside the if
      $page->setCulture($culture);
    }
    return $page;
  }

  /**
   * Gets a query for slots and media items for pages, used for when rendering a page.
   * @param int $version version of slot to query for, if false returns latest version of each slot
   * @param $culture culture to retrieve from, if null the current user's culture, or sf_default_culture if cone is set or we're running in a task context
   * @param Doctrine_Query $query
   * @return Doctrine_Query $query
   */
  public static function queryWithSlots($version = false, $culture = null, $query = null)
  {
    if(is_null($query))
    {
      $query = Doctrine::getTable('aPage')->createQuery('p');
    }
    if (is_null($culture))
    {
      $culture = aTools::getUserCulture();
    }
    
    // ACHTUNG: resist the temptation to move these into WHERE clauses.
    // It looks all sweet and innocent until you don't get any records back
    // and assume you have to recreate the global page... once on every page load.
    // That's why we LEFT JOIN in the first place
    $areaJoinArgs = array();
    $areaJoin = 'p.Areas a';
    if ($culture !== 'all')
    {
      $areaJoin .= ' WITH a.culture = ?';
      $areaJoinArgs[] = $culture;
    }
    $versionJoinArgs = array();
    $versionJoin = 'a.AreaVersions v';
    if ($version === false)
    {
      $versionJoin .= ' WITH a.latest_version = v.version';
    } else
    {
      $versionJoin .= ' WITH v.version = ?';
      $versionJoinArgs[] = $version;
    }
    
    $query->
      leftJoin($areaJoin, $areaJoinArgs)->
      leftJoin($versionJoin, $versionJoinArgs)->
      leftJoin('v.AreaVersionSlots avs')->
      leftJoin('avs.Slot s')->
      leftJoin('s.MediaItems m')->
      orderBy('avs.rank asc');

    // If we don't do this explicitly, ->getNode()->getChildren()
    // will only pull a few page columns, resulting in 
    // redundant and inaccurate queries. You can add more
    // with addSelect()
    
    $query->addSelect('p.*,a.*,v.*,v.*,avs.*,s.*,m.*');

    return $query;
  }
   
  static protected $treeObject = null;
  
  static public function treeTitlesOn()
  {
    aPageTable::treeSlotOn('title');
  }
  
  static public function treeSlotOn($slot)
  {
    $query = aPageTable::queryWithSlot($slot);
    aPageTable::$treeObject = Doctrine::getTable('aPage')->getTree();
    // I'm not crazy about how I have to set the base query and then
    // reset it, instead of simply passing it to getChildren. A
    // Doctrine oddity
    aPageTable::$treeObject->setBaseQuery($query);
  }
  
  static public function treeTitlesOff()
  {
    aPageTable::treeSlotOff();
  }
  
  static public function treeSlotOff()
  {
    aPageTable::$treeObject->resetBaseQuery();
  } 
  
  public function getLuceneIndexFile()
  {
    return aZendSearch::getLuceneIndexFile($this);
  }

  public function getLuceneIndex()
  {
    return aZendSearch::getLuceneIndex($this);
  }

  // This does the entire thing at one go, which may be too memory intensive.
  // The apostrophe:rebuild-search-index task instead invokes apostrophe:update-search-index
  // for batches of 100 pages
  public function rebuildLuceneIndex()
  {
    aZendSearch::purgeLuceneIndex($this);
    $pages = $this->createQuery('p')->innerJoin('p.Areas a')->execute(array(), Doctrine::HYDRATE_ARRAY);
    foreach ($pages as $page)
    {
      $cultures = array();
      foreach ($page['Areas'] as $area)
      {
        $cultures[$area['culture']] = true; 
      }
      $cultures = array_keys($cultures);
      foreach ($cultures as $culture)
      {
        $cpage = aPageTable::retrieveBySlugWithSlots($page['slug'], $culture);
        $cpage->updateLuceneIndex();
      }
    }
  }
  
  public function addSearchQuery(Doctrine_Query $q = null, $luceneQuery)
  {
    // Page searches are always specific to this user's culture
    $culture = aTools::getUserCulture();
    $luceneQuery = "+(text:($luceneQuery))";
    return aZendSearch::addSearchQuery($this, $q, $luceneQuery, $culture);
  }
  
  public function addSearchQueryWithScores(Doctrine_Query $q = null, $luceneQuery, &$scores)
  {
    // Page searches are always specific to this user's culture
    $culture = aTools::getUserCulture();
    $luceneQuery = "+(text:($luceneQuery))";
    return aZendSearch::addSearchQueryWithScores($this, $q, $luceneQuery, $culture, $scores);
  }
  
  // Just a hook used by the above
  public function searchLucene($query, $culture)
  {
    return aZendSearch::searchLucene($this, $query, $culture);
  }
  
  // Just a hook used by the above
  public function searchLuceneWithScores($query, $culture)
  {
    return aZendSearch::searchLuceneWithScores($this, $query, $culture);
  }

  // Returns engine page with the longest matching path.
  // We use a cache so that we don't wind up making separate queries
  // for every engine route in the application
  
  protected static $engineCacheUrl = false;
  protected static $engineCachePageInfo = false;
  protected static $engineCacheRemainder = false;
  protected static $engineCacheFirstEnginePages = array();
  protected static $engineCachePagePrefix = false;
  protected static $dummyUrlCache = array();
  // DEPRECATED. Never returned a fully populated page anyway, and yet
  // it wasted time and memory on object hydration. I doubt anyone used
  // this directly but us, so I'm not wrapping an entirely new cache around it
  
  static public function getMatchingEnginePage($url, &$remainder)
  {
    $info = aPageTable::getMatchingEnginePageInfo($url, $remainder);
    if ($info)
    {
      return Doctrine::getTable('aPageTable')->find($info['id']);
    }
    return null;
  }
  
  // For performance reasons this returns a stripped down info array with
  // just the slug, engine and id guaranteed to be present, NO title
  
  static public function getMatchingEnginePageInfo($url, &$remainder)
  {
    // The URL should match a_page, which should allow us to break the slug out and learn the
    // user's culture in a way that doesn't hardcode whether cultures are present and how etc.
    
    $routes = sfContext::getInstance()->getRouting()->getRoutes();
    $culture = aTools::getUserCulture();
    
    if (isset($routes['a_page']))
    {
      $r = $routes['a_page'];
      $parameters = $r->matchesUrl($url);
      if ($parameters)
      {
        // Since we're not really visiting a_page the culture won't switch
        // if we don't visit some normal page in French before the engine page.
        // We resolve this by implementing the culture switch here
        if (isset($parameters['sf_culture']))
        {
          $culture = $parameters['sf_culture'];
          $user = sfContext::getInstance()->getUser();
          if ($user)
          {
            $user->setCulture($culture);
          }
        }
      }
    }
    
    // Engines won't work on sites where the CMS is not mounted at the root of the site
    // unless we examine the a_page route to determine a prefix. Generate the route properly
    // then lop off the controller name, if any
            
    $prefix = '';
    $culturePrefix = '';
    if (!isset(aPageTable::$dummyUrlCache[$culture]))
    {
      aPageTable::$dummyUrlCache[$culture] = sfContext::getInstance()->getRouting()->generate('a_page', array('slug' => 'dummy', 'sf_culture' => $culture), false);
    }
    $dummyUrl = aPageTable::$dummyUrlCache[$culture];
    $rr = preg_quote(sfContext::getInstance()->getRequest()->getRelativeUrlRoot(), '/');
    // The URL we're being asked to examine has already
    // lost its relative_root_url, so don't include $rr in
    // the prefix we attempt to remove
    
    // Tolerate and ignore a query string (which will be there if sf_culture is not prettified by the route)
    $re = "/^(\/\w+\.php)?$rr(.*)\/dummy(?:\?.*)?$/";
    if (preg_match($re, $dummyUrl, $matches))
    {
      $controllerPrefix = $matches[1];
      $culturePrefix = $matches[2];
    }
    aPageTable::$engineCachePagePrefix = $prefix;
    $url = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $url);
    
    
    // Now remove the culture prefix too so we don't fail to find the page
    $url = preg_replace('/^' . preg_quote($culturePrefix, '/') . '/', '', $url);

    // Moved caching after the rewrites, otherwise it never works for
    // interesting frontend controller names, URLs with cultures in them, etc.

    if ($url === aPageTable::$engineCacheUrl)
    {
      $remainder = aPageTable::$engineCacheRemainder;
      return aPageTable::$engineCachePageInfo;
    }
    
    $urls = array();
    // Remove any query string
    $twig = preg_replace('/\?.*$/', '', $url);
    while (true)
    {
      if (($twig === '/') || (!strlen($twig)))
      {
        // Either we've been called for the home page, or we just
        // stripped the first slash and are now considering the home page
        $urls[] = '/';
        break;
      }
      $urls[] = $twig;
      if (!preg_match('/^(.*)\/[^\/]+$/', $twig, $matches))
      {
        break;
      }
      $twig = $matches[1];
    }
    $pageInfo = Doctrine_Query::create()->
      select('p.*, length(p.slug) as len')->
      from('aPage p')->
      whereIn('p.slug', $urls)->
      andWhere('p.engine IS NOT NULL')->
      orderBy('len desc')->
      limit(1)->
      fetchOne(array(), Doctrine::HYDRATE_ARRAY);
    aPageTable::$engineCachePageInfo = $pageInfo;
    aPageTable::$engineCacheUrl = $url;
    aPageTable::$engineCacheRemainder = false;
    if ($pageInfo)
    {
      $remainder = substr($url, strlen($pageInfo['slug']));
      aPageTable::$engineCacheRemainder = $remainder;
      return $pageInfo;
    }
    return false;
  }
  
  // Used when generating an engine link from a page other than the engine page itself.
  // Many engines are only placed in one location per site, so this is often reasonable.
  // Cache this for acceptable performance. Admin pages match first to ensure that the
  // Apostrophe menu always goes to the right place. If you have a public version of the same
  // engine and you want to link to it via link_to(), target it explicitly, see
  // aRouteTools::pushTargetEnginePage()
  
  static public function getFirstEnginePage($engine)
  {
    if (isset(aPageTable::$engineCacheFirstEnginePages[$engine]))
    {
      return aPageTable::$engineCacheFirstEnginePages[$engine];
    }
    $page = Doctrine_Query::create()->
     from('aPage p')->
     where('p.engine = ?', array($engine))->
     // Take care NOT to match virtual pages. They use the engine column
     // to indicate the engine they are associated with
     // Don't match virtual pages
     addWhere('slug LIKE "/%"')->
     limit(1)->
     fetchOne();
    aPageTable::$engineCacheFirstEnginePages[$engine] = $page;
    return $page;
  }
  
  // Useful with queries aimed at finding a page; avoids the 
  // considerable expense of hydrating it
  static public function fetchOneSlug($query)
  {
    $query->limit(1);
    $data = $query->fetchArray();
    if (!count($data))
    {
      return false;
    }
    return $data[0]['slug'];
  }
  
  // Want to extend privilege checks? Override checkUserPrivilegeBody(). Read on for details
  
  // Check whether the user has sufficient privileges to access a page. This includes
  // checking explicit privileges in the case of pages that have them on sites where
  // there is a 'candidate group' for that privilege. $pageOrInfo can be a
  // Doctrine aPage object or an info structure like those returned by getAncestorsInfo() etc.
  
  // Sometimes you can't afford the overhead of an aPage object, thus this method.
  
  static $privilegesCache = array();
  
  // Static methods are tricky to override in PHP. Get an instance of the table and call a new
  // non-static version
  
  static public function checkPrivilege($privilege, $pageOrInfo, $user = false)
  {
    $table = Doctrine::getTable('aPage');
    return $table->checkUserPrivilege($privilege, $pageOrInfo, $user);
  }
  
  public function checkUserPrivilege($privilege, $pageOrInfo, $user)
  {
    if ($user === false)
    {
      $user = sfContext::getInstance()->getUser();
    }
    
    $username = false;
    if ($user->getGuardUser())
    {
      $username = $user->getGuardUser()->getUsername();
    }

    // Archived pages can only be visited by users who are permitted to edit them.
    // This trumps the less draconian privileges for viewing pages, locked or otherwise
    if (($privilege === 'view') && $pageOrInfo['archived'])
    {
      $privilege = 'edit';
    }
    else
    {
      // Individual pages can be conveniently locked for 
      // viewing purposes on an otherwise public site. This is
      // implemented as a separate permission. 
      if ($privilege === 'view') 
      {
        if ($pageOrInfo['view_is_secure'])
        {
          if ($pageOrInfo['view_guest'])
          {
            $privilege = 'view_locked';
          }
          else
          {
            // There are never 'sufficient' credentials for this so we wind up checking
            // for specific privileges. However if we can edit or manage the page
            // we can always view it
            $privilege = 'edit|manage|view_custom';
          }
        }
        else
        {
          if ($pageOrInfo['view_admin_lock'])
          {
            return $user->isAuthenticated() && $user->hasCredential('cms_admin');
          }
        }
      }
    }

    // If you can manage, you can also edit. Implement this 
    // in one place so we don't have to say it explicitly all over
    if ($privilege === 'edit')
    {
      $privilege = 'edit|manage';
    }

    if (isset(aPageTable::$privilegesCache[$username][$privilege][$pageOrInfo['id']]))
    {
      return aPageTable::$privilegesCache[$username][$privilege][$pageOrInfo['id']];
    }
    $result = $this->checkUserPrivilegeBody($privilege, $pageOrInfo, $user, $username);
    aPageTable::$privilegesCache[$username][$privilege][$pageOrInfo['id']] = $result;
    return $result;
  }
  
  // The privilege name has already been transformed if appropriate. The username has already been fetched
  // (false for a logged out user). The cache has already been checked. The return value of this call will
  // be cached by the checkUserPrivilege method. Override this method, calling the parent first and then
  // adding further checks as you deem necessary
  
  public function checkUserPrivilegeBody($privilege, $pageOrInfo, $user, $username)
  {
    $result = false;
    
    // Rule 1: admin can do anything
    // Work around a bug in some releases of sfDoctrineGuard: users sometimes
    // still have credentials even though they are not logged in
    if ($user->isAuthenticated() && $user->hasCredential('cms_admin'))
    {
      $result = true;
    }
    else
    {
      $privileges = explode("|", $privilege);
      foreach ($privileges as $privilege)
      {
        // Rule 1a: if edit_admin_lock is set, only admins can edit or manage
        if (($privilege === 'edit') || ($privilege === 'manage'))
        {
          if ($pageOrInfo['edit_admin_lock'])
          {
            continue;
          }
        }
        
        $sufficientCredentials = sfConfig::get('app_a_' . $privilege . '_sufficient_credentials', false);
        $sufficientGroup = sfConfig::get('app_a_' . $privilege . '_sufficient_group', false);
            
        // Starting in 1.5 edit and manage share a candidate group
        if ($privilege === 'manage')
        {
          $privilegeForGroup = 'edit';
        }
        else
        {
          $privilegeForGroup = $privilege;
        }
        $candidateGroup = sfConfig::get('app_a_'.$privilegeForGroup.'_candidate_group', false);
            
        // By default users must log in to do anything, except for viewing an unlocked page
        $loginRequired = sfConfig::get('app_a_'.$privilege.'_login_required', ($privilege === 'view' ? false : true));

        // Rule 2: if no login is required for the site as a whole for this
        // privilege, anyone can do it...
        if (!$loginRequired)
        {
          $result = true;
          break;
        }

        // Corollary of rule 2: if login IS required and you're not
        // logged in, bye-bye
        if (!$user->isAuthenticated())
        {
          continue;
        }

        if ($privilege !== 'view_custom')
        {
          // Rule 3: if there are no sufficient credentials and there is no
          // required or sufficient group, then login alone is sufficient. Common 
          // on sites with one admin. Exception: there is no such thing as sufficient
          // credentials for view_custom, the whole point is to check your
          // individual credentials
          if (($sufficientCredentials === false) && ($candidateGroup === false) && ($sufficientGroup === false))
          {
            // Logging in is the only requirement
            $result = true;
            break;
          }

          // Rule 4: if the user has sufficient credentials... that's sufficient!
          // Many sites will want to simply say 'editors can edit everything' etc
          if ($sufficientCredentials && 
            ($user->hasCredential($sufficientCredentials)))
          {
            $result = true;
            break;
          }
          if ($sufficientGroup && 
            ($user->hasGroup($sufficientGroup)))
          {
            $result = true;
            break;
          }
        }

        // Remaining cases require that the page be in the tree so explicit privileges can be applied
        // Failure to check this grants blanket access to all global slots
        if (!$pageOrInfo['lft'])
        {
          continue;
        }

        // Rule 5: if there is a candidate group, make sure the user is a member
        // before checking for explicit privileges for that user
        if ($candidateGroup && 
          (!$user->hasGroup($candidateGroup)))
        {
          // Nope
        }
        else
        {
          // The explicit case for users
          $user_id = $user->getGuardUser()->getId();
        
          // Privileges are no longer inherited. Instead there are generous tools for
          // explicitly copying a permission to all descendant pages, and then it is 
          // possible to modify them individually. There is also a lock option which
          // restricts edits or views to admins only and cannot be clobbered by a cascade. 
          // This is a good compromise that avoids forcing the full complexities of ACLs 
          // on admins while still allowing for a wide array of commonly encountered scenarios
          $accesses = Doctrine_Query::create()->
            select('a.*')->from('aAccess a')->innerJoin('a.Page p')->
            where("p.id = ? AND a.user_id = ? AND a.privilege = ?", array($pageOrInfo['id'], $user_id, $privilege))->
            limit(1)->
            execute(array(), Doctrine::HYDRATE_ARRAY);
          if (count($accesses) > 0)
          {
            $result = true;
            break;
          }
        }
        
        // We don't have this privilege as an individual. How about via a group?
        
        // Get group memberships
        
        $groupIds = aArray::getIds($user->getGroups());

        if (!count($groupIds))
        {
          continue;
        }
        
        $query = Doctrine_Query::create()->
          select('a.*')->from('aGroupAccess a')->innerJoin('a.Group g');
        
        // All groups are fair game to receive view permissions
        if ($privilege !== 'view_custom')
        {  
          $query->innerJoin('g.Permissions per WITH per.name = ?', sfConfig::get('app_a_group_editor_permission', 'editor'));
        }
        $accesses = $query->innerJoin('a.Page p')->
          where("p.id = ? AND a.privilege = ?", array($pageOrInfo['id'], $privilege))->
          andWhereIn("a.group_id", $groupIds)->
          limit(1)->
          execute(array(), Doctrine::HYDRATE_ARRAY);
        if (count($accesses) > 0)
        {
          $result = true;
          break;
        }
      }
    }
    return $result;
  }
  
  // Gets only users who are candidates to be editors if given access -
  // not admins who have it regardless. Useful for populating permissions widgets
  public function getEditorCandidates()
  {
    $sufficientCredentials = sfConfig::get('app_a_edit_sufficient_credentials', false);
    $sufficientGroup = sfConfig::get('app_a_edit_sufficient_group', false);
    $candidateGroup = sfConfig::get('app_a_edit_candidate_group', false);
    $sufficientGroup = sfConfig::get('app_a_edit_candidate_group', false);
    if (!$candidateGroup)
    {
      return Doctrine::getTable('sfGuardUser')->createQuery('u')->orderBy('u.username ASC')->fetchArray();
    }
    else
    {
      return Doctrine::getTable('sfGuardUser')->createQuery('u')->innerJoin('u.Groups g WITH g.name = ?', $candidateGroup)->orderBy('u.username ASC')->fetchArray();
    }
  }

  public function getEditorCandidateGroups()
  {
    $p = sfConfig::get('app_a_group_editor_permission', 'editor');
    return Doctrine::getTable('sfGuardGroup')->createQuery('g')->innerJoin('g.Permissions p WITH p.name = ?', $p)->orderBy('g.name ASC')->fetchArray();
  }

  // View candidates = everyone with the view_locked permission whether individually or
  // via a group. On some sites this is a lot of people, so we may find ourselves
  // overriding this for some projects, or just turning off individual permissions
  // for some projects in favor of group permissions. At worst, you'd have to create
  // a group just for Dean Bob
  
  public function getViewCandidates()
  {
    $sufficientCredentials = sfConfig::get('app_a_view_locked_sufficient_credentials', 'view_locked');

    $q = Doctrine::getTable('sfGuardUser')->createQuery('u');
    if ($sufficientCredentials)
    {
      $q->leftJoin('u.Groups g')->leftJoin('g.Permissions gp WITH gp.name = ?', $sufficientCredentials);
      $q->leftJoin('u.Permissions p WITH p.name = ?', $sufficientCredentials);
      $q->andWhere('gp.name = ? OR p.name = ?', array($sufficientCredentials, $sufficientCredentials));
    }
    return $q->orderBy('u.username ASC')->fetchArray();
  }
  
  public function getViewCandidateGroups()
  {
    // All groups are fair game to receive view permissions
    
    return Doctrine::getTable('sfGuardGroup')->createQuery('g')->orderBy('g.name ASC')->fetchArray();
  }
  
  // Gets explicit permissions, not implied permissions such as those held
  // by all admins
  
  public function getPrivilegesInfoForPageId($id)
  {
    $current = Doctrine::getTable('aPage')->createQuery('p')->where('p.id = ?', $id)->innerJoin('p.Accesses a')->innerJoin('a.User u')->orderBy('u.username ASC, a.privilege ASC')->fetchArray();
    $info = array();
    // There will be only one page returned but it's arranged as an array
    foreach ($current as $page)
    {
      foreach ($page['Accesses'] as $a)
      {
        $u = $a['User'];
        $id = $u['id'];
        $info[$id]['id'] = $id;
        $info[$id]['name'] = $u['username'];
        $info[$id]['privileges'][$a['privilege']] = true;
      }
    }
    return $info;
  }
  
  // Gets explicit permissions, not implied permissions such as those held
  // by all admins
  
  public function getGroupPrivilegesInfoForPageId($id)
  {
    $current = Doctrine::getTable('aPage')->createQuery('p')->where('p.id = ?', $id)->innerJoin('p.GroupAccesses a')->innerJoin('a.Group g')->orderBy('g.name ASC, a.privilege ASC')->fetchArray();
    $info = array();
    // There will be only one page returned but it's arranged as an array
    foreach ($current as $page)
    {
      foreach ($page['GroupAccesses'] as $a)
      {
        $g = $a['Group'];
        $id = $g['id'];
        $info[$id]['id'] = $id;
        $info[$id]['name'] = $g['name'];
        $info[$id]['privileges'][$a['privilege']] = true;
      }
    }
    return $info;
  }

  // Accepts array('info' => [page info array], 'includeSelf' => false, all options accepted by getPagesInfo except 'where')
   
  static public function getAncestorsInfo($options)
  {
    $id = $options['info']['id'];
    $includeSelf = isset($options['includeSelf']) ? $options['includeSelf'] : false;
    // We cache the results of one simple query that gets the whole lineage, and permute that a little
    // for the includeSelf case
		$key = serialize($options);
    if (!isset(aPageTable::$ancestorsInfo[$key]))
    {
      // Since our presence on an admin page implies we know about it, it's OK to include
      // admin pages in the breadcrumb. It's not OK in other navigation
      aPageTable::$ancestorsInfo[$key] = aPageTable::getPagesInfo(array_merge($options, array('where' => "( p.lft <= " . $options['info']['lft'] . " AND p.rgt >= " . $options['info']['rgt'] . ' )')));
    }
		$ancestorsInfo = aPageTable::$ancestorsInfo[$key];
		if (!$includeSelf)
		{
			array_pop($ancestorsInfo);
		}
		return $ancestorsInfo;
  }
  
  // Accepts array('info' => [page info array], all options accepted by getPagesInfo except where)
  
  static public function getParentInfo($options)
  {
    $info = aPageTable::getAncestorsInfo($options);
    if (count($info))
    {
      return $info[count($info) - 1];
    }
    return false;
  }

  // Accepts array('info' => [page info array], all options accepted by getPagesInfo except where)
  
  static public function getPeerInfo($options)
  {
    $id = $options['info']['id'];
    if (!isset(aPageTable::$peersInfo[$id]))
    {
      // Even if the parent is archived we need to know our true peers
      $parentInfo = aPageTable::getParentInfo(array_merge($options, array('ignore_permissions' => true)));
      if (!$parentInfo)
      {
        // It's the home page. Return a stub: the home page is its only peer
        aPageTable::$peersInfo[$id] = array(aPageTable::getInfo($options));
      }
      else
      {
        $lft = $parentInfo['lft'];
        $rgt = $parentInfo['rgt'];
        $level = $parentInfo['level'] + 1;
        aPageTable::$peersInfo[$id] = aPageTable::getPagesInfo(array_merge($options, array('where' => '(( p.lft > ' . $lft . ' AND p.rgt < ' . $rgt . ' ) AND (level = ' . $level . '))')));        
      }       
    }   
    return aPageTable::$peersInfo[$id];
  }
  
  // Unlike the others this accepts $options['id'] rather than $options['info'] and provides a way of
  // obtaining an info array about any page. If you have a page object just call getInfo() on that object,
  // that is typically more efficient
  
  static public function getInfo($options)
  {
    $id = (int) $options['id'];
    if (!isset(aPageTable::$pagesInfo[$id]))
    {
      aPageTable::$pagesInfo[$id] = aPageTable::getPagesInfo(array_merge($options, array('where' => '(id = ' . $id . ')')));
    }
    if (count(aPageTable::$pagesInfo[$id]))
    {
      return aPageTable::$pagesInfo[$id][0];
    }
    return null;
  }
  
  // Accepts array('info' => [page info array], all options accepted by getPagesInfo except where)
  
  static public function getChildrenInfo($options)
  {
    $id = $options['info']['id'];
    if (!isset(aPageTable::$childrenInfo[$id]))
    {
      $lft = $options['info']['lft'];
      $rgt = $options['info']['rgt'];
      $level = $options['info']['level'] + 1;
      aPageTable::$childrenInfo[$id] = aPageTable::getPagesInfo(array_merge($options, array('where' => '(( p.lft > ' . $lft . ' AND p.rgt < ' . $rgt . ' ) AND (level = ' . $level . '))')));
    }
    return aPageTable::$childrenInfo[$id];
  }
  
  // Accepts array('info' => [page info array], 'where' => [where clause])
  //
  // Returns results the current user is permitted to see. You can override this if you specify the
  // following options (must specify all or none):
  // 'user_id', 'has_view_locked_permission', 'group_ids', 'has_cms_admin_permission'
  //
  // You can override the user's culture by specifying 'culture'
    
  static public function getPagesInfo($options)
  {
    $whereClauses = array();
    $ignorePermissions = false;
		if (isset($options['ignore_permissions']))
		{
			// getAncestorsInfo has to return everything in some contexts to work properly
			$ignorePermissions = $options['ignore_permissions'];
		}
    if (!isset($options['culture']))
    {
      $options['culture'] = aTools::getUserCulture();
    }
    
    // In the absence of a bc option for page visibility, we can make a better
    // determination based on the user's access rights (1.5)
    
    $joins = '';
    if (isset($options['user_id']))
    {
      // If you pass this in you have to pass all of it in. But if you are just
      // interested in the current user you needn't bother (see the else clause)
      $user_id = $options['user_id'];
      $group_ids = $options['group_ids'];
      if (!count($group_ids))
      {
        // Should never be empty due to IN's limitations
        $group_ids = array(0);
      }
      $hasViewLockedPermission = $options['has_view_locked_permission'];
      $hasCmsAdmin = $options['has_cms_admin_permission'];
    }
    else
    {
      // Get it automatically for the current user
      $user = sfContext::getInstance()->getUser();
      $user_id = 0;
      $hasViewLockedPermission = false;
      $group_ids = array(0);
      $hasCmsAdmin = false;
      if ($user->isAuthenticated())
      {
        $user_id = $user->getGuardUser()->id;
        // In 1.5 this one is a little bit of a misnomer because of the new provision for locking
        // to individuals or groups rather than "Editors & Guests". In the new use case it is
        // merely a prerequisite
        $credentials = sfConfig::get('app_a_view_locked_sufficient_credentials', 'view_locked');
        $hasViewLockedPermission = $user->hasCredential($credentials);
        $group_ids = aArray::getIds($user->getGroups());
        // Careful: empty IN clauses do not work
        if (!count($group_ids))
        {
          $group_ids = array(0);
        }
        $hasCmsAdmin = $user->hasCredential('cms_admin');
      }
    }
        
    $joins .= 'LEFT JOIN a_access aa ON aa.page_id = p.id AND aa.user_id = ' . $user_id . ' ';
    if (!count($group_ids))
    {
      // A group that can never be
      $group_ids = array(0);
    }
    $joins .= 'LEFT JOIN a_group_access ga ON ga.page_id = p.id AND ga.group_id IN (' . implode(',', $group_ids) . ') ';
    $viewLockedClause = '';
    if ($hasViewLockedPermission)
    {
      $viewLockedClause = 'OR p.view_guest IS TRUE ';
    }
    // CMS admin can always view
    if (!$hasCmsAdmin && (!$ignorePermissions))
    {
      // YOU CAN VIEW IF
      // * view_admin_lock is NOT set, AND
      // * You can edit
      // OR
      // p.archived is false AND p.published_at is in the past AND p.view_is_secure is false
      // OR
      // p.archived is false AND p.published_at is in the past AND p.view_is_secure is true AND (p.view_guest is true OR you have view_locked OR you have an explicit view privilege
      // However note that if you have a group privilege you don't need to have hasViewLockedPermission (all groups are candidates)
      $whereClauses[] = '(p.view_admin_lock IS FALSE AND (((aa.privilege = "edit") || (ga.privilege = "edit")) OR ' .
        '((p.archived IS FALSE OR p.archived IS NULL) AND p.published_at < NOW() AND ' .
        '((p.view_is_secure IS FALSE OR p.view_is_secure IS NULL) OR ' .
          '(p.view_is_secure IS TRUE AND ' .
            '(ga.privilege = "view_custom" OR ' . ($hasViewLockedPermission ? '(p.view_guest IS TRUE OR aa.privilege = "view_custom")' : '(0 <> 0)') . '))))))';
    }
    
    if (!isset($options['admin']))
    {
      $options['admin'] = false;
    }
    if (!isset($options['where']))
    {
      throw new sfException("You must specify a where clause when calling getPagesInfo");
    }
    $culture = $options['culture'];
    $admin = $options['admin'];
    $where = $options['where'];
    
    // Raw PDO for performance
    $connection = Doctrine_Manager::connection();
    $pdo = $connection->getDbh();
    // When we look for the current culture, we need to do it in the ON clause, not
    // in the WHERE clause. Otherwise we don't get any information at all about pages
    // not i18n'd yet
    $escCulture = $connection->quote($culture);
    $query = "SELECT p.id, p.slug, p.view_is_secure, p.view_guest, p.view_admin_lock, p.edit_admin_lock, p.archived, p.lft, p.rgt, p.level, p.engine, p.template, s.value AS title FROM a_page p
      LEFT JOIN a_area a ON a.page_id = p.id AND a.name = 'title' AND a.culture = $escCulture
      LEFT JOIN a_area_version v ON v.area_id = a.id AND a.latest_version = v.version 
      LEFT JOIN a_area_version_slot avs ON avs.area_version_id = v.id
      LEFT JOIN a_slot s ON s.id = avs.slot_id $joins";
    // admin pages are almost never visible in navigation
    if (!$admin)
    {
      $whereClauses[] = '(p.admin IS FALSE OR p.admin IS NULL)';
    }
		// Virtual pages are never appropriate for getPagesInfo. Note that privileges for virtual pages
		// are by definition always the responsibility of the code that brought them into being and never
		// based on "normal" page permissions, so getPagesInfo is entirely the wrong API for them
		$whereClauses[] = '(substr(p.slug, 1, 1) = "/")';
    $whereClauses[] = $where;
    $query .= "WHERE " . implode(' AND ', $whereClauses);
    $query .= " ORDER BY p.lft";
    $resultSet = $pdo->query($query);
    // Turn it into an actual array rather than some iterable almost-array thing
    $results = array();
    $seenId = array();
    foreach ($resultSet as $result)
    {
      // Careful: with the new LEFT JOINs on access rights we have extra rows. 
      // Get only one for each page
      if (isset($seenId[$result['id']]))
      {
        continue;
      }
      $seenId[$result['id']] = true;
      // If there is no title yet, supply one to help the translator limp along
      if (!strlen($result['title']))
      {
        if ($result['slug'] === '/')
        {
          $result['title'] = 'home';
        }
        else
        {
          if (preg_match('|([^/]+)$|', $result['slug'], $matches))
          {
            $result['title'] = $matches[1];
          }
        }
      }
      $results[] = $result;
    }
    return $results;
  }
}
