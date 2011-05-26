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
    $errorListFormatInARow     = "<div class='a-form-errors'>\n<ul class=\"a-ui a-error-list error_list\">\n%errors%</ul>\n</div>\n",
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
   * getARowClassName builds the unique class for a form row that provides an easy way to have complete control over how different fields can be styled with CSS.
	 * @param string $field
   * @return string
   */
  public function getARowClassName($field = null)
  {
	 	$className = '';
		if ($field) 
		{
			$doc = new DOMDocument();
			$doc->loadXML($field);
			foreach($doc->childNodes as $node);
			$name = $node->nodeName; // input, select, textarea 
			$type = ($node->getAttribute('type')) ? $node->getAttribute('type') : ''; // text, password, checkbox
			$id = ($node->getAttribute('id')) ? $node->getAttribute('id') : ''; // form_class_id
			$className = ' '.aTools::slugify('a-'.$name.'-'.$type).' '.aTools::slugify($id);
		}
    return $this->aRowClassName . $className;
  }
}