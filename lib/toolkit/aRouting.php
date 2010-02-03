<?php

class aRouting extends sfPatternRouting
{
  static public function listenToRoutingLoadConfigurationEvent(sfEvent $event)
  {
    $r = $event->getSubject();
    if (sfConfig::get('app_a_routes_register', true) && in_array('a', sfConfig::get('sf_enabled_modules')))
    {
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
  }
}
