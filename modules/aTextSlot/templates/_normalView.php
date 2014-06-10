<?php
  // Compatible with sf_escaping_strategy: true
  $editable = isset($editable) ? $sf_data->getRaw('editable') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $page = isset($page) ? $sf_data->getRaw('page') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $slot = isset($slot) ? $sf_data->getRaw('slot') : null;
  $value = isset($value) ? $sf_data->getRaw('value') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
?>
<?php use_helper('a') ?>
<?php if ($editable): ?>
<?php include_partial('a/simpleEditWithVariants', array('pageid' => $page->id, 'name' => $name, 'permid' => $permid, 'slot' => $slot, 'page' => $page, 'label' => a_get_option($options, 'editLabel', a_('Edit')))) ?>
<?php endif ?>

<?php if (!strlen($value)): ?>
  <?php if (isset($options['initialText'])): ?>
    <?php // No really, I want this to be the actual ?>
    <?php // default for this slot, it's not the instructions to the editor ?>
    <?php echo $options['initialText'] ?>
  <?php elseif ($editable && isset($options['defaultText'])): ?>
    <?php // bc, meant as instructions, only the editor sees ?>
		<?php echo $options['defaultText'] ?>
  <?php endif ?>
<?php else: ?>
<?php echo $value ?>
<?php endif ?>

