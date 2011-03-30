<?php
/**
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaGroupAdminForm extends sfGuardGroupForm
{

  /**
   * DOCUMENT ME
   * @param mixed $object
   * @param mixed $options
   * @param mixed $CSRFSecret
   */
  public function __construct($object = null, $options = array(), $CSRFSecret = null)
  {
    parent::__construct($object, $options, $CSRFSecret);
    // Runs AFTER updateDefaultsFromObject()
    $this->postConfigure();
  }

  /**
   * DOCUMENT ME
   */
  public function configure()
  {
    parent::configure();
    // Not scalable
    unset($this['users_list']);
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    $this->widgetSchema->setHelp('permissions_list', 'The default permissions are appropriate for a group that will be given editing privileges somewhere in the site. Members of the group will be able to author blog posts, upload media and view locked pages but will not have editing privileges for pages until you specifically grant them to that group via "This Page."');
  }

  /**
   * DOCUMENT ME
   */
  public function postConfigure()
  {
    if ($this->getObject()->isNew())
    {
      $permissionIds = array();
      $permissionNames = array(sfConfig::get('app_a_group_editor_permission', 'editor'), aMediaTools::getOption('upload_credential'), 'blog_author', 'view_locked');
      foreach ($permissionNames as $permissionName)
      {
        $permission = Doctrine::getTable('sfGuardPermission')->findOneByName($permissionName);
        if ($permission)
        {
          $permissionIds[] = $permission->id;
        }
      }
      $this->setDefault('permissions_list', $permissionIds);
    }
  }
  
}
