<?php    
class aFeedForm extends sfForm
{
  // Ensures unique IDs throughout the page
  protected $id;
  public function __construct($id, $defaults)
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
  }
}
