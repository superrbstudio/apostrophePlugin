<?php    
class aNewRichTextForm extends BaseForm
{
  // Ensures unique IDs throughout the page
  protected $id;
  public function __construct($id, $defaults = array(), $options = array(), $CSRFSecret = null)
  {
    $this->id = $id;
    // TODO why is case inconsistent here? Borrowed from aRichText
    $this->setOption('internal_browser', false);
    $this->setOption('allowed-tags', null);
    $this->setOption('allowed-attributes', null);
    $this->setOption('allowed-styles', null);
    
    parent::__construct($defaults, $options, $CSRFSecret);
  }
  public function configure()
  {
    // ADD YOUR FIELDS HERE
    
    $this->setWidget('value', new aWidgetFormRichTextarea(array('internal_browser' => $this->getOption('internal_browser'))));
    $this->setValidator('value', new sfValidatorHtml(array('required' => false, 'allowed_tags' => $this->getOption('allowed-tags'), 'allowed_attributes' => $this->getOption('allowed-attributes'), 'allowed_styles' => $this->getOption('allowed-styles'))));

    $this->widgetSchema->setNameFormat('slotform-' . $this->id . '[%s]');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
  }
}
