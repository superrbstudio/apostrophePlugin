<?php

class apostropheDeployTask extends sfBaseTask
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
        'The remote server nickname. The server nickname must be defined in properties.ini'),
      new sfCommandArgument('env', 
        sfCommandArgument::REQUIRED, 
        'The remote environment ("staging")')
    ));

    $this->addOptions(array(
      new sfCommandOption('skip-migrate', 
        sfCommandOption::PARAMETER_NONE)
    ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'deploy';
    $this->briefDescription = 'Deploys a site, then performs migrations, cc, etc.';
    $this->detailedDescription = <<<EOF
The [apostrophe:deploy|INFO] task deploys a site to a server, carrying out additional steps after
the core Symfony project:deploy task is complete to ensure success.

It currently invokes:

./symfony project:permissions
./symfony project:deploy servernickname --go

And then, on the remote end via ssh:

./symfony project:after-deploy

Which currently invokes:

./symfony cc
./symfony doctrine:migrate --env=envname
./symfony apostrophe:migrate --env=envname

Call it with:

  [php symfony apostrophe:deploy (staging|production) (staging|prod)|INFO]

You can skip the migration step by adding the --skip-migrate option. This is necessary
if the remote database has just been created or does not exist yet.
  
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
    $env = $arguments['env'];

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

    $eserver = escapeshellarg($server);
    $eenv = escapeshellarg($env);
    $eauth = escapeshellarg($data['user'] . '@' . $data['host']);
    $eport = '';
    if (isset($data['port']))
    {
      $eport .= ' -p' . ($data['port'] + 0);
    }
    system('./symfony project:permissions', $result);
    if ($result != 0)
    {
      throw new sfException('Problem executing project:permissions task.');
    }
    
    system("./symfony project:deploy --go $eserver", $result);
    if ($result != 0)
    {
      throw new sfException('Problem executing project:deploy task.');
    }
    $extra = '';
    if ($options['skip-migrate'])
    {
      $extra .= ' --skip-migrate';
    }
    $epath = escapeshellarg($data['dir']);
    $cmd = "ssh $eport $eauth " . escapeshellarg("(cd $epath; ./symfony apostrophe:after-deploy $extra $eenv)");
    echo("$cmd\n");
    system($cmd, $result);
    if ($result != 0)
    {
      throw new sfException("The remote task returned an error code: $result");
    }
    $this->clearAPCCache($uristem);
  }
  
  protected function getSyncProperty($property, $default = null)
  {
    if (!isset($this->properties['sync'][$property]))
    {
      return $default;
    }
    return $this->properties['sync'][$property];
  }
  
  public function clearAPCCache($uristem)
  {
    if (!isset($this->properties['sync']))
    {
      echo("\n\nWARNING: [sync] properties are not set in properties.ini\n\n");
      echo("You will have to reset Apache manually to clear the APC cache.\n\n");  
    }
    
    if (!$this->getSyncProperty('clear_apc_cache', true))
    {
      return;
    }
    
    $syncPassword = $this->getSyncProperty('password');
    if (!$syncPassword)
    {
      echo("\n\nWARNING: sync_password is not set in properties.ini,\ncannot clear remote APC cache\n\n");
      echo("Fix that, or clear it yourself by restarting Apache.\n\n");
      return;
    }
    $url = "$uristem/async/clearAPCCache?password=" . $syncPassword;
    echo("Accessing $url\n");
    $result = file_get_contents($url);
    if ($result === false)
    {
      echo("\n\nWARNING: fetch of $url failed, APC cache could not be cleared\n\nClear it yourself by restarting Apache.\n\nTo avoid this error set uristem in config/properties.ini\n\n");
      return;
    }
    else
    {
      if (substr($result, 0, 2) === 'OK')
      {
        echo("\n\nRemote APC cache cleared successfully. Response was:\n\n$result\n\n");
      }
      else
      {
        echo("\n\nRemote APC cache did not clear successfully. Response was:\n\n$result\n\n");
      }
    }
  }
}
