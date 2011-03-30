<?php
/**
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaRawHTMLForm extends BaseForm
{
  protected $id;

  /**
   * DOCUMENT ME
   * @param mixed $id
   */
  public function __construct($id)
  {
    $this->id = $id;
    parent::__construct();
  }

  /**
   * DOCUMENT ME
   */
  public function configure()
  {
    $this->setWidgets(array('value' => new sfWidgetFormTextarea(array(), array('class' => 'aRawHTMLSlotTextarea'))));
    // Raw HTML slot, so anything goes, including an empty response 
    $this->setValidators(array('value' => new sfValidatorString(array('required' => false))));
    $this->widgetSchema->setNameFormat('slot-form-' . $this->id . '[%s]');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
  }
}