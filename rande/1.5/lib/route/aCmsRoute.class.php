<?php
/*
 * This file is part of Apostrophe
 * (c) 2009 P'unk Avenue LLC, www.punkave.com
 */

/**
 * @package    apostrophePlugin
 * @subpackage Tasks
 * @author     Thomas Rabaix <thomas.rabaix@ekino.com>
 */
class aCmsRoute extends sfDoctrineRoute
{

  static $cache_codes = null;

  static $cache_slug = array();
    
  protected $page;


  /**
   * Returns true if the parameters matches this route, false otherwise.
   *
   * @param  mixed  $params  The parameters
   * @param  array  $context The context
   *
   * @return Boolean         true if the parameters matches this route, false otherwise.
   */
  public function matchesParameters($params, $context = array())
  {
    return parent::matchesParameters('object' == $this->options['type'] ? $this->convertObjectToArray($params) : $params);
  }

  /**
   * Returns an array of parameters if the URL matches this route, false otherwise.
   *
   * @param  string  $url     The URL
   * @param  array   $context The context
   *
   * @return array   An array of parameters
   */
  public function matchesUrl($slug, $context = array())
  {

    if (substr($slug, 0, 1) !== '/')
    {
      $slug = '/'.$slug;
    }

    if(!isset(self::$cache_slug[$slug])) {
        self::$cache_slug[$slug] = aPageTable::retrieveBySlugWithSlots($slug);
    }

    $this->page = self::$cache_slug[$slug];

    if($this->page && $this->page->skip_on_url_match)
    {
      return false;
    }

    return $this->page ? array('module' => 'a', 'action' => 'show') : parent::matchesUrl($slug, $context);
  }

  public function getObject()
  {
    return $this->getPage();
  }

  public function getPage()
  {

    return $this->page;
  }

  public static function loadCodeInformation()
  {
    if(self::$cache_codes === null)
    {
      self::$cache_codes = Doctrine_Query::create()
        ->from('aPage p INDEXBY code')
        ->select('p.code, p.slug')
        ->where('p.code IS NOT NULL AND p.code <> ""')
        ->fetchArray();
    }

    return self::$cache_codes;
  }

  public function generate($params, $context = array(), $absolute = false)
  {
    $params = $this->convertObjectToArray($params);

    if (!$this->compiled)
    {
      $this->compile();
    }

    if(isset($params['code']))
    {
      $this->loadCodeInformation();

      if(array_key_exists($params['code'], self::$cache_codes))
      {
        $params['slug'] = substr(self::$cache_codes[$params['code']]['slug'], 1);
      }
      else
      {
        $params['slug'] = 'page-does-not-exists';
      }

      unset($params['code']);
    }

    $url = $this->pattern;

    $defaults = $this->mergeArrays($this->getDefaultParameters(), $this->defaults);
    $tparams = $this->mergeArrays($defaults, $params);

    // all params must be given
    if ($diff = array_diff_key($this->variables, $tparams))
    {
      throw new InvalidArgumentException(sprintf('The "%s" route has some missing mandatory parameters (%s).', $this->pattern, implode(', ', $diff)));
    }

    if ($this->options['generate_shortest_url'] || $this->customToken)
    {
      $url = $this->generateWithTokens($tparams);
    }
    else
    {
      // replace variables
      $variables = $this->variables;
      uasort($variables, create_function('$a, $b', 'return strlen($a) < strlen($b);'));
      foreach ($variables as $variable => $value)
      {
        if($variable == 'slug')
        {

          $url = str_replace($value, $tparams[$variable], $url);
        }
        else
        {
          $url = str_replace($value, urlencode($tparams[$variable]), $url);
        }

      }

      if(!in_array($this->suffix, $this->options['segment_separators']))
      {
        $url .= $this->suffix;
      }
    }

    // replace extra parameters if the route contains *
    $url = $this->generateStarParameter($url, $defaults, $tparams);

    if ($this->options['extra_parameters_as_query_string'] && !$this->hasStarParameter())
    {
      // add a query string if needed
      if ($extra = array_diff_key($params, $this->variables, $defaults))
      {
        $url .= '?'.http_build_query($extra);
      }
    }

    return $url;
  }

  /**
   * Generates a URL for the given parameters by using the route tokens.
   *
   * @param array $parameters An array of parameters
   */
  protected function generateWithTokens($parameters)
  {
    $url = array();
    $optional = $this->options['generate_shortest_url'];
    $first = true;
    $tokens = array_reverse($this->tokens);
    foreach ($tokens as $token)
    {
      switch ($token[0])
      {
        case 'variable':
          if (!$optional || !isset($this->defaults[$token[3]]) || $parameters[$token[3]] != $this->defaults[$token[3]])
          {
            if($token[3] == 'slug')
            {
              if(substr($parameters['slug'], 0, 1) == '/')
              {
                $parameters['slug'] = substr($parameters['slug'], 1);
              }
              $url[] = $parameters['slug'];
            }
            else
            {
              $url[] = urlencode($parameters[$token[3]]);
            }

            $optional = false;
          }
          break;
        case 'text':
          $url[] = $token[2];
          $optional = false;
          break;
        case 'separator':
          if (false === $optional || $first)
          {
            $url[] = $token[2];
          }
          break;
        default:

          // handle custom tokens
          if ($segment = call_user_func_array(array($this, 'generateFor'.ucfirst(array_shift($token))), array_merge(array($optional, $parameters), $token)))
          {
            $url[] = $segment;
            $optional = false;
          }
          break;
      }

      $first = false;
    }

    $url = implode('', array_reverse($url));
    if (!$url)
    {
      $url = '/';
    }

    return $url;
  }
}
