<?php
  // Compatible with sf_escaping_strategy: true
  $controlsSlot = isset($controlsSlot) ? $sf_data->getRaw('controlsSlot') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $pageid = isset($pageid) ? $sf_data->getRaw('pageid') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $slot = isset($slot) ? $sf_data->getRaw('slot') : null;
?>
<?php if (!isset($controlsSlot)): ?>
  <?php $controlsSlot = true ?>
<?php endif ?>

<?php if ($controlsSlot): ?>
	<?php slot("a-slot-controls-$pageid-$name-$permid") ?>
<?php endif ?>

<?php include_partial('a/simpleEditButton', array('pageid' => $pageid, 'name' => $name, 'permid' => $permid, 'controlsSlot' => false)) ?>

<?php include_partial('a/variant', array('pageid' => $pageid, 'name' => $name, 'permid' => $permid, 'slot' => $slot)) ?>

<?php if ($controlsSlot): ?>
	<?php end_slot() ?>
<?php endif ?>