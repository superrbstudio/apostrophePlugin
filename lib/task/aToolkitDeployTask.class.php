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

Call it with:

  [php symfony apostrophe:deploy (staging|production) (staging|prod)|INFO]

You can skip the migration step by adding the --skip-migrate option. This is necessary
if the remote database has just been created or does not exist yet.
  
Note that you must specify both the server nickname and the remote environment name.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $settings = parse_ini_file("config/properties.ini", true);
    if ($settings === false)
    {
      throw new sfException("You must be in a symfony project directory");
    }
    
    $server = $arguments['server'];
    $env = $arguments['env'];
    
    foreach ($settings as $section => $data)
    {
      if ($server === $section)
      {
        $found = true;
        break;   
      }
    }

    if (!$found)
    {
      throw new sfException("First argument must be a server nickname as found in properties.ini (for instance: staging or production)");
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
  }
}
