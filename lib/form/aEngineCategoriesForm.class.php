<?php
/**
 * Much of the time all an engine needs from its custom settings form is a way to add categories
 * to the page. Subclass this when that's what you are after
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aEngineCategoriesForm extends aPageForm
{

  /**
   * DOCUMENT ME
   */
  public function setup()
  {
    parent::setup();

    $this->useFields(array('categories_list'));
    $this->getWidget('categories_list')->setOption('query', Doctrine::getTable('aCategory')->createQuery()->orderBy('aCategory.name asc'));
    if (sfContext::getInstance()->getUser()->hasCredential('admin'))
    {
      // If we make this a "hidden" field renderHiddenFields will output it, 
      // causing conflicts
      $this->setWidget('categories_list_add',
        new sfWidgetFormInputText());
      // Our update method deals with validating this for duplicates
      $this->setValidator('categories_list_add',
        new sfValidatorPass(array('required' => false)));
    }
    $this->widgetSchema->setLabel('categories_list', 'Categories');
    $this->widgetSchema->setHelp('categories_list','(Defaults to All Categories)');
    $this->getValidator('categories_list')->setOption('required', false);
    $this->widgetSchema->setNameFormat('enginesettings[%s]');
    // We use the aPageSettings formatter here instead of aAdmin because it puts H4 tags around the labels for styling
    $this->widgetSchema->setFormFormatterName('aPageSettings');
  }

  /**
   * DOCUMENT ME
   * @param mixed $addValues
   */
  public function updateCategoriesList($addValues)
  {
    // Add any new categories (categories_list_add)
    $link = array();
    if (!is_array($addValues))
    {
      $addValues = array();
    }
    foreach ($addValues as $value)
    {
      $existing = Doctrine::getTable('aCategory')->findOneBy('name', $value);
      if ($existing)
      {
        $aCategory = $existing;
      }
      else
      {
        $aCategory = new aCategory();
        $aCategory['name'] = $value;
      }
      $aCategory->save();
      $link[] = $aCategory['id'];
    }
    if (!is_array($this->values['categories_list']))
    {
      $this->values['categories_list'] = array();
    }
    foreach ($link as $id)
    {
      if (!in_array($id, $this->values['categories_list']))
      {
        $this->values['categories_list'][] = $id;
      }
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $con
   */
  protected function doSave($con = null)
  {
    $this->updateCategoriesList(isset($this->values['categories_list_add']) ? $this->values['categories_list_add'] : array());
    parent::doSave($con);
  }
}