<?php include_partial('a/simpleEditButton',
  array('name' => $name, 'permid' => $permid)) ?>
<?php include_partial('a/variant',
  array('name' => $name, 'permid' => $permid, 'page' => $page, 'slot' => $slot)) ?>

<?php if (!strlen($value)): ?>
  <?php if ($editable): ?>
    Click edit to add text.
  <?php endif ?>
<?php else: ?>
<?php echo $value ?>
<?php endif ?>

