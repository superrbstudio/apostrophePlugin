<?php
  // Compatible with sf_escaping_strategy: true
  $editable = isset($editable) ? $sf_data->getRaw('editable') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
  $page = isset($page) ? $sf_data->getRaw('page') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $value = isset($value) ? $sf_data->getRaw('value') : null;
?>
<?php use_helper('a') ?>
<?php if ($editable): ?>
<?php include_partial('a/simpleEditButton', array('pageid' => $page->id, 'name' => $name, 'permid' => $permid, 'slot' => $slot, 'label' => a_get_option($options, 'editLabel', a_('Edit')))) ?>
<?php endif ?>

<?php if (!strlen($value)): ?>
  <?php if ($editable): ?>
		<?php include_partial('aRawHTMLSlot/help', array('options' => $options)) ?>
  <?php endif ?>
<?php else: ?>
  <?php if ($sf_params->get('safemode')): ?>
    <?php echo htmlspecialchars($value) ?>
  <?php else: ?>
    <?php echo $value ?>
  <?php endif ?>
<?php endif ?>

