<?php
/**
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaUserAdminForm extends sfGuardUserAdminForm
{

  /**
   * DOCUMENT ME
   */
  public function configure()
  {
    parent::configure();
    // Easy to override
    $this->useFields($this->getUseFields());
    
    unset($this['is_super_admin']);

    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    // It's convenient to kill these when using Shibboleth
    if (!sfConfig::get('app_a_user_admin_password', true))
    {
      unset($this['password']);
      unset($this['password_again']);
    }
    if (!sfConfig::get('app_a_user_admin_is_active', true))
    {
      unset($this['is_active']);
    }
    // Handing out permissions directly is usually a mistake, use groups and
    // restrict full permissions admin to the superadmin
    if (!sfConfig::get('app_a_user_admin_permissions', false))
    {
      unset($this['permissions_list']);
    }

    $this->getWidget('groups_list')->setOption('query', Doctrine::getTable('sfGuardGroup')->createQuery()->orderBy('sfGuardGroup.name asc'));
    $this->getWidget('categories_list')->setOption('query', Doctrine::getTable('aCategory')->createQuery()->orderBy('aCategory.name asc'));

    $this->widgetSchema->setHelp('groups_list', 'If you want to grant a user the ability to edit a portion of the site as an individual, first add them to the editor group. Then browse to that area of the site and click Page Settings to add them to the list of users who can edit in that particular area. You can also add them to a group that has the Editor permission and grant that group editing privileges anywhere in the site. If you want a user to have full control over the entire site, add them to the admin group.');
    $this->widgetSchema->setHelp('categories_list', 'Adding the "news" category grants that user or group the ability to categorize content as "news," with the consequence that blog pages and blog slots that display "news" will display that content. In order to post content in the first place, the user must also be a potential editor. In addition to admins, users who have been specifically granted membership in the "editor" group or any other group that has been given the "editor" permission are potential editors.');
    
    foreach ($this->getUseFields() as $field)
    {
      $this->getWidget($field)->setAttribute('autocomplete', 'off');
    }
  }

  /**
   * DOCUMENT ME
   */
  private function i18nDummy()
  {
    // This phrase isn't being discovered otherwise
    __('Password (again)', null, 'apostrophe');
  }

  /**
   * Override me to add more
   * @return mixed
   */
  public function getUseFields()
  {
    $fields = array('first_name', 'last_name', 'email_address', 'username', 'password', 'password_again', 'is_active', 'groups_list', 'categories_list');
    if (!sfConfig::get('app_a_user_admin_password', true))
    {
      $fields = array_diff($fields, array('password', 'password_again'));
    }
    if (!sfConfig::get('app_a_user_admin_is_active', true))
    {
      $fields = array_diff($fields, array('is_active'));
    }
    return $fields;
  }
}
