<?php
/**
 * @package    apostrophePlugin
 * @subpackage    task
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aRefreshTask extends sfBaseTask
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

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
        new sfCommandOption('allversions', 
          sfCommandOption::PARAMETER_NONE)
      ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'refresh';
    $this->briefDescription = 'Refresh metadata in all slots that require it';
    $this->detailedDescription = <<<EOF
The [apostrophe:refresh|INFO] task gives every slot an opportunity to update any cached metadata
it may be retaining. In particular, the media slots query the media server to update all media URLs
they currently contain. For efficiency reasons, media slots contain direct URLs pointing to scaled
and/or original images, PDFs and videos. If you switch frontend controllers from frontend_dev.php
to index.php and switch on no_script_name (i.e. go from a dev to a prod environment) you may find
that images load slower than expected (because index.php is still in old URLs) or fail to load
(because there is no frontend_dev controller on your production server). If you manually click
"Choose Image" and re-confirm the same selection for every slot, the problem goes away. A much
more efficient solution: just run this task, which fetches updated direct URLs for every
media slot.

This task also removes any references to media items that have been deleted. For safety reasons
it does so only if the media item is actually gone, not if the media plugin simply fails to
return a proper response.

For periodic refreshes of slots representing current content, call this task normally to 
quickly refresh only the current versions of slots. For a one-time changeover in which 
every slot should be refreshed, including older versions of slots, add the
--allversions option. This is slower, of course.

Custom slot types can participate in this task by implementing the refreshSlot() method.

Call it with:

  [php symfony a:refresh|INFO]
  
This task defaults to the dev environment and the frontend application. You can override these
with --env=envname and --application=applicationname.
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
    aTaskTools::setCliHost();
    
    // Get all of the current pages with their slots. For efficiency this normally does not return slots 
    // that are not the current version
    
    if ($options['allversions'])
    {
      // All versions case is a simpler query since we want to look at every slot
      $slots = Doctrine::getTable('aSlot')->findAll();
      foreach ($slots as $slot)
      {
        $slot->refreshSlot();
      }
    }
    else
    {
      // All cultures
      $pages = aPageTable::queryWithSlots(false, 'all')->execute();
      foreach ($pages as $page)
      {
        foreach ($page->Areas as $area)
        {
          foreach ($area->AreaVersions as $areaVersion)
          {
            foreach ($areaVersion->AreaVersionSlots as $areaVersionSlot)
            {
              $areaVersionSlot->Slot->refreshSlot();
            }
          }
        }
      }
    }
  }
}
