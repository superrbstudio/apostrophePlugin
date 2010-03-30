<?php

class aTestFunctional extends sfTestFunctional
{
  public function __construct(sfBrowserBase $browser, lime_test $lime = null, $testers = array())
  {
    parent::__construct($browser, $lime, $testers);
    aTestTools::loadData($this);
  }
  
  protected $options = array(
    'login-button-text' => 'Sign In',
    'login-url' => '/login',
    'default-prefix' => '/cms/'
  );
  
  public function setOptions($options = array())
  {
    $this->options = array_merge($this->options, $options);
  }

  // This isn't full-scale routing, it just prepends the appropriate prefix to the
  // URL. That's /cms/ if we're running with the default route as a mere plugin, 
  // or /admin/ if we're running from the sandbox project
  public function route($route)
  {
    return $this->options['default-prefix'] . $route;
  }
  
  public function loadData($path = null)
  {
    if (!$path)
    {
      $path = sfConfig::get('sf_test_dir').'/fixtures';
    }
    
    Doctrine::loadData($path);
 
    return $this;
  }

  public function login($username = 'admin', $password = null)
  {
    if (!$password)
    {
      $password = $username;
    }
    
    return $this->
      get($this->options['login-url'])->
      setField('signin[username]', $username)->
      setField('signin[password]', $password)->
      click($this->options['login-button-text'], array('_with_csrf' => true))->
      with('response')->isRedirected()->
      followRedirect()
    ;
  }
  
  public function loginFailed($username = 'user_1', $password = null)
  {
    if (!$password)
    {
      $password = $username;
    }
    
    return $this->
      get($this->options['login-url'])->
      setField('signin[username]', $username)->
      setField('signin[password]', $password)->
      click('sign in', array('_with_csrf' => true))->
      with('response')->begin()->
        isStatusCode(200)->
        contains('The username and/or password is invalid')->
      end()
    ; 
  }

  public function createPage($parentSlug, $pageTitle)
  {
    // submit parent (a slug) and title to aContextCMS/create via POST
    return $this->
      post($this->route('a/create'), array('parent' => $parentSlug, 'title' => $pageTitle))->
      with('response')->begin()->
        isRedirected()->followRedirect()->
      end()->
      with('request')->begin()->
        isParameter('module', 'a')->
        isParameter('action', 'show')->
      end();
  }
}
