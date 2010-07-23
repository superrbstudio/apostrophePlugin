<?php // Yes, this is template code, but we use regular PHP syntax because we are building a sentence and the introduction of ?>
<?php // newlines wrecks the punctuation. (OK, we're building a ul list now...) ?>
<?php 
use_helper('I18N');
$clauses = array();
// We don't describe the aspect ratio or fixed width and height anymore, since we allow the user to crop to achieve them
if (aMediaTools::getAttribute('minimum-width'))
{
  $clauses[] = __('A minimum width of %mw% pixels', array('%mw%' => aMediaTools::getAttribute('minimum-width')), 'apostrophe');
}
if (aMediaTools::getAttribute('minimum-height'))
{
  $clauses[] = __('A minimum height of %mh% pixels', array('%mh%' => aMediaTools::getAttribute('minimum-height')), 'apostrophe');
}
if (aMediaTools::getAttribute('type'))
{
  // Internationalize the plural so that can be correct too
  $type = __(aMediaTools::getAttribute('type') . "s", null, 'apostrophe');
} 
else
{
  $type = __("items", null, 'apostrophe');
}
if (count($clauses))
{
  // Markup change: for I18N it's better to use a list here rather than
  // trying to create a sentence with commas and 'and'
  echo('<h4>' . __("Displaying only %t% with:", array('%t%' => $type), 'apostrophe') . '</h4>');
  echo('<ul class="a-constraints">');
  foreach ($clauses as $clause)
  {
    echo('<li>' . $clause . '</li>');
  }
  echo('</ul>');
}
