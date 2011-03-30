<?php
/**
 * 
 * aUserAdmin module configuration.
 * @package    sfShibbolethPlugin
 * @subpackage aUserAdmin
 * @author     Alex Gilbert
 * @version    SVN: $Id: aUserAdminGeneratorConfiguration.class.php 12896 2008-11-10 19:02:34Z fabien $
 */
class aUserAdminGeneratorConfiguration extends BaseaUserAdminGeneratorConfiguration
{

  /**
   * DOCUMENT ME
   */
  private function i18nDummy()
  {
    __('<p>If you want to grant a user the ability to edit a portion of the site, first add them to the <b>editor</b> group.</p><p>Then browse to that area of the site and click the gear to add them to the list of users who can edit in that particular area.</p><p>If you want a user to have full control over the entire site, add them to the <b>admin</b> group.</p>', null, 'apostrophe');
    __('New', null, 'apostrophe');
    __('Cancel', null, 'apostrophe');
    __('Save and add', null, 'apostrophe');
  }
}
