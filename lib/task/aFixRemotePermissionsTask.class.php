<?php

class aFixRemotePermissionsTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addArguments(array(
      new sfCommandArgument('server',
        sfCommandArgument::REQUIRED, 
        'The remote server nickname. The server nickname must be defined in properties.ini')
    ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'fix-remote-permissions';
    $this->briefDescription = 'Fixes permissions on a remote server via the webserver.';
    $this->detailedDescription = <<<EOF
The [apostrophe:deploy|INFO] task fixes permissions on a remote server via the webserver
itself by invoking a Symfony action on the server to do so.

This task currently fixes permissions in web/uploads and data/a_writable (or whatever they
have been overridden to be).

uristem must be set in properties.ini in the section for the server in question, and also 
the password field in the [sync] section. If the default routing does not make the
async module visible at /admin/async (followed by /action) you should also set the prefix 
option in the [sync] section to the appropriate prefix.
  
Call it with:

  [php symfony apostrophe:fix-remote-permissions (staging|production)|INFO]

Note that you must specify both the server nickname and the remote environment name.
EOF;
  }

  // properties.ini 
  protected $properties;
  
  protected function execute($arguments = array(), $options = array())
  {
    $this->properties = parse_ini_file("config/properties.ini", true);
    
    if ($this->properties === false)
    {
      throw new sfException("You must be in a symfony project directory");
    }
    
    
    $server = $arguments['server'];

    // Why did I think properties.ini wouldn't load as a hash of hashes? 
    // Sigh this is much simpler
    if (!isset($this->properties[$server]))
    {      
      throw new sfException("First argument must be a server nickname as found in properties.ini (for instance: staging or production)");
    }

    // Sometimes the ssh host and the actual site URL differ. Sometimes
    // the actual site URL involves https://. Etc.
    
    // NO TRAILING SLASH on this properties.ini setting please
    
    $data = $this->properties[$server];
    if (isset($data['uristem']))
    {
      $uristem = $data['uristem'];
    }
    else
    {
      $uristem = 'http://' . $data['host'];
    }
    
    if (isset($this->properties['sync']['password']))
    {
      $syncPassword = $this->properties['sync']['password'];
    }
    else
    {
      $syncPassword = '';
    }
    
    if (isset($this->properties['sync']['prefix']))
    {
      $uristem .= $this->properties['sync']['prefix'];
    }
    else
    {
      $uristem .= '/admin/aSync';
    }

    $url = $uristem . '/fixPermissions?password=' . $syncPassword;
    echo("Accessing $url\n");
    $result = file_get_contents($url);
    if ($result === false)
    {
      echo("\n\nWARNING: fetch of $url failed, remote permissions could not be reset\n\n");
    }
    else
    {
      if (substr($result, 0, 2) === 'OK')
      {
        echo("\n\nRemote permissions reset successfully. Response was:\n\n$result\n\n");
      }
      else
      {
        echo("\n\nRemote permissions not reset successfully. Response was:\n\n$result\n\n");
      }
    }
  }
}
