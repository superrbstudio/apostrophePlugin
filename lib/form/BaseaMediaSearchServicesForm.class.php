<?php
/**
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaMediaSearchServicesForm extends BaseForm
{

  /**
   * The search services form doesn't do anything permanent and we want to be able
   * to stuff it via pager links, so let's disable CSRF
   * @param mixed $defaults
   * @param mixed $options
   * @param mixed $CSRFSecret
   */
  public function __construct($defaults = array(), $options = array(), $CSRFSecret = null) 
  {
    parent::__construct($defaults, $options, false);
  }

  /**
   * DOCUMENT ME
   */
  public function configure()
  {
    $services = aMediaTools::getEmbedServiceNames();
    $this->setWidget('service', new aWidgetFormChoice(array('choices' => array_combine($services, $services), 'expanded' => true, 'default' => 'YouTube')));
    $this->setValidator('service', new sfValidatorChoice(array('choices' => $services)));
    $this->setWidget('q', new sfWidgetFormInput(array(),array('class'=>'a-search-video a-search-form')));
    $this->setValidator('q', new sfValidatorString(array('required' => true)));
    $this->widgetSchema->setNameFormat('aMediaSearchServices[%s]');
    $this->widgetSchema->setFormFormatterName('aAdmin');  
    $this->widgetSchema->setLabel('q', ' ');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
  }
}
