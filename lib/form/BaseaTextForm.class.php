<?php
/**
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaTextForm extends BaseForm
{
  protected $id;
  protected $value;
  protected $soptions;

  /**
   * DOCUMENT ME
   * @param mixed $id
   * @param mixed $value
   * @param mixed $soptions
   */
  public function __construct($id, $value, $soptions)
  {
    $this->id = $id;
    $this->value = $value;
    $this->soptions = $soptions;
    parent::__construct();
  }

  /**
   * DOCUMENT ME
   */
  public function configure()
  {
    $class = isset($this->soptions['class']) ? ($this->soptions['class'] . ' ') : '';
    $class .= 'aTextSlot';

    $text = aHtml::toPlaintext($this->value);

    if (isset($this->soptions['multiline']) && $this->soptions['multiline'])
    {
      unset($this->soptions['multiline']);
      $class .= ' multi-line';
      $this->setWidgets(array('value' => new sfWidgetFormTextarea(array('default' => $text), array('class' => $class))));
    }
    else
    {
      $class .= ' single-line';
      $this->setWidgets(array('value' => new sfWidgetFormInputText(array('default' => $text), array('class' => $class))));
    }

    $this->setValidators(array('value' => new sfValidatorString(array('required' => false))));
    $this->widgetSchema->setNameFormat('slot-form-' . $this->id . '[%s]');    
    $this->widgetSchema->setFormFormatterName('aAdmin');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
  }
}