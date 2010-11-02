<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
  $item = isset($item) ? $sf_data->getRaw('item') : null;
	$popularTags = isset($popularTags) ? $sf_data->getRaw('popularTags') : null;
	$allTags = isset($allTags) ? $sf_data->getRaw('allTags') : null;
?>
<?php use_helper('a') ?>
<?php include_partial('aMedia/edit', array('item' => $item, 'form' => $form, 'withPreview' => false, 'popularTags' => $popularTags, 'allTags' => $allTags)) ?>
<?php include_partial('a/globalJavascripts') ?>
