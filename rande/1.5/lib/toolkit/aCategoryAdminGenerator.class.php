<?php

class aCategoryAdminGenerator extends sfDoctrineGenerator
{
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