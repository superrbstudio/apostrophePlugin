<?php
/**
 * @package    apostrophePlugin
 * @subpackage    task
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class apostropheDeployTask extends sfBaseTask
{

  /**
   * DOCUMENT ME
   */
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

  /**
   * DOCUMENT ME
   * @param mixed $arguments
   * @param mixed $options
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->properties = parse_ini_file("config/properties.ini", true);
    
    if ($this->properties === false)
    {
      throw new sfException("You must be in a symfony project directory");
    }
    
    // If we're using svn, make sure we're using it responsibly.
    // Don't break for people who don't do svn
    if (file_exists('.svn'))
    {
      echo("\n\n\nDID YOU RUN SVN UPDATE? DO YOU HAVE YOUR COWORKERS' CONTRIBUTIONS?\n\n\n");
      // We could force this, but it could make apostrophe:deploy too annoying to use,
      // similar to what happened with pkcommit
      // passthru("svn update");
      
      $xml = new SimpleXMLElement(`svn status --xml`);
      $warn = 0;
      if ($xml->xpath("//wc-status[@item='modified']"))
      {
        echo("WARNING: YOU HAVE MODIFIED FILES NOT COMMITTED TO SVN\n");
        $warn++;
      }
      if ($xml->xpath("//wc-status[@item='missing']"))
      {
        echo("WARNING: YOU HAVE MISSING FILES ACCORDING TO SVN\n");
        $warn++;
      }
      if ($xml->xpath("//wc-status[@item='unversioned']"))
      {
        echo("WARNING: YOU HAVE NEW FILES NOT ADDED TO SVN\n");
        $warn++;
      }
      if ($warn)
      {
        if (!$this->askConfirmation(
    "You really should quit now and address your svn issues.\n" .
    "Are you sure you want to deploy anyway? [y/N]",
          'QUESTION_LARGE',
          false))
        {
          fwrite(STDERR, "Operation CANCELLED. No changes made.\n");
          exit(1);
        }
      }
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
    
    // -cI: Use checksum rather than timestamp/size. The timestamp check is only good if the same developer always
    // does the rsync, otherwise everything always appears to be new in lib/vendor, which is disastrous. The
    // checksum is "slow" because it md5's every file, however in practice it's not very slow at all because
    // CPUs are fast these days and our projects aren't all THAT big.
    
    // --no-t: Don't preserve timestamp. That way we don't have to clear the APC cache after every sync, because APC
    // can tell our code is new
    
    $cmd = "./symfony project:deploy --rsync-options=\"-azvCcI --no-t --force --delete --progress --exclude-from=config/rsync_exclude.txt\" --go $eserver";
    system($cmd, $result);
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
  }
}
