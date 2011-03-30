<?php
/**
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aCategoryAdminGenerator extends sfDoctrineGenerator
{

  /**
   * DOCUMENT ME
   * @param mixed $field
   * @return mixed
   */
  public function renderField($field)
  {
    if($field->getType() == 'Category')
    {
      return sprintf('$helper->getCount(\'%s\', $a_category->id)', $field->getName());
    }
    else
    {
      return parent::renderField($field);
    }
  }
}