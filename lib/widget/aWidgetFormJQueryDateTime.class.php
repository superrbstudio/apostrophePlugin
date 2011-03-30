<?php
/**
 * @package    apostrophePlugin
 * @subpackage    widget
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aWidgetFormJQueryDateTime extends sfWidgetFormDateTime
{
  
  protected $dateWidget;
  protected $timeWidget;

  /**
   * DOCUMENT ME
   * @param mixed $options
   * @param mixed $attributes
   */
  protected function configure($options = array(), $attributes = array())
  {    
    $this->addOption('date', array());
    $this->addOption('time', array());
    $this->addOption('with_time', true);
    $this->addOption('format', '%date% %time%');
  }

  /**
   * DOCUMENT ME
   * @param mixed $name
   * @param mixed $value
   * @param mixed $attributes
   * @param mixed $errors
   * @return mixed
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $date = $this->getDateWidget($attributes)->render($name, $value);

    if(!$this->getOption('with_time', true))
    {
      $value = '';
    }

    return strtr($this->getOption('format'), array(
      '%date%' => $date,
      '%time%' => $this->getTimeWidget()->render($name, $value, $this->getAttributesFor('time', $attributes)),
    ));
  }

  /**
   * 
   * Returns the date widget.
   * @param  array $attributes  An array of attributes
   * @return sfWidgetForm A Widget representing the date
   */
  protected function getDateWidget($attributes = array())
  {
    return new aWidgetFormJQueryDate($this->getOptionsFor('date'), $this->getAttributesFor('date', $attributes));
  }

  /**
   * 
   * Returns the time widget.
   * @param  array $attributes  An array of attributes
   * @return sfWidgetForm A Widget representing the time
   */
  protected function getTimeWidget($attributes = array())
  {
    return new aWidgetFormJQueryTime($this->getOptionsFor('time'), $this->getAttributesFor('time', $attributes));
  }

  /**
   * 
   * Returns an array of HTML attributes for the given type.
   * @param  string $type        The type (date or time)
   * @param  array  $attributes  An array of attributes
   * @return array  An array of HTML attributes
   */
  protected function getAttributesFor($type, $attributes)
  {
    $defaults = isset($this->attributes[$type]) ? $this->attributes[$type] : array();

    return isset($attributes[$type]) ? array_merge($defaults, $attributes[$type]) : $defaults;
  }
}
