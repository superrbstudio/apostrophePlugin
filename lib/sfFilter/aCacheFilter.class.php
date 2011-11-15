<?php

/**
 * Cache all actions for 5 minutes provided that:
 * 1. app_a_page_cache_enabled is set to true (defaults false)
 * 2. The user is not logged in
 * 3. The request is not a POST request
 * 4. The response does not contain _csrf_token
 * 5. The programmer has not explicitly set aCacheInvalid as a flash or regular user attribute
 * This has the right semantics to work with most Apostrophe sites 
 */
class aCacheFilter extends sfFilter
{
  /**
   * Executes the filter chain.
   *
   * @param sfFilterChain $filterChain
   */
  public function execute($filterChain)
  {
    if (!sfConfig::get('app_a_page_cache_enabled', false))
    {
      $filterChain->execute();
      return;
    }
    $sfUser = $this->context->getUser();
    $uri = $this->context->getRequest()->getUri();
    // Check for the aCacheInvalid override both before and after content gets generated
    if ($sfUser->isAuthenticated() || ($this->context->getRequest()->getMethod() !== 'GET') || ($sfUser->getFlash('aCacheInvalid', false)) || ($sfUser->getAttribute('aCacheInvalid', false)))
    {
      $filterChain->execute();
      return;
    }

    $cache = aCacheFilter::getCache();
    $content = $cache->get($uri, null);
    if (!is_null($content))
    {
      $this->context->getResponse()->setContent($content);
    }
    else
    {
      $filterChain->execute();
      $content = $this->context->getResponse()->getContent();
      // Check whether aCacheInvalid was set for this user during the current request, don't cache
      // if it was
      if ($sfUser->getFlash('aCacheInvalid', false))
      {
        return;
      }
      if ($sfUser->getAttribute('aCacheInvalid', false))
      {
        return;
      }
      // Never cache anything with a CSRF token as it won't work (you should remove CSRF tokens from
      // forms that can't do anything negative/embarrassing/privacy-wrecking/spammy without information
      // a CSRF attacker won't have; for instance you don't need CSRF for a login form because the
      // spammer doesn't know your password)
      if (strstr($content, '_csrf_token') !== false)
      {
        return;
      }
      $cache->set($uri, $this->context->getResponse()->getContent(), sfConfig::get('app_a_page_cache_lifetime', 300));
    }
  }
  
  static public function getCache()
  {
    return aCacheTools::get('page');
  }
}
