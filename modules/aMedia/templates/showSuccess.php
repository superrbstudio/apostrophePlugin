<?php
  // Compatible with sf_escaping_strategy: true
  $mediaItem = isset($mediaItem) ? $sf_data->getRaw('mediaItem') : null;
?>
<?php use_helper('a') ?>

<?php $type = aMediaTools::getAttribute('type') ?>
<?php $selecting = aMediaTools::isSelecting() ?>
<?php $multipleStyle = (($type === 'image') || (aMediaTools::isMultiple())) ?>

<?php $body_class = 'a-media a-media-show'?>
<?php $body_class .= ($selecting) ? ' a-media-selecting a-previewing':'' ?>
<?php slot('body_class', $body_class) ?>

<?php $id = $mediaItem->getId() ?>
<?php $i = 1 ?>
<?php // If we can't render this format on this particular server platform (ie PDF on Windows), ?>
<?php // leave the embed code unset and don't try to echo it later ?>
<?php if ($mediaItem->getWidth()): ?>
  <?php $options = aDimensions::constrain($mediaItem->getWidth(), $mediaItem->getHeight(),
    $mediaItem->getFormat(), aMediaTools::getOption('show_constraints')) ?>
  <?php $embedCode = $mediaItem->getEmbedCode(
    $options['width'], $options['height'], $options['resizeType'], $options['format']) ?>
<?php else: ?>
  <?php $options = array() ?>
  <?php $format = $mediaItem->getFormat() ?>
  <?php $embedCode = '<span class="a-media-type '.$format.'"><b>'.$format.'</b></span>' ?>
<?php endif ?>

<?php slot('a-page-header') ?>
	<?php include_partial('aMedia/mediaHeader', array('uploadAllowed' => $uploadAllowed, 'embedAllowed' => $embedAllowed)) ?>
<?php end_slot() ?>

<div class="a-media-library">	
	<div class="a-media-items">		
    <?php include_partial('aMedia/mediaItem', array('mediaItem' => $mediaItem, 'options' => $options,  'layout' => $layout, 'i' => $i, 'selecting' => $selecting, 'autoplay' => true)) ?>
	</div>
</div>

<?php // Media Sidebar is wrapped slot('a-subnav') ?>
<?php include_component('aMedia', 'browser') ?>

<?php a_js_call('apostrophe.selectOnFocus(?)', '.a-select-on-focus') ?>