<?php
/**
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aSubCrudTools
{

  /**
   * DOCUMENT ME
   * @param mixed $model
   * @param mixed $subtype
   * @return mixed
   */
  static public function getFormClass($model, $subtype)
  {
    return $model . ucfirst($subtype) . 'Form';
  }
}

