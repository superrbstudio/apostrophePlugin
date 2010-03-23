<?php

class aTestFunctional extends sfTestFunctional
{
  protected $loginButtonText = 'sign in';

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
      get('/login')->
      setField('signin[username]', $username)->
      setField('signin[password]', $password)->
      click($this->loginButtonText)->
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
      get('/login')->
      setField('signin[username]', $username)->
      setField('signin[password]', $password)->
      click('sign in')->
      with('response')->begin()->
        isStatusCode(200)->
        contains('The username and/or password is invalid')->
      end()
    ; 
  }

  public function createPage($parentSlug, $pageTitle)
  {
    // submit parent (a slug) and title to a/create via POST
    return $this->
      post('/cms/a/create', array('parent' => $parentSlug, 'title' => $pageTitle))->
      isRedirected()->followRedirect()->
      isRequestParameter('module', 'a')->
      isRequestParameter('action', 'show');
  }
}
