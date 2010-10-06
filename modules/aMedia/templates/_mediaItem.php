<?php
  // Compatible with sf_escaping_strategy: true
  $mediaItem = isset($mediaItem) ? $sf_data->getRaw('mediaItem') : null;
?>
<?php use_helper('a') ?>
<?php $domId = 'a-media-thumb-link-' . $mediaItem->getId() ?>
<?php $galleryConstraints = aMediaTools::getOption('gallery_constraints'); ?>
<?php (isset($layout['showSuccess'])) ? $embedConstraints = aMediaTools::getOption('show_constraints') : $embedConstraints = $galleryConstraints ?>

<?php if (aMediaTools::isSelecting()): ?>
	<?php if (aMediaTools::isMultiple() || ($mediaItem->getType() === 'image')): ?>
    <?php $linkAttributes = "/#select-media-item"; ?>
  <?php else: ?>
    <?php // Non-image single select. The multiple add action is a bit of a misnomer here ?>
    <?php // and redirects to aMedia/selected after adding the media item ?>
    <?php $linkAttributes = url_for('aMedia/multipleAdd?id=' . $mediaItem->getId()); ?>
  <?php endif ?>
<?php else: ?>
  <?php $linkAttributes = url_for("aMedia/show?" . http_build_query(array("slug" => $mediaItem->getSlug()))); ?>
<?php endif ?>

<div id="a-media-item-<?php echo $mediaItem->getId() ?>" class="a-ui a-media-item <?php echo ($i%$layout['columns'] == 0)? 'first':'' ?> <?php echo ($i%$layout['columns'] < $layout['columns'] - 1)? '' : 'last' ?> a-format-<?php echo $mediaItem->getFormat() ?> a-type-<?php echo $mediaItem->getType() ?><?php echo ($mediaItem->getEmbeddable()) ? ' a-embedded-item':'' ?>">

	<?php if (!isset($layout['showSuccess']) || ($layout['showSuccess'] && !$mediaItem->getEmbeddable())): ?>
	<div class="a-media-item-thumbnail">
	  <a href="<?php echo $linkAttributes ?>" class="a-media-thumb-link" id="<?php echo $domId ?>">

			<?php // Embeddable Media (Videos) ?>
	    <?php if ($mediaItem->getEmbeddable()): ?>
	      <?php if ($mediaItem->getImageAvailable()): ?>
	        <span class="a-media-play-btn"></span>
	      <?php endif ?>
      <?php endif ?>
        
			<?php if (aMediaTools::isSelecting()): ?>
				<span class="a-media-select-overlay" title="<?php echo a_('Click to select this item.') ?>"><span><?php echo a_('Click to select this item.') ?></span></span>
			<?php endif ?>
			
			<?php // PDFs with an image preview ?>
	    <?php if ($mediaItem->getWidth() && ($mediaItem->getType() == 'pdf')): ?><span class="a-media-pdf-btn"></span><?php endif ?>

			<?php // Images or anything else with an image thumbnail ?>	
 			<?php if ($mediaItem->getImageAvailable()): ?>
	      	<img src="<?php echo url_for($mediaItem->getScaledUrl(aMediaTools::getOption('gallery_constraints'))) ?>" />						
 	    <?php else: ?>
				<?php // Files (Word Docs, Powerpoints, Spreadsheets) ?>	
 	      <?php // We can't render this format on this server but we need a placeholder thumbnail ?>
 				<span class="a-media-type <?php echo $mediaItem->getFormat() ?>" ><b><?php echo $mediaItem->getFormat() ?></b></span>
 	    <?php endif ?>			
	  </a>
	</div>
	<?php endif ?>

  <?php if ($mediaItem->getType() == 'audio'): ?>
		<?php $playerOptions = array('width' => (($layout['name'] == 'four-up') ? 165 : $galleryConstraints['width']), 'download' => false, 'player' => 'lite') ?>
		<?php include_partial('aAudioSlot/'.$playerOptions['player'].'Player', array('item' => $mediaItem, 'uniqueID' => $mediaItem->getId(), 'options' => $playerOptions)) ?>			
	<?php endif ?>
	
	<?php if ($mediaItem->getEmbeddable()): ?>
	<div class="a-media-item-embed<?php echo (!isset($layout['showSuccess']))? ' a-hidden':'' ?>">
		<?php echo $mediaItem->getEmbedCode($embedConstraints['width'], $embedConstraints['height'], $embedConstraints['resizeType'], $mediaItem->getFormat()) ?>
	</div>
	<?php endif ?>

	<div class="a-media-item-information">
		<?php include_partial('aMedia/mediaItemMeta', array('mediaItem' => $mediaItem, 'layout' => $layout, 'linkAttributes' => $linkAttributes)) ?>
	</div>

</div>

<?php a_js_call('apostrophe.setObjectId(?, ?)', $domId, $mediaItem->getId()) ?>