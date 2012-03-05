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

  <?php if ($editable): ?>
    <?php // Marked this with a span so it can be hidden in preview mode ?>
    <span class="a-placeholder-for-editors"><?php echo __('Click edit to add text.', null, 'apostrophe') ?></span>
  <?php endif ?>

<?php else: ?>
	
	<?php echo $value ?>
	
<?php endif ?>

