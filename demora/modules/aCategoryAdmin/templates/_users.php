<?php
  // Compatible with sf_escaping_strategy: true
  $a_category = isset($a_category) ? $sf_data->getRaw('a_category') : null;
  $i = isset($i) ? $sf_data->getRaw('i') : null;
?>
<?php $i=1 ?>
<?php foreach($a_category->Users as $user): ?>
<?php echo $user ?><?php if($i < count($a_category->Users)): ?>, <?php endif ?>
<?php $i++ ?>
<?php endforeach ?>
