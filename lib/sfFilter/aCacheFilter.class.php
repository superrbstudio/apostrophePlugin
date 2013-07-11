<?php

/**
 *
 * THE EASY PART (just requires app.yml and filters.yml settings):
 *
 * Cache all actions for 5 minutes provided that:
 * 1. app_a_page_cache_enabled is set to true (defaults false)
 * 2. The user is not logged in
 * 3. The request is not a POST request
 * 4. The request is not an AJAX request (pages are rendered without layout on AJAX)
 * 5. The response does not contain _csrf_token
 * 6. The programmer has not explicitly set aCacheInvalid as a flash or regular user attribute
 * This has the right semantics to work with most Apostrophe sites.
 *
 * Non-success responses are never cached.
 *
 * THE HARD BUT AWESOME PART: CACHE HEADERS (CDN friendly, but requires bigger changes):
 *
 * If app_a_page_cache_set_headers is true then in addition to the local cache on the server,
 * cache headers are set requesting the same caching behavior from browsers, reverse
 * proxies, CDNs, etc. This is great (you *may* even withstand the Oprah effect if you
 * work with CloudFlare or a similar service), but it can prevent users from logging in
 * because their browser is caching the home page. To fix that, we never cache traffic 
 * to the host specified by app_a_login_host. You must educate your editors to log in to
 * that special subdomain and/or override the login action to redirect there. You 
 * must also configure Apache to accept the app_a_login_host name as a
 * ServerAlias.
 *
 * When using app_a_page_cache_set_headers, sessions are NOT available at all
 * for logged-out users. They are enabled only on the editing host. This restriction
 * does not apply if you're not using the headers feature.
 *
 * To properly prevent unwanted Pragma: no-cache headers on the public host but
 * keep them on the editing host, you must also switch session storage classes
 * in factories.yml:
 *
 *
 * all:
 *
 *   storage:
 *     class: aSessionStorageIfEditingHost
 *     param:
 *       session_name: symfony
 *       auto_start: true
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
    $enabled = sfConfig::get('app_a_page_cache_enabled', false);
    $editingHost = ($this->context->getRequest()->getHost() === sfConfig::get('app_a_page_cache_editing_host', 'none'));

    if ((!$enabled) || $editingHost)
    {
      $filterChain->execute();
      return;
    }
    $sfUser = $this->context->getUser();
    $request = $this->context->getRequest();
    $uri = $request->getUri();
    // Check for the aCacheInvalid override both before and after content gets generated
    if ($sfUser->isAuthenticated() || ($request->getMethod() !== 'GET') || $request->isXmlHttpRequest() || ($sfUser->getFlash('aCacheInvalid', false)) || ($sfUser->getAttribute('aCacheInvalid', false)))
    {
      $filterChain->execute();
      return;
    }

    $cache = aCacheFilter::getCache();
    $content = $cache->get($uri, null);

    $lifetime = sfConfig::get('app_a_page_cache_lifetime', 300);
    
    if (!is_null($content))
    {
      // TODO: this potentially doubles the effective cache lifetime because
      // we're not paying attention to how much of it has already passed by.
      // Fixing that requires adding a new method to aMysqlCache and aMongoDBCache
      // which is used, if available, to get the item *and* the remaining seconds
      // in its cache lifetime. But this is considerably more work, and a slightly longer
      // cache lifetime is not a big problem in practice, especially if all the traffic
      // is from a well-run reverse proxy anyway in which case this shouldn't
      // even come up.
      if (sfConfig::get('app_a_page_cache_set_headers', false)) 
      {
        $this->context->getResponse()->addCacheControlHttpHeader('max_age=' . $lifetime);
      }
      $this->context->getResponse()->setContent($content);
    }
    else
    {
      $filterChain->execute();
      // Never try to cache a 404 error. Later we might consider
      // caching them but remembering their status properly so it 
      // doesn't magically become a 200 OK when returned from cache
      if ($this->context->getResponse()->getStatusCode() !== 200)
      {
        return;
      }
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
      $cache->set($uri, $this->context->getResponse()->getContent(), $lifetime);
      // Optionally also set cache headers so that browsers and servers
      // (such as reverse proxies and CDNs like cloudflare) can cache the content
      // for us, reducing the overhead to zero for cache hits. 
      if (sfConfig::get('app_a_page_cache_set_headers', false)) 
      {
        $this->context->getResponse()->addCacheControlHttpHeader('max_age=' . $lifetime);
      }
    }
  }
  
  static public function getCache()
  {
    return aCacheTools::get('page');
  }
}
