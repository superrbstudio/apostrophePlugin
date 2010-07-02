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
