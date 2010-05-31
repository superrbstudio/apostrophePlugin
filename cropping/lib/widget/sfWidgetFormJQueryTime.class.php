<?php

/*
 * "WHAT IS sfWidgetFormTime DOING HERE?" 
 *
 * We copied and pasted it here in order to fix a bug and then inherit from the fixed version.
 * TODO: undo this once our patch for http://trac.symfony-project.org/ticket/8446
 * is accepted and released in an official 1.4.x tarball.
 *
 */
 
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormTimeFixed represents a time widget.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfWidgetFormTime.class.php 15283 2009-02-05 15:19:29Z fabien $
 */
class sfWidgetFormTimeFixed extends sfWidgetForm
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * format:                 The time format string (%hour%:%minute%:%second%)
   *  * format_without_seconds: The time format string without seconds (%hour%:%minute%)
   *  * with_seconds:           Whether to include a select for seconds (false by default)
   *  * hours:                  An array of hours for the hour select tag (optional)
   *  * minutes:                An array of minutes for the minute select tag (optional)
   *  * seconds:                An array of seconds for the second select tag (optional)
   *  * can_be_empty:           Whether the widget accept an empty value (true by default)
   *  * empty_values:           An array of values to use for the empty value (empty string for hours, minutes, and seconds by default)
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addOption('format', '%hour%:%minute%:%second%');
    $this->addOption('format_without_seconds', '%hour%:%minute%');
    $this->addOption('with_seconds', false);
    $this->addOption('hours', parent::generateTwoCharsRange(0, 23));
    $this->addOption('minutes', parent::generateTwoCharsRange(0, 59));
    $this->addOption('seconds', parent::generateTwoCharsRange(0, 59));

    $this->addOption('can_be_empty', true);
    $this->addOption('empty_values', array('hour' => '', 'minute' => '', 'second' => ''));
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
    // convert value to an array
    $default = array('hour' => null, 'minute' => null, 'second' => null);
    if (is_array($value))
    {
      $value = array_merge($default, $value);
    }
    else
    {
      $value = ctype_digit($value) ? (integer) $value : strtotime($value);
      if (false === $value)
      {
        $value = $default;
      }
      else
      {
        // int cast required to get rid of leading zeros
        $value = array('hour' => (int) date('H', $value), 'minute' => (int) date('i', $value), 'second' => (int) date('s', $value));
      }
    }

    $time = array();
    $emptyValues = $this->getOption('empty_values');

    $baseOptions = array();
    if ($this->getIdFormat() !== null)
    {
      $baseOptions['id_format'] = $this->getIdFormat();
    }
    // hours
    $widget = new sfWidgetFormSelect(array_merge($baseOptions, array('choices' => $this->getOption('can_be_empty') ? array('' => $emptyValues['hour']) + $this->getOption('hours') : $this->getOption('hours'))), array_merge($this->attributes, $attributes));
    $time['%hour%'] = $widget->render($name.'[hour]', $value['hour']);

    // minutes
    $widget = new sfWidgetFormSelect(array_merge($baseOptions, array('choices' => $this->getOption('can_be_empty') ? array('' => $emptyValues['minute']) + $this->getOption('minutes') : $this->getOption('minutes'))), array_merge($this->attributes, $attributes));
    $time['%minute%'] = $widget->render($name.'[minute]', $value['minute']);

    if ($this->getOption('with_seconds'))
    {
      // seconds
      $widget = new sfWidgetFormSelect(array_merge($baseOptions, array('choices' => $this->getOption('can_be_empty') ? array('' => $emptyValues['second']) + $this->getOption('seconds') : $this->getOption('seconds'))), array_merge($this->attributes, $attributes));
      $time['%second%'] = $widget->render($name.'[second]', $value['second']);
    }

    return strtr($this->getOption('with_seconds') ? $this->getOption('format') : $this->getOption('format_without_seconds'), $time);
  }
}

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormJQueryDate represents a date widget rendered by JQuery UI.
 *
 * This widget needs JQuery and JQuery UI to work.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfWidgetFormJQueryDate.class.php 12875 2008-11-10 12:22:33Z fabien $
 */
class sfWidgetFormJQueryTime extends sfWidgetFormTimeFixed
{
  /**
   * Configures the current widget.
   *
   * Available options:
   *
   *  * image:   The image path to represent the widget (false by default)
   *  * config:  A JavaScript array that configures the JQuery time widget
   *  * culture: The user culture
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addOption('image', false);
    $this->addOption('config', '{}');
    $this->addOption('culture', '');

    parent::configure($options, $attributes);

    $classes = preg_split('/\s+/', $this->getAttribute('class'));
    $classes[] = 'a-time-field';
    $this->setAttribute('class', implode(' ', $classes));

    if ('en' == $this->getOption('culture'))
    {
      $this->setOption('culture', 'en');
    }
  }

  /**
   * @param  string $name        The element name
   * @param  string $value       The date displayed in this widget (sometimes already an array with hour and minute keys)
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $prefix = $this->generateId($name);
    sfContext::getInstance()->getResponse()->addJavascript('/sfJqueryReloadedPlugin/js/plugins/jquery.autocomplete.min.js', 'last');

    $image = '';
    if (false !== $this->getOption('image'))
    {
      // TODO: clock widget handling
    }
    $hourid = $this->generateId($name.'[hour]');
    $minid = $this->generateId($name.'[minute]');

		$s = '<div class="a-time-wrapper" id="' . $prefix . '-time">';
    $s .= '<span style="display: none">' . parent::render($name, $value, $attributes, $errors) . '</span>';

    $val = '';
    if (is_array($value))
    {
      if (strlen($value['hour']) && strlen($value['minute']))
      {
        $val = htmlspecialchars(aDate::time(sprintf("%02d:%02d:00", $value['hour'], $value['minute']), false));
      }
    }
    elseif (strlen($value))
    {
      $val = htmlspecialchars(aDate::time($value, false), ENT_QUOTES);
    }
    $s .= "<input type='text' name='a-ignored' id='$prefix-ui' value='$val' class='" . (isset($attributes['class']) ? $attributes['class'] : '') . "'><img id='$prefix-ui-trigger' class='ui-timepicker-trigger' src='/apostrophePlugin/images/a-icon-timepicker.png'/>";
    $s .= <<<EOM
<script type="text/javascript" charset="utf-8">
$(function() { 
  var hour;
  var min;
  var times = [ ];
  for (thour = 0; (thour < 24); thour++)
  {
    // Starting with 8am makes more sense for typical clients
    var hour = (thour + 8) % 24;
    for (min = 0; (min < 60); min += 30)
    {
      times.push(prettyTime(hour, min));
    }
  }
  $('#$prefix-ui').autocomplete(times, { minChars: 0, selectFirst: false, max: 100 });
  // Double click on focus pops up autocomplete immediately
  $('#$prefix-ui').focus(function() { $(this).click(); $(this).click() } ).next().click(function(event){
		event.preventDefault();
		$(this).prev().focus();
	});
  $('#$prefix-ui').blur(function() {
    var val = $(this).val();
    var components = val.match(/(\d\d?)(:\d\d)?\s*(am|pm)?/i);
    if (components)
    {
      var hour = components[1];
      var min = components[2];
      if (min)
      {
        min = min.substr(1);
      }
      if (!min)
      {
        min = '00';
      }
      if (min < 10)
      {
        min = '0' + Math.floor(min);
      }
      var ampm = components[3] ? components[3].toUpperCase() : false;
      if (!ampm)
      {
        if (hour >= 8)
        {
          ampm = 'AM';
        }
        else
        {
          ampm = 'PM';
        }
      }
      var formal = hour + ':' + min + ampm;
      $(this).val(formal);
      if ((ampm === 'AM') && (hour == 12))
      {
        hour = 0;
      }
      if ((ampm === 'PM') && !(hour == 12))
      {
        // Careful: force numeric
        hour = Math.floor(hour) + 12;
      }
      $('#$hourid').val(hour);
      $('#$minid').val(min);
      // Something to bind to in other places
      $(this).trigger('aTimeUpdated');
    }
    else
    {
      if (val.length)
      {
        alert("The time must be in hh:mm format, followed by AM or PM. Hint: click on the typeahead suggestions.");
        $('#$prefix-ui').focus();
      }
      else
      {
        // NULL is often a valid value
        $('#$hourid').val('');
        $('#$minid').val('');
      }
    }
  });
  function prettyTime(hour, min)
  {
    var ampm = 'AM';
    phour = hour;
    if (hour >= 12)
    {
      ampm = 'PM';
    }
    if (hour >= 13)
    {
      phour -= 12;
    }
    if (phour == 0)
    {
      phour = 12;
    }
    pmin = min;
    if (min < 10)
    {
      pmin = '0' + Math.floor(min);
    }
    return phour + ':' + pmin + ampm;
  }

	// General useability stuff that the original date widget was lacking because it was made by robots and not actual human beings
	$('#$prefix-ui-trigger').attr('title','Set A Time').hover(function(){
		$(this).fadeTo(0,.5);
	},function(){
		$(this).fadeTo(0,1);
	});
});
</script>
</div>
EOM
;
    return $s;
  }
}
