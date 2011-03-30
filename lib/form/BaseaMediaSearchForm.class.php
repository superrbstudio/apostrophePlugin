<?php
/**
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaMediaSearchForm extends BaseForm
{

  /**
   * DOCUMENT ME
   */
  public function configure()
  {
    $this->setWidget('search', new sfWidgetFormInputText(array(), array('id' => 'a-media-search', 'class' => 'a-search-field')));
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
  }
}
