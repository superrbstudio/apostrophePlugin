<?php
  // Compatible with sf_escaping_strategy: true
  $a_category = isset($a_category) ? $sf_data->getRaw('a_category') : null;
?>
<?php echo link_to($a_category->name, '@a_category_admin_edit?id='.$a_category->id) ?>
