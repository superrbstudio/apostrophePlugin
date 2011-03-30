<?php
/**
 * @package    apostrophePlugin
 * @subpackage    widget
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aWidgetFormJQueryTime extends sfWidgetFormTime
{

  /**
   * DOCUMENT ME
   * @param mixed $options
   * @param mixed $attributes
   */
  protected function configure($options = array(), $attributes = array())
  {
    $jq_path = '/apostrophePlugin/js/timepicker.js';  
    sfContext::getInstance()->getResponse()->addJavascript($jq_path, 'first');
    
    parent::configure($options, $attributes);

    $this->addOption('format', 'g:iA');

  }

  /**
   * 
   * @param  string $name        The element name
   * @param  string $value       The time displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   * @return string An HTML tag string
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $format = true;
    $empty = empty($value);
    if ($empty)
    {
      $format = false;
    }
    if (is_array($value))
    {
      if ((!strlen($value['hour'])) || (!strlen($value['minute'])))
      {
        $format = false;
      }
      else
      {
        $value = $value['hour'] . ':' . $value['minute'];
        if (isset($value['second']))
        {
          $value .= ':' . $value['second'];
        }
      }
    }
    if ($format)
    {
      $value = date($this->getOption('format'), strtotime($value));
    }

    $attributes['id'] = $this->generateId($name);
    $html = parent::render($name, $value, $attributes, $errors);
    $wrapperID = $attributes['id'] . rand(0, 10000);
    $html = $this->wrapInDiv($html, $wrapperID);
    $html.= "<script type='text/javascript'>$(document).ready(function() { timepicker2('#" . $wrapperID . "', " . json_encode($attributes) . ") });</script>";
    return $html;
  }

  /**
   * DOCUMENT ME
   * @param mixed $html
   * @param mixed $id
   * @return mixed
   */
  protected function wrapInDiv($html, $id)
  {
    return '<div id="' . $id . '">' . $html . '</div>';
  }
}