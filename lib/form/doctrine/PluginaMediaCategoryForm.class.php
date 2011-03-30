<?php
/**
 * 
 * PluginaMediaCategory form.
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfDoctrineFormPluginTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
abstract class PluginaMediaCategoryForm extends BaseaMediaCategoryForm
{

  /**
   * DOCUMENT ME
   */
  public function setup()
  {
    parent::setup();
    unset($this['created_at'], $this['updated_at'], $this['media_items_list'], $this['pages_list'], $this['slug']);
    $this->validatorSchema['name']->setOption('required', true);
    $this->widgetSchema->setFormFormatterName('aAdmin');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
  }
}
