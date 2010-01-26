<?php

class aSyncActions extends sfActions
{
  protected $sync;
  protected function get($param, $default = null)
  {
    if (!isset($this->sync))
    {
      $settings = parse_ini_file(sfConfig::get('sf_root_dir') . "/config/properties.ini", true);
      if ($settings === false)
      {
        throw new sfException("Cannot find config/properties.ini");
      }
    
      if (!isset($settings['sync']))
      {
        throw new sfException('sync section not configured in properties.ini');
      }      
      $this->sync = $settings['sync'];
    }
    
    if (!isset($this->sync[$param]))
    {
      return $default;
    }
    return $this->sync[$param];
  }

  // preExecute validates the password before allowing anything to be done
  // (this module is intended to grow to encompass PHP-based code syncing like
  // what I did for Pressroom for future cloud sites; right now it just has a
  // method to clear the APC cache)
    
  public function preExecute()
  {
    $syncPassword = $this->get('password');
    if (!$syncPassword)
    {
      throw new sfException('Sync password is not set, sync module disabled');
      return;
    }    
    if ($this->getRequestParameter('password') !== $syncPassword)
    {
      throw new sfException('Bad sync password');
    }
    // We want to give back script-friendly responses
    $this->setLayout(false);
  }
  public function executeClearAPCCache(sfWebRequest $request)
  {
    if (!$this->get('clear_apc_cache', true))
    {
      throw new sfException('APC cache clear feature is disabled in app.yml');
    }
    if (function_exists('apc_clear_cache'))
    {
      apc_clear_cache();
    }
    else
    {
      // This is NOT an error, it just means there is no APC on this site anyway,
      // so there is no potential for cache-related problems
      return 'NotActive';
    }
  }
}