<?php

/**
 * PluginaEmbedMediaAccount form.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfDoctrineFormPluginTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
abstract class PluginaEmbedMediaAccountForm extends BaseaEmbedMediaAccountForm
{
  public function configure()
  {
    parent::configure();
    $services = aMediaTools::getEmbedServiceNames();
    $this->setWidget('service', new sfWidgetFormChoice(array('choices' => array_combine($services, $services))));
    $this->setValidator('service', new sfValidatorChoice(array('choices' => $services)));
    $this->widgetSchema->setFormFormatterName('aAdmin');
  }
}
