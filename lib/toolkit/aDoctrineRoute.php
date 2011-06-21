<?php

// Used by engine pages.

class aDoctrineRoute extends sfDoctrineRoute 
{
  public function __construct($pattern, array $defaults = array(), array $requirements = array(), array $options = array())
  {
    parent::__construct($pattern, $defaults, $requirements, $options);  
  }

  /**
   * Returns true if the URL matches this route, false otherwise.
   *
   * @param  string  $url     The URL
   * @param  array   $context The context
   *
   * @return array   An array of parameters
   */
  public function matchesUrl($url, $context = array())
  {
   $url = aRouteTools::removePageFromUrl($this, $url);
   // No engine page found
   if ($url === false)
   {
     return false;
   }
   return parent::matchesUrl($url, $context);
  }

  /**
   * Generates a URL from the given parameters.
   *
   * @param  mixed   $params    The parameter values
   * @param  array   $context   The context
   * @param  Boolean $absolute  Whether to generate an absolute URL
   *
   * @return string The generated URL
   */
  public function generate($params, $context = array(), $absolute = false)
  {
    $slug = null;
    $defaults = $this->getDefaults();

    if (isset($params['sf_subject']) && (!isset($params['engine-slug'])))
    {
      // Don't override the current page if it is an engine, or a previously
      // pushed engine page
      $slug = aRouteTools::getContextEngineSlug($this);
      if ($slug)
      {
        $params['engine-slug'] = $slug;
      }
      else
      {
        if (method_exists($params['sf_subject'], 'getEngineSlug'))
        {
          $params['engine-slug'] = $params['sf_subject']->getEngineSlug();
        }
      }
    }

    if (isset($params['engine-slug']))
    {
      $slug = $params['engine-slug'];
      aRouteTools::pushTargetEngineSlug($slug, $defaults['module']);
      unset($params['engine-slug']);
    } 
    $result = aRouteTools::addPageToUrl($this, parent::generate($params, $context, false), $absolute);
    if ($slug)
    {
      aRouteTools::popTargetEngine($defaults['module']);
    }
    return $result;
  } 
  
  
  /**
   * Check to see if the buffer is '.:sf_format' and if so we can correctly
   * parse out the separator token ('.') even if it directly follows another
   * separator. Thanks to stereoscott.
   *
   * This method must return false if the buffer has not been parsed.
   *
   * @param string   $buffer           The current route buffer
   * @param array    $tokens           An array of current tokens
   * @param Boolean  $afterASeparator  Whether the buffer is just after a separator
   * @param string   $currentSeparator The last matched separator
   *
   * @return Boolean true if a token has been generated, false otherwise
   */
  protected function tokenizeBufferBefore(&$buffer, &$tokens, &$afterASeparator, &$currentSeparator)
  {
    if ($buffer === '.:sf_format') {
      $tokens[] = array('separator', $currentSeparator, '.', null);
      $currentSeparator = '.';
      $buffer = substr($buffer, 1);
      $afterASeparator = true;
      return true;
    } else {
      return false;
    }
  }
}
