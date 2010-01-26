<?php

// A helper class containing methods to be called from subclasses of sfRoute that are
// intended for use with a engines. Keeping this code here minimizes duplication
// and avoids the need for frequent changes to multiple classes when this code is modified.
// This is poor man's multiple inheritance. See aRoute and aDoctrineRoute

class aRouteTools
{
  /**
   * Returns the portion of the URL after the engine page slug, or false if there
   * is no engine page matching the URL. As a special case, if the URL exactly matches the slug,
   * / is returned.
   *
   * @param  string  $url     The URL
   *
   * @return string The remainder of the URL
   */
  static public function removePageFromUrl(sfRoute $route, $url)
  {
    $remainder = false;
    // Modifies $remainder if it returns a matching page
    $page = aPageTable::getMatchingEnginePage($url, $remainder);
    if (!$page)
    {
      return false;
    }
    // Engine pages can't have subpages, so if the longest matching path for any engine page
    // has the wrong engine type for this route, this route definitely doesn't match
    $defaults = $route->getDefaults();
    if ($page->engine !== $defaults['module'])
    {
      return false;
    }
    // Allows aRoute URLs to be written like ordinary URLs rather than
    // specifying an empty URL, which seems prone to lead to incompatibilities
    
    // Remainder comes back as false, not '', for an exact match
    if (!strlen($remainder))
    {
      $remainder = '/';
    }
    return $remainder;
  }
  
  /**
   * Prepends the current CMS page to the URL.
   *
   * @param  string $url The URL so far obtained from parent::generate
   * @param  Boolean $absolute  Whether to generate an absolute URL
   *
   * @return string The generated URL
   */
  
  static public function addPageToUrl(sfRoute $route, $url, $absolute)
  {
    $defaults = $route->getDefaults();
    $page = aTools::getCurrentPage();
    if ((!$page) || ($page->engine !== $defaults['module']))
    {
      $page = aPageTable::getFirstEnginePage($defaults['module']);
      if (!$page)
      {
        throw new sfException('Attempt to generate aRoute URL for module ' . $defaults['module'] . ' with no matching engine page on the site');
      }
    }
    // A route URL of / for an engine route maps to the page itself, without a trailing /
    if ($url === '/')
    {
      $url = '';
    }
    // Ditto for / followed by a query string (missed this before)
    if (substr($url, 0, 2) === '/?')
    {
      $url = substr($url, 1);
    }
    $pageUrl = $page->getUrl($absolute);
    // Strip controller off so it doesn't duplicate the controller in the 
    // URL we just generated. We could use the slug directly, but that would
    // break if the CMS were not mounted at the root on a particular site.
    // Take care to function properly in the presence of an absolute URL
    if (preg_match("/^(https?:\/\/[^\/]+)?\/[^\/]+\.php(.*)$/", $pageUrl, $matches))
    {
      $pageUrl = $matches[2];
    }
    return $pageUrl . $url;
  }
}
