<?php
  // Compatible with sf_escaping_strategy: true
  $mediaItem = isset($mediaItem) ? $sf_data->getRaw('mediaItem') : null;
?>
<?php use_helper('a') ?>
<?php slot('body_class','a-media a-media-show') ?>
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
  <?php $format = $mediaItem->getFormat() ?>
  <?php $embedCode = '<span class="a-media-type '.$format.'"><b>'.$format.'</b></span>' ?>
<?php endif ?>

<div class="a-media-library">	
	<div class="a-media-items">

		<ul class='a-controls a-media-controls a-ui'>
			<li><?php echo link_to('<span class="icon"></span>'.__('Media Library', null, 'apostrophe'), '@a_media_index', array('class' => 'a-btn big icon a-arrow-left', 'id' => 'media-library-back-button', ))?></li>
		</ul>
		
    <?php include_partial('aMedia/mediaItem', array('mediaItem' => $mediaItem, 'options' => $options,  'layout' => $layout, 'i' => $i )) ?>

	</div>
</div>

<?php // Media Sidebar is wrapped slot('a-subnav') ?>
<?php include_component('aMedia', 'browser') ?>

<?php a_js_call('apostrophe.selectOnFocus(?)', '.a-select-on-focus') ?>