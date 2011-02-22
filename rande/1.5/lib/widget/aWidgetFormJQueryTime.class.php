<?php

class aWidgetFormJQueryTime extends sfWidgetFormTime
{
  protected function configure($options = array(), $attributes = array())
  {
		$jq_path = '/apostrophePlugin/js/timepicker.js';	
		sfContext::getInstance()->getResponse()->addJavascript($jq_path, 'first');
		
    parent::configure($options, $attributes);

    $this->addOption('format', 'g:iA');

  }

  /**
   * @param  string $name        The element name
   * @param  string $value       The time displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    if(!empty($value))
    {
      // Allow both array and string syntax
      if (is_array($value))
      {
        $value = $value['hour'] . ':' . $value['minute'];
        if (isset($value['second']))
        {
          $value .= ':' . $value['second'];
        }
      }
      $value = date($this->getOption('format'), strtotime($value));
    }

    $attributes['id'] = $this->generateId($name);
    $html = parent::render($name, $value, $attributes, $errors);
		$wrapperID = $attributes['id'] . rand(0, 10000);
		$html = $this->wrapInDiv($html, $wrapperID);
    $html.= "<script type='text/javascript'>$(document).ready(function() { timepicker2('#" . $wrapperID . "', " . json_encode($attributes) . ") });</script>";

    return $html;
  }

	protected function wrapInDiv($html, $id)
	{
		return '<div id="' . $id . '">' . $html . '</div>';
	}
}