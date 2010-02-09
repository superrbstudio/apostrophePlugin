<?php if (!isset($controlsSlot)): ?>
  <?php $controlsSlot = true ?>
<?php endif ?>

<?php if ($controlsSlot): ?>
	<?php slot("a-slot-controls-$pageid-$name-$permid") ?>
<?php endif ?>

	<?php include_partial('a/simpleEditButton', array('pageid' => $page->id, 'name' => $name, 'permid' => $permid, 'slot' => $slot, 'page' => $page)) ?>
	<?php include_partial('a/variant', array('pageid' => $page->id, 'name' => $name, 'permid' => $permid, 'page' => $page, 'slot' => $slot)) ?>
	
<?php if ($controlsSlot): ?>
	<?php end_slot() ?>
<?php endif ?>

<?php if (!strlen($value)): ?>

  <?php if ($editable): ?>
    Click edit to add text.
  <?php endif ?>

<?php else: ?>
	
	<?php echo $value ?>
	
<?php endif ?>

