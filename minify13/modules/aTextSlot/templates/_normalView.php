<?php include_partial('a/simpleEditWithVariants', array('pageid' => $page->id, 'name' => $name, 'permid' => $permid, 'slot' => $slot, 'page' => $page)) ?>

<?php if (!strlen($value)): ?>
  <?php if ($editable): ?>
    Click edit to add text.
  <?php endif ?>
<?php else: ?>
<?php echo $value ?>
<?php endif ?>

