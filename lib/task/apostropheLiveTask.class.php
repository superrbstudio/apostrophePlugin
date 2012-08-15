<?php
/**
 * @package    apostrophePlugin
 * @subpackage    task
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class apostropheLiveTask extends sfBaseTask
{

  /**
   * DOCUMENT ME
   */
  protected function configure()
  {
    $this->addArguments(array());

    $this->addOptions(array());
    $this->namespace        = 'apostrophe';
    $this->name             = 'live';
    $this->briefDescription = 'Put this site on hold nicely & efficiently via .htaccess directives';
    $this->detailedDescription = <<<EOF
The [apostrophe:live|INFO] task resumes operation of the site. Its counterpart
is apostrophe:standby.

EOF;
  }

  /**
   * DOCUMENT ME
   * @param mixed $arguments
   * @param mixed $options
   */
  protected function execute($arguments = array(), $options = array())
  {
    $path = sfConfig::get('sf_root_dir') . '/web/.htaccess';
    $htaccess = @file_get_contents($path);
    if ($htaccess === false)
    {
      // If they don't have a .htaccess file we are not responsible for suspend/resume
      return;
    }
    $htaccess = preg_replace('/^([^\#].*?apostrophe\-standby.*?$)/m', '#$1', $htaccess);
    file_put_contents($path, $htaccess);
    echo("Site is live\n");
  }
}
