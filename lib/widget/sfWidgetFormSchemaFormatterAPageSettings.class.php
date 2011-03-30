<?php
/**
 * @package    apostrophePlugin
 * @subpackage    widget
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class sfWidgetFormSchemaFormatterAPageSettings extends sfWidgetFormSchemaFormatterAAdmin 
{

  /**
   * DOCUMENT ME
   * @param mixed $label
   * @param mixed $field
   * @param mixed $errors
   * @param mixed $help
   * @param mixed $hiddenFields
   * @return mixed
   */
  public function formatRow($label, $field, $errors = array(), $help = '', $hiddenFields = null)
  {
    return strtr($this->getRowFormat(), array(
      '%a_row_class%'   => (count($errors)) ? $this->getARowClassName().' has-errors': $this->getARowClassName(), 
      '%label%'         => "<h4>".$label.'</h4>',
      '%field%'         => $field,
      '%error%'         => $this->formatErrorsForRow($errors),
      '%help%'          => $this->formatHelp($help),
      '%hidden_fields%' => null === $hiddenFields ? '%hidden_fields%' : $hiddenFields,
    ));
   }
}