<?php
/**
 * @package    apostrophePlugin
 * @subpackage    test
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aTestFunctional extends sfTestFunctional
{

  /**
   * DOCUMENT ME
   * @param sfBrowserBase $browser
   * @param lime_test $lime
   * @param mixed $testers
   */
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

  /**
   * DOCUMENT ME
   * @param mixed $options
   */
  public function setOptions($options = array())
  {
    $this->options = array_merge($this->options, $options);
  }

  /**
   * This isn't full-scale routing, it just prepends the appropriate prefix to the
   * URL. That's /cms/ if we're running with the default route as a mere plugin,
   * or /admin/ if we're running from the sandbox project
   * @param mixed $route
   * @return mixed
   */
  public function route($route)
  {
    return $this->options['default-prefix'] . $route;
  }

  /**
   * DOCUMENT ME
   * @param mixed $path
   * @return mixed
   */
  public function loadData($path = null)
  {
    if (!$path)
    {
      $path = sfConfig::get('sf_test_dir').'/fixtures';
    }
    
    Doctrine::loadData($path);
 
    return $this;
  }

  /**
   * DOCUMENT ME
   * @param mixed $username
   * @param mixed $password
   * @return mixed
   */
 public function login($username = 'admin', $password = null)
  {
    if (!$password)
    {
      $password = $username;
    }
    
    // Due to the use of javascript based login buttons we have to
    // just submit the form. Thanks to George on the google group
    return $this->
      get($this->options['login-url'])->
      post('/login', array('signin'=> array('username' => $username,
 'password' => $password)))->
     with('response')->
      isStatusCode(302)->
        followRedirect()
    ;

  }

  /**
   * DOCUMENT ME
   * @param mixed $username
   * @param mixed $password
   * @return mixed
   */
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
      click($this->options['login-button-text'], array('_with_csrf' => true))->
      with('response')->begin()->
        isStatusCode(200)->
      end()->
      with('form')->begin()->
        hasErrors()->
      end()
    ; 
  }

  /**
   * DOCUMENT ME
   * @param mixed $parentSlug
   * @param mixed $pageTitle
   * @return mixed
   */
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
