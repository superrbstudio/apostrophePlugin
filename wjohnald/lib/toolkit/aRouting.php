<?php

class aRouting extends sfPatternRouting
{
  static public function listenToRoutingLoadConfigurationEvent(sfEvent $event)
  {
    // Removed a redundant check here: the plugin configuration class checks first before registering this handler
    $r = $event->getSubject();
    // 0.13: By default we'll use /cms for pages to avoid compatibility problems with
    // the default routing of other modules. But see the routing.yml of the asandbox
    // project for a better way to do this so your CMS pages (often the point of your site!)
    // don't have to be locked down in a subfolder
    // 0.14: rename this rule a_page and require its use
    $r->prependRoute('a_page', 
      new sfRoute('/cms/:slug', 
        array('module' => 'a', 'action' => 'show'),
        array('slug' => '.*')));
  }
  
  static public function listenToRoutingAdminLoadConfigurationEvent(sfEvent $event)
  {
    $r = $event->getSubject();
    $enabledModules = array_flip(sfConfig::get('sf_enabled_modules', array()));
    if (isset($enabledModules['aUserAdmin']))
    {
      $r->prependRoute('a_user_admin', new sfDoctrineRouteCollection(array('name' => 'a_user_admin',
        'model' => 'sfGuardUser',
        'module' => 'aUserAdmin',
        'prefix_path' => 'admin/user',
        'column' => 'id',
        'with_wildcard_routes' => true)));
    }
    if (isset($enabledModules['aGroupAdmin']))
    {
      $r->prependRoute('a_group_admin', new sfDoctrineRouteCollection(array('name' => 'a_group_admin',
        'model' => 'sfGuardGroup',
        'module' => 'aGroupAdmin',
        'prefix_path' => 'admin/group',
        'column' => 'id',
        'with_wildcard_routes' => true)));
    }
    if (isset($enabledModules['aPermissionAdmin']))
    {
      $r->prependRoute('a_permission_admin', new sfDoctrineRouteCollection(array('name' => 'a_permission_admin',
        'model' => 'sfGuardPermission',
        'module' => 'aPermissionAdmin',
        'prefix_path' => 'admin/permission',
        'column' => 'id',
        'with_wildcard_routes' => true)));
    }
    // Used by apostrophe:deploy to clear the APC cache, needs a consistent path
    if (isset($enabledModules['aSync']))
    {
      $r->prependRoute('a_sync', new sfRoute('/async/:action', array(
        'module' => 'aSync',
        'url' => '/async/:action')));
    }
    // Right now the admin engine isn't terribly exciting,
    // it just redirects away from the /admin page that belongs to it.
    // Longer URLs starting with /admin are left alone as they often belong
    // to non-engine modules like the users module
    if (isset($enabledModules['aAdmin']))
    {
      $r->prependRoute('a_admin', new aRoute('/', array(
        'module' => 'aAdmin',
        'action' => 'index',
        'url' => '/')));
    }
  }
}
