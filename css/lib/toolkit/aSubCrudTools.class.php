<?php

class aSubCrudTools
{ 
  static public function getFormClass($model, $subtype)
  {
    return $model . ucfirst($subtype) . 'Form';
  }
}

