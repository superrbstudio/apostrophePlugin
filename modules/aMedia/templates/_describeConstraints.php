<?php // Yes, this is template code, but we use regular PHP syntax because we are building a sentence and the introduction of ?>
<?php // newlines wrecks the punctuation. ?>
<?php 
use_helper('I18N');
$clauses = array();
if (aMediaTools::getAttribute('aspect-width') && aMediaTools::getAttribute('aspect-height'))
{
  $clauses[] = __('A %w%x%h% aspect ratio', array('%w%' => aMediaTools::getAttribute('aspect-width'), '%h%' => aMediaTools::getAttribute('aspect-height')));
}
if (aMediaTools::getAttribute('minimum-width'))
{
  $clauses[] = __('A minimum width of %mw% pixels', array('%mw%' => aMediaTools::getAttribute('minimum-width')));
}
if (aMediaTools::getAttribute('minimum-height'))
{
  $clauses[] = __('A minimum height of %mh% pixels', array('%mh%' => aMediaTools::getAttribute('minimum-height')));
}
if (aMediaTools::getAttribute('width'))
{
  $clauses[] = __('A width of exactly %w% pixels', array('%w%' => aMediaTools::getAttribute('width')));
}
if (aMediaTools::getAttribute('height'))
{
  $clauses[] = __('A height of exactly %h% pixels', array('%h%' => aMediaTools::getAttribute('height')));
}
if (aMediaTools::getAttribute('type'))
{
  // Internationalize the plural so that can be correct too
  $type = __(aMediaTools::getAttribute('type') . "s");
} 
else
{
  $type = __("items");
}
if (count($clauses))
{
  // Markup change: for I18N it's better to use a list here rather than
  // trying to create a sentence with commas and 'and'
  echo('<h3>' . __("Displaying only %t% with:", array('%t%' => $type)) . '</h3>');
  echo('<ul class="a-constraints">');
  foreach ($clauses as $clause)
  {
    echo('<li>' . $clause . '</li>');
  }
  echo('</ul>');
}
