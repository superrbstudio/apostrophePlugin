<?php    
class BaseaFeedForm extends BaseForm
{
  // Ensures unique IDs throughout the page
  protected $id;
  // PARAMETERS ARE REQUIRED, no-parameters version is strictly to satisfy i18n-update
  public function __construct($id = 1, $defaults = array())
  {
    $this->id = $id;
    parent::__construct();
    $this->setDefaults($defaults);
  }
  public function configure()
  {
    $this->setWidgets(array('url' => new sfWidgetFormInputText(array('label' => 'RSS Feed URL'))));
    $this->setValidators(array('url' => new sfValidatorUrl(array('required' => true, 'max_length' => 1024))));
    
    // Ensures unique IDs throughout the page
    $this->widgetSchema->setNameFormat('slotform-' . $this->id . '[%s]');
    $this->widgetSchema->setFormFormatterName('aAdmin');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
  }
}
