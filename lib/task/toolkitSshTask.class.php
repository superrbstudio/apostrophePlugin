<?php
/**
 * @package    apostrophePlugin
 * @subpackage    task
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class toolkitSsh extends sfBaseTask
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

    $this->addArgument('server', sfCommandArgument::REQUIRED, 'A server name as listed in properties.ini (examples: staging, production)');

    $this->namespace        = 'apostrophe';
    $this->name             = 'ssh';
    $this->briefDescription = 'Opens an interactive ssh connection to the specified server using the username, port and hostname in properties.ini';
    $this->detailedDescription = <<<EOF
The [apostrophe:ssh|INFO] task opens an interactive ssh connection to the specified server, using the
credentials specified in properties.ini. The cd command is used to change the current directory to
the project directory, and then you are given interactive control of the shell. NOTE: uses expect
and prompts you for the ssh password. Not designed for situations where a password is not required.

Call it with:

  [php symfony apostrophePlugin:ssh servername (examples: staging, production)]
  
EOF;
  }

  /**
   * DOCUMENT ME
   * @param mixed $arguments
   * @param mixed $options
   */
  protected function execute($arguments = array(), $options = array())
  {
    $scriptFile = aFiles::getTemporaryFilename();
    $out = fopen($scriptFile, "w");
    $server = $arguments['server'];
    $data = parse_ini_file($this->configuration->getRootDir() . '/config/properties.ini', true);
    if (!isset($data[$server]))
    {
      throw new sfException("$server does not exist in config/properties.ini. Examples: staging, production\n");
    }
    $data = $data[$server];
    $cmd = "ssh ";
    if (isset($data['port']))
    {
      $cmd .= "-p " . $data['port'];
    }
    if (isset($data['user']))
    {
      $cmd .= " -l " . $data['user'];
    }
    $cmd .= " " . $data['host'];
    $dir = $data['dir'];
    $user = $data['user'];
    $host = $data['host'];
    $escapedDir = escapeshellarg($dir);
    $cd = escapeshellcmd("cd $escapedDir");
    fwrite($out, <<<EOM
spawn $cmd
stty -echo
expect password:
send_user -- "Password for $user@$host: "
expect_user -re "(.*)\\n"
send_user "\\n"
stty echo
set password \$expect_out(1,string)
send "\$password\\n"
expect "\\\\$"
send "$cd\\n"
interact 
EOM
    );
    passthru("expect $scriptFile");
    aFiles::unlink($scriptFile);
  }
}
