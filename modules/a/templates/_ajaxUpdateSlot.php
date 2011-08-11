<?php
  // Compatible with sf_escaping_strategy: true
  $editorOpen = isset($editorOpen) ? $sf_data->getRaw('editorOpen') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
  $pageid = isset($pageid) ? $sf_data->getRaw('pageid') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $type = isset($type) ? $sf_data->getRaw('type') : null;
  $validationData = isset($validationData) ? $sf_data->getRaw('validationData') : null;
  $variant = isset($variant) ? $sf_data->getRaw('variant') : null;
  $slot = isset($slot) ? $sf_data->getRaw('slot') : null;
?>
<?php // 1.3 and up don't do this automatically (no common filter) ?>
<?php // Note that you cannot add more JS and CSS files in an ajax slot update. You are ?>
<?php // expected to load those with the page. This avoids a lot of chicken and egg problems ?>
<?php // with double-loading of CSS and JS files. Prior to this commit there was code here to ?>
<?php // include CSS and JS, however it didn't work anyway - that code was run before a_slot_body and ?>
<?php // therefore never had anything to add ?>
<?php use_helper('a') ?>
<?php a_slot_body($name, $type, $permid, $options, $validationData, $editorOpen, true) ?>
<?php if (!$slot->isNew()): ?>
  <?php a_js_call('apostrophe.slotNotNew(?, ?, ?)', $pageid, $name, $permid) ?>
<?php endif ?>
<?php a_js_call('apostrophe.areaUpdateMoveButtons(?, ?, ?)', a_url('a', 'moveSlot'), $pageid, $name) ?>

<?php if (isset($variant)): ?>
  <?php a_js_call('apostrophe.slotHideVariantsMenu(?)', "#a-$pageid-$name-$permid-variant ul.a-variant-options") ?>
<?php endif ?>

<?php include_partial('a/globalJavascripts') ?>

