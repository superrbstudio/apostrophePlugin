<?php
  // Compatible with sf_escaping_strategy: true
  $cancel = isset($cancel) ? $sf_data->getRaw('cancel') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $preview = isset($preview) ? $sf_data->getRaw('preview') : null;
  $revert = isset($revert) ? $sf_data->getRaw('revert') : null;
?>
<?php use_helper('a') ?>
<?php include_component('a', 'area', 
  array('name' => $name, 'refresh' => true, 'preview' => $preview))?>
<?php if ($cancel || $revert): ?>
  <script type="text/javascript">
    $('#a-history-container-<?php echo $name?>').html("");
  </script>
 <?php endif ?>
<?php include_partial('a/globalJavascripts') ?>
