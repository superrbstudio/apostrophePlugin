<?php
  // Compatible with sf_escaping_strategy: true
  $name = isset($name) ? $sf_data->getRaw('name') : null;
?>
<?php use_helper('a') ?>
<?php include_component('a', 'area', 
  array('name' => $name, 'refresh' => true, 'preview' => false, 'options' => $options))?>
<?php include_partial('a/globalJavascripts') ?>
