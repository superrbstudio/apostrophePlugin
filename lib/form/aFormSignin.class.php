<?php
/**
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aFormSignin extends sfGuardFormSignin
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
    // Not sure why extraction is failing for these
    __('Remember', null, 'apostrophe');
    __('The username and/or password is invalid.', null, 'apostrophe');
  }
}
