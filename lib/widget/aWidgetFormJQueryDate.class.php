<?php
/**
 * 
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * aWidgetFormJQueryDate represents a date widget rendered by JQuery UI.
 * This widget needs JQuery and JQuery UI to work.
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: aWidgetFormJQueryDate.class.php 12875 2008-11-10 12:22:33Z fabien $
 */
class aWidgetFormJQueryDate extends sfWidgetFormDate
{

  /**
   * 
   * Configures the current widget.
   * Available options:
   * * image:   The image path to represent the widget (false by default)
   * config:  A JavaScript array that configures the JQuery date widget
   * culture: The user culture
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->addOption('image', false);
    $this->addOption('config', '{}');
    $this->addOption('culture', '');
    $years = range(date('Y') - 150, date('Y') + 150);
    $this->addOption('years', array_combine($years, $years));
    
    $classes = preg_split('/\s+/', $this->getAttribute('class'));
    $classes[] = 'a-date-field';
    $this->setAttribute('class', implode(' ', $classes));
    if ('en' == $this->getOption('culture'))
    {
      $this->setOption('culture', 'en');
    }
  }

  /**
   * 
   * @param  string $name        The element name
   * @param  string $value       The date displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   * @return string An HTML tag string
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $prefix = $this->generateId($name);
    // Spike Broehm: hyphens are not valid in function names
    $prefix = str_replace('-', '_', $prefix);
    $image = '';
    if (false !== $this->getOption('image'))
    {
      $image = sprintf(', buttonImage: "%s", buttonImageOnly: true', $this->getOption('image'));
    }

    $beforeShow = '';
    if (isset($attributes['beforeShow']))
    {
      $beforeShow = sprintf(', beforeShow: %s', $attributes['beforeShow']);
    }

    $onClose = '';
    if (isset($attributes['onClose']))
    {
      $onClose = sprintf(', onClose: %s', $attributes['onClose']);
    }

    return 
      // Outer div with the prefix ID allows efficient jQuery - Firefox can delay for
      // as much as two full seconds trying to process the :has selectors that are otherwise
      // necessary to locate all of the controls in here
      '<div class="a-date-wrapper" id="' . $prefix . '">' .
      // Parent class select controls, our interface to Symfony
      '<span style="display: none">' . parent::render($name, $value, $attributes, $errors) . '</span>' .
      // Autopopulated by jQuery.Datepicker, we also allow direct editing and have hooks relating to that
      $this->renderTag('input', array('type' => 'text', 'size' => 10, 'id' => $id = $this->generateId($name).'_jquery_control', 'class' => isset($attributes['class']) ? $attributes['class'] : '', 'onBlur' => $prefix . "_update_linked($('#$id').val())")) .
           sprintf(<<<EOF
<script type="text/javascript">

function %s_read_linked()
{
  var sel = '#%s';
  var month = '#%s';
  var day = '#%s';
  var year = '#%s';
  val = \$(month).val() + "/" + \$(day).val() + "/" + \$(year).val();
  if (val === '//')
  {
    val = '';
  }
  \$(sel).val(val);
  return {};
}

function %s_update_linked(date)
{
  apostrophe.log(date);
  var components = date.match(/(\d+)\/(\d+)\/(\d\d\d\d)/);
  var month = "#%s";
  var day = "#%s";
  var year = "#%s";
  if (!components)
  {
    if (date.length)
    {
      apostrophe.log("Bad format");
      // alert("The date must be in MM/DD/YYYY format. Example: 09/29/2009. Hint: select a date from the calendar.");
      $('#$id').focus();
    }
    // TODO: an option to make it mandatory
    \$(month).val('');
    \$(day).val('');
    \$(year).val('');
    \$(month).change();
    \$(day).change();
    \$(year).change();
  }
  else
  {
    // Leading zeroes cannot be removed by parseInt alone
    \$(month).val(components[1].replace(/^0+/, ''));
    \$(day).val(components[2].replace(/^0+/, ''));
    \$(year).val(components[3].replace(/^0+/, ''));
    // Clean up the displayed value also
    $('#$id').val(\$(month).val() + '/' + \$(day).val() + '/' + \$(year).val());
    \$(month).change();
    \$(day).change();
    \$(year).change();
  }
  
  $('#$id').trigger('aDateUpdated');
}

$(function()
{

  %s_read_linked();
  
  \$("#%s").datepicker(\$.extend({}, {
    dateFormat: "mm/dd/yyyy",
    minDate:    new Date(%s, 1 - 1, 1),
    maxDate:    new Date(%s, 12 - 1, 31),
    beforeShow: %s_read_linked,
    onSelect:   %s_update_linked,
    showOn:     "both",
		showAnim: 	"fadeIn"
		%s
		%s
    %s
  }, \$.datepicker.regional["%s"], %s));

	// General useability stuff that the original date widget was lacking because it was made by robots and not actual human beings
	$('.ui-datepicker-trigger').attr('title','Choose A Date').hover(function(){
		$(this).fadeTo(0,.5);
	},function(){
		$(this).fadeTo(0,1);	
	});
		
});

</script>

EOF
      ,
      $prefix, $id,
      $this->generateId($name.'[month]'), $this->generateId($name.'[day]'), $this->generateId($name.'[year]'),
      $prefix,
      $this->generateId($name.'[month]'), $this->generateId($name.'[day]'), $this->generateId($name.'[year]'),
      $prefix,
      $id,
      min($this->getOption('years')), max($this->getOption('years')),
      $prefix, $prefix, $beforeShow, $onClose, $image, $this->getOption('culture'), $this->getOption('config')
    ) . 
    // Close wrapper div
    '</div>';
  }
}
