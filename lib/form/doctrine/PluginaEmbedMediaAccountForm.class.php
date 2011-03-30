<?php
/**
 * 
 * PluginaEmbedMediaAccount form.
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfDoctrineFormPluginTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
abstract class PluginaEmbedMediaAccountForm extends BaseaEmbedMediaAccountForm
{

  /**
   * DOCUMENT ME
   */
  public function setup()
  {
    parent::setup();
    $services = aMediaTools::getEmbedServiceNames();
    $this->setWidget('service', new aWidgetFormChoice(array('choices' => array_combine($services, $services), 'expanded' => true, 'default' => 'YouTube')));
    $this->setValidator('service', new sfValidatorChoice(array('choices' => $services)));
    $this->widgetSchema->setFormFormatterName('aAdmin');
  }
}
