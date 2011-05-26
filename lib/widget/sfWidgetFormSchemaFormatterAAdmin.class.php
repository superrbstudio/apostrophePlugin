<?php
/**
 * @package    apostrophePlugin
 * @subpackage    widget
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class sfWidgetFormSchemaFormatterAAdmin extends sfWidgetFormSchemaFormatter
{
  protected
    $aRowClassName = "a-form-row",
    $rowFormat = "<div class=\"%a_row_class%\">\n  %label%\n  <div class=\"a-form-field\">%field%</div> %error% \n %help%%hidden_fields%\n</div>\n",
    $errorRowFormat = '%errors%',
    $helpFormat = '<div class="a-help">%help%</div>',
    $decoratorFormat ="<div class=\"a-admin-form-container\">\n %content%\n</div>",
    $errorListFormatInARow     = "<div class='a-form-errors'>\n<ul class=\"a-error-list error_list\">\n%errors%</ul>\n</div>\n",
    $errorRowFormatInARow      = "<li>%error%</li>\n",
    $namedErrorRowFormatInARow = "<li>%name%: %error%</li>\n";

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
      '%a_row_class%'   => (count($errors)) ? $this->getARowClassName($field).' has-errors': $this->getARowClassName($field),
      '%label%'         => $label,
      '%field%'         => $field,
      '%error%'         => $this->formatErrorsForRow($errors),
      '%help%'          => $this->formatHelp($help),
      '%hidden_fields%' => null === $hiddenFields ? '%hidden_fields%' : $hiddenFields,
    ));
   }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function getARowClassName($field = null)
  {
	 	$id = '';
		if ($field) {
			$doc = new DOMDocument();
			$doc ->loadXML($field);
			foreach($doc->childNodes as $node);
			$id = aTools::slugify($node->getAttribute('id'));
		}

    return $this->aRowClassName . ' ' . $id;
  }
}