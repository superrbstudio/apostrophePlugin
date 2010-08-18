<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
?>
<?php // Just echo the form. You might want to render the form fields differently ?>
<?php echo $form ?>