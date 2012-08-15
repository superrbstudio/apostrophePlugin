<?php
/**
 * @package    apostrophePlugin
 * @subpackage    task
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class apostropheStandbyTask extends sfBaseTask
{

  /**
   * DOCUMENT ME
   */
  protected function configure()
  {
    $this->addArguments(array());

    $this->addOptions(array());
    $this->namespace        = 'apostrophe';
    $this->name             = 'standby';
    $this->briefDescription = 'Put this site on hold nicely & efficiently via .htaccess directives';
    $this->detailedDescription = <<<EOF
The [apostrophe:standby|INFO] task suspends the site and directs traffic
to /standby/index.html. Other files in that folder are also delivered, so
assets for the standby mechanism can be kept there. No other URLs on the
server are live. This is done to avoid any and all chicken and egg problems
that may occur during deployment of new code, the clearing of the cache, etc.

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
    $htaccess = preg_replace('/^\#(.*?apostrophe\-standby.*?$)/m', '$1', $htaccess);
    file_put_contents($path, $htaccess);
    echo("Site is on standby\n");
  }
}
