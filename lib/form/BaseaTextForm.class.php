<?php

class BaseaTextForm extends BaseForm
{
  protected $id;
  protected $value;
  protected $soptions;
  public function __construct($id, $value, $soptions)
  {
    $this->id = $id;
    $this->value = $value;
    $this->soptions = $soptions;
    parent::__construct();
  }
  public function configure()
  {
    $class = isset($this->soptions['class']) ? ($this->soptions['class'] . ' ') : '';
    $class .= 'aTextSlot';

    $text = html_entity_decode(strip_tags($this->value), ENT_COMPAT, 'UTF-8');

    if (isset($this->soptions['multiline']) && $this->soptions['multiline'])
    {
      unset($this->soptions['multiline']);
      $class .= ' multi-line';
	    $this->soptions['class'] = $class;
	    $this->setWidgets(array('value' => new sfWidgetFormTextarea(array('default' => $text), $this->soptions)));
    }
    else
    {
      $class .= ' single-line';
	    $this->soptions['class'] = $class;
	    $this->setWidgets(array('value' => new sfWidgetFormInputText(array('default' => $text), $this->soptions)));
    }

    $this->setValidators(array('value' => new sfValidatorString(array('required' => false))));
    $this->widgetSchema->setNameFormat('slot-form-' . $this->id . '[%s]');    
    $this->widgetSchema->setFormFormatterName('aAdmin');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
  }
}