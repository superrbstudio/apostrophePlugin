<?php use_helper('a', 'jQuery') ?>


<?php foreach ($slots as $permid => $slot): ?>

   <?php if ($infinite): ?>
  	<?php if (isset($options['type_options'][$slot->type])): ?>
  	  <?php $slotOptions = $options['type_options'][$slot->type]; ?>
  	<?php else: ?>
  	  <?php $slotOptions = array() ?>
  	<?php endif ?>
  <?php else: ?>
  	<?php $slotOptions = $options ?>
  <?php endif ?>
  <?php $outlineEditableClass = "" ?>

  <?php slot("a-slot-content-$pageid-$name-$permid") ?>
  <?php a_slot_body($name, $slot->type, $permid, array_merge(array('edit' => false, 'preview' => false), $slotOptions), array(), $slot->isOpen()) ?>
  <?php end_slot() ?>


  <?php include_slot("a-slot-content-$pageid-$name-$permid") ?>

  <?php endforeach ?>

