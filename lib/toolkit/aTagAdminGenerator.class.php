<?php
/**
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aTagAdminGenerator extends sfDoctrineGenerator
{

  /**
   * DOCUMENT ME
   * @param mixed $field
   * @return mixed
   */
  public function renderField($field)
  {
    if(preg_match('/^tag_/', $field->getName()))
    {
      $parts = preg_split('/^tag_/', $field->getName());
      return sprintf('$%s->%sCount', $this->getSingularName(), $parts[1]);
    }
    else
    {
      return parent::renderField($field);
    }
  }
}