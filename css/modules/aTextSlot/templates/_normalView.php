<?php use_helper('I18N') ?>
<?php include_partial('a/simpleEditWithVariants', array('pageid' => $page->id, 'name' => $name, 'permid' => $permid, 'slot' => $slot, 'page' => $page)) ?>

<?php if (!strlen($value)): ?>
  <?php if ($editable): ?>
    <?php echo __('Click edit to add text.', null, 'apostrophe') ?>
  <?php endif ?>
<?php else: ?>
<?php echo $value ?>
<?php endif ?>

