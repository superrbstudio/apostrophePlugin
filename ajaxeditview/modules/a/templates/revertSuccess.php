<?php use_helper('jQuery') ?>
<?php include_component('a', 'area', 
  array('name' => $name, 'refresh' => true, 'preview' => $preview))?>
<?php if ($cancel || $revert): ?>
  <script type="text/javascript" charset="utf-8">
    $('#a-history-container-<?php echo $name?>').html("");
  </script>
 <?php endif ?>
