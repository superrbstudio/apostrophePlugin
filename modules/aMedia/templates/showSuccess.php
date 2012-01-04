<?php
  // Compatible with sf_escaping_strategy: true
  $mediaItem = isset($mediaItem) ? $sf_data->getRaw('mediaItem') : null;
  $layout = isset($layout) ? $sf_data->getRaw('layout') : null;
?>
<?php use_helper('a') ?>

<?php $type = aMediaTools::getAttribute('type') ?>
<?php $selecting = aMediaTools::isSelecting() ?>

<?php $body_class = 'a-media a-media-show'?>
<?php $body_class .= ($page->admin) ? ' aMediaAdmin':'' ?>
<?php $body_class .= ($selecting) ? ' a-media-selecting a-previewing':'' ?>
<?php slot('body_class', $body_class) ?>

<?php $i = 1 ?>

<?php slot('a-page-header') ?>
	<?php include_partial('aMedia/mediaHeader', array('uploadAllowed' => $uploadAllowed, 'embedAllowed' => $embedAllowed)) ?>
<?php end_slot() ?>

<div class="a-media-library">
	<div class="a-media-items">
    <?php include_partial('aMedia/mediaItem', array('mediaItem' => $mediaItem, 'layout' => $layout, 'i' => $i, 'selecting' => $selecting, 'autoplay' => true)) ?>
	</div>
</div>

<?php // Media Sidebar is wrapped slot('a-subnav') ?>
<?php include_component('aMedia', 'browser') ?>

<?php a_js_call('apostrophe.selectOnFocus(?)', '.a-select-on-focus') ?>