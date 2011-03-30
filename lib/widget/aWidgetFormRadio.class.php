<?php
/**
 * @package    apostrophePlugin
 * @subpackage    widget
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aWidgetFormRadio extends sfWidgetFormSelectRadio
{

  /**
   * DOCUMENT ME
   * @param mixed $options
   * @param mixed $attributes
   */
  protected function configure($options = array(), $attributes = array())
  {    
    parent::configure($options, $attributes);
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
  protected function formatChoices($name, $value, $choices, $attributes)
  {  
    $inputs = array();
    foreach ($choices as $key => $option)
    {
      $baseAttributes = array(
        'name'  => substr($name, 0, -2),
        'type'  => 'radio',
        'value' => self::escapeOnce($key),
        'id'    => $id = $this->generateId($name, self::escapeOnce($key)),
        );

      if (strval($key) == strval($value === false ? 0 : $value))
      {
        $baseAttributes['checked'] = 'checked';
      }

      $inputs[$id] = array(
        'input' => $this->renderTag('input', array_merge($baseAttributes, $attributes)),
        'label' => $this->renderContentTag('label', self::escapeOnce($option), array('for' => $id, 'id' => $id.'label', 'class' => $id)),
        );
    }

    return call_user_func($this->getOption('formatter'), $this, $inputs);
  }
}
