<?php
/**
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaRichTextForm extends BaseForm
{
  protected $id;
  protected $soptions;

  /**
   * DOCUMENT ME
   * @param mixed $id
   * @param mixed $soptions
   */
  public function __construct($id, $soptions = null)
  {
    $this->id = $id;
    $this->soptions = $soptions;
    $this->allowedTags = $this->consumeSlotOption('allowed-tags');
    $this->allowedAttributes = $this->consumeSlotOption('allowed-attributes');
    $this->allowedStyles = $this->consumeSlotOption('allowed-styles');
    parent::__construct();
  }

  /**
   * DOCUMENT ME
   * @param mixed $s
   * @return mixed
   */
  protected function consumeSlotOption($s)
  {
    if (isset($this->soptions[$s]))
    {
      $v = $this->soptions[$s];
      unset($this->soptions[$s]);
      return $v;
    }
    else
    {
      return null;
    }
  }

  /**
   * DOCUMENT ME
   */
  public function configure()
  {
    $widgetOptions = array();
    $tool = $this->consumeSlotOption('tool');
    if (!is_null($tool))
    {
      $widgetOptions['tool'] = $tool;
    }
    // The rest of the options passed become attributes of the widget
    $this->setWidgets(array('value' => new aWidgetFormRichTextarea($widgetOptions, $this->soptions)));
    $this->setValidators(array('value' => new sfValidatorHtml(array('required' => false, 'allowed_tags' => $this->allowedTags, 'allowed_attributes' => $this->allowedAttributes, 'allowed_styles' => $this->allowedStyles))));
    // There are problems with AJAX plus FCK plus Symfony forms. FCK insists on making the name and ID
    // the same and brackets are not valid in IDs which can lead to problems in strict settings
    // like AJAX in IE. Work around this by not attempting to use brackets here
    $this->widgetSchema->setNameFormat('slot-form-' . $this->id . '-%s');
    $this->widgetSchema->setFormFormatterName('aAdmin');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
  }
}