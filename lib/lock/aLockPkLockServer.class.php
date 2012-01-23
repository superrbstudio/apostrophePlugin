<?php

/**
 * Locking support based on pkLockServer, https://github.com/punkave/pkLockServer
 * pkLockServer is a pure PHP network lockserver based on persistent socket
 * connections. Suitable for use in an environment with multiple frontend servers
 * sharing a reliable private network.
 */
 
class aLockPkLockServer implements aLock
{
  protected $pkLockClient;
  protected $namespace;
  
  /**
   * The host and port options are mandatory and must
   * point to a running instance of pkLockServer. The
   * namespace option can be used to distinguish this site
   * from other sites sharing a lockserver. If namespace
   * is not set, the project name setting from properties.ini
   * is used. If the project name setting is missing,
   * a generic namespace is used.
   *
   * @param array $options
   */
   
  public function __construct($options)
  {
    error_log("Constructed aLockPkLockServer\n");
    error_log(json_encode($options));
    if (!isset($options['namespace']))
    {
      // Do our best to distinguish locks intended for different sites based on the
      // name of the project
      $info = parse_ini_file(sfConfig::get('sf_config_dir') . '/properties.ini', true);
      if (isset($info['symfony']['name']))
      {
        $options['namespace'] = $info['symfony']['name'];
      }
      else
      {
        $options['namespace'] = 'generic';
      }
    }
    $this->namespace = $options['namespace'];
    $this->pkLockClient = new pkLockClient($options);
  }

  protected $locks = array();
  
  /**
   * The lock name must be non-empty and must be made up
   * entirely of ASCII alphanumeric characters and underscores.
   *
   * Returns true if the resource is successfully locked
   * exclusively to this PHP request, otherwise false.
   * If $wait is true, waits 30 seconds for the resource
   * to be available, then throws an sfException. If
   * $wait is false, returns true or false immediately based
   * on whether the lock could be obtained immediately or not.
   * "Immediately" should be interpreted reasonably for 
   * a given implementation; of course it can take time
   * to be reasonably certain that you got a lock over a
   * network. But in no event should it wait more than
   * 5 seconds without returning failure.
   */
  public function lock($name, $wait = true)
  {
    $options = array('timeLimit' => $wait ? 30 : 1);
    $result = $this->pkLockClient->lock("{$this->namespace}:$name", $options);
    if ($result)
    {
      $this->locks[] = $name;
      return $result;
    }
    if ($wait)
    {
      throw new sfException("Unable to obtain a lock, another process has the lock or no server is running");
    }
    return false;
  }
  
  /**
   * Releases the most recently obtained lock, if any,
   * belonging to this PHP request.
   * 
   * It is NOT an error to call unlock when you do not
   * currently hold any locks. This simplifies cleanup
   * in many cases.
   */
  public function unlock()
  {
    if (count($this->locks))
    {
      $name = array_pop($this->locks);
      $this->pkLockClient->unlock("{$this->namespace}:$name");
    }
  }
}
