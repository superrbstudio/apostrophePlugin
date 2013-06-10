<?php
/**
 * @package    apostrophePlugin
 * @subpackage    task
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aMirrorForSearchTask extends sfBaseTask
{

  /**
   * DOCUMENT ME
   */
  protected function configure()
  {
    // add your own arguments here
    $this->addArguments(array(
      new sfCommandArgument('model', sfCommandArgument::REQUIRED, 'My model class'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
        new sfCommandOption('allversions', 
          sfCommandOption::PARAMETER_NONE)
      ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'mirror-for-search';
    $this->briefDescription = 'Mirror specified model class for search';
    $this->detailedDescription = <<<EOF
The [apostrophe:mirror-for-search|INFO] task mirrors the specified model
class for search. In order for this to work the model class must
be implemented according to:

http://trac.apostrophenow.org/wiki/ManualDevelopersGuide#ExtendingSearch

  [php symfony apostrophe:mirror-for-search classname|INFO]

Typically you will only need this task once, and only when upgrading
an existing class to include search mirroring. Later edits should be
automatically mirrored if you implement it correctly.

This task can take a long time and will run out of memory if there are
many objects unless you increase memory_limit quite a bit. It could
be refactored to use multiple invocations.
EOF;
  }

  /**
   * DOCUMENT ME
   * @param mixed $arguments
   * @param mixed $options
   */
  protected function execute($arguments = array(), $options = array())
  {
    // We need a proper environment. This also gives us superadmin privileges
    aTaskTools::signinAsTaskUser($this->createConfiguration($options['application'], $options['env']), $options['connection']);

    $aPageTable = Doctrine::getTable('aPage');
    $objects = Doctrine::getTable($arguments['model'])->findAll();
    foreach ($objects as $object)
    {
      $aPageTable->mirrorForSearch($object);
    }
  }
}
