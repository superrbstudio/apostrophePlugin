<?php

class aTestFunctional extends sfTestFunctional
{
  public function __construct(sfBrowserBase $browser, lime_test $lime = null, $testers = array())
  {
    parent::__construct($browser, $lime, $testers);
    aTestTools::loadData($this);
  }
  
  public function login($username = 'user_1', $password = null)
  {
    if (!$password)
    {
      if ($username === 'admin')
      {
        $password = 'demo';
      }
      else
      {
        $password = $username;
      }
    }
    $this->
      get('/login')->
      setField('signin[username]', $username)->
      setField('signin[password]', $password)->
      click('sign in', array('_with_csrf' => true));
    echo($this->getResponse()->getContent());
    $this->
      with('response')->begin()->isRedirected()->end()->
        followRedirect()
    ;
  }
  
  // TBB: there is no redirect on a login failure. Test for 
  // expected login failure separately from expected success
  
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
    // submit parent (a slug) and title to aContextCMS/create via POST
    return $this->
      post('/admin/a/create', array('parent' => $parentSlug, 'title' => $pageTitle))->
      with('response')->begin()->
        isRedirected()->followRedirect()->
      end()->
      with('request')->begin()->
        isParameter('module', 'a')->
        isParameter('action', 'show')->
      end();
  }  
}