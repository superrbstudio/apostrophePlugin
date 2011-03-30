<?php
/**
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aMediaCMSSlotsTools
{

  /**
   * You too can do this in a plugin dependent on a, see the provided stylesheet
   * for how to correctly specify an icon to go with your button. See the
   * apostrophePluginConfiguration class for the registration of the event listener.
   */
  static public function getGlobalButtons()
  {
    // Only if we have suitable credentials
    if (aMediaTools::userHasUploadPrivilege())
    {
      aTools::addGlobalButtons(array(
        new aGlobalButton('media', 'Media', 'aMedia/index', 'a-media', '/admin/media', 'aMedia')));
    }
  }

  /**
   * DOCUMENT ME
   */
  static private function i18nDummy()
  {
    __('Media');
  }
}
