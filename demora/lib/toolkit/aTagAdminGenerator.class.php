<?php

class aTagAdminGenerator extends sfDoctrineGenerator
{
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