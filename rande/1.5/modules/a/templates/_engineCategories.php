<?php // Include this partial from your myengine/settings partial as needed ?>
<?php
  use_helper('a');
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
?>
<?php // Don't let a text field be output that winds up shadowing our array of DHTML checkboxes. ?>
<?php // TODO: come up with a better solution (hidden fields are even more problematic in that they ?>
<?php // are echoed even by renderHiddenFormFields(). ?>
<?php unset($form['categories_list_add'])?>
<?php echo $form ?>
<?php $options = array('choose-one' => a_('Select to Add')) ?>
<?php if ($sf_user->hasCredential('admin')): ?>
  <?php $options['add'] = a_('+ Add New Category') ?>
<?php endif ?>
<?php a_js_call('aMultipleSelect(?, ?)', '.a-engine-page-settings', $options) ?>
