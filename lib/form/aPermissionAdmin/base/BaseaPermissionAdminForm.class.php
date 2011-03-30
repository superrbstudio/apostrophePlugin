<?php
/**
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaPermissionAdminForm extends sfGuardPermissionForm
{

  /**
   * DOCUMENT ME
   */
  public function configure()
  {
    parent::configure();
    
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
  }

  /**
   * DOCUMENT ME
   */
  private function i18nDummy()
  {
    // This phrase isn't being discovered otherwise
    __('Save and add', null, 'apostrophe');
  }
}
