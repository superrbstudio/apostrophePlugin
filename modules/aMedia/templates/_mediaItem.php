<?php
  // Compatible with sf_escaping_strategy: true
  $mediaItem = isset($mediaItem) ? $sf_data->getRaw('mediaItem') : null;
?>
<?php use_helper('a') ?>
<?php $domId = 'a-media-thumb-link-' . $mediaItem->getId() ?>
<?php (isset($layout['showSuccess'])) ? $displayWidth = 720 : $displayWidth = 340 ?>

<?php if (aMediaTools::isSelecting()): ?>
	<?php if (aMediaTools::isMultiple() || ($mediaItem->getType() === 'image')): ?>
  	<?php // This was more complex before the a.js refactoring ?>
    <?php $linkAttributes = 'href= "#select-image"' ?>
  <?php else: ?>
    <?php // Non-image single select. The multiple add action is a bit of a misnomer here ?>
    <?php // and redirects to aMedia/selected after adding the media item ?>
    <?php $linkAttributes = 'href = "' . url_for("aMedia/multipleAdd?id=$mediaItem->getId()") . '"' ?>
  <?php endif ?>
<?php else: ?>
  <?php $linkAttributes = 'href = "' . url_for("aMedia/show?" . http_build_query(array("slug" => $mediaItem->getSlug()))) . '"' ?>
<?php endif ?>

<div id="a-media-item-<?php echo $mediaItem->getId() ?>" class="a-ui a-media-item <?php echo ($i%$layout['columns'] == 0)? 'first':'' ?> <?php echo ($i%$layout['columns'] < $layout['columns'] - 1)? '' : 'last' ?> a-format-<?php echo $mediaItem->getFormat() ?> a-type-<?php echo $mediaItem->getType() ?><?php echo ($mediaItem->getEmbeddable()) ? ' a-embedded-item':'' ?>">

	<?php if (!isset($layout['showSuccess'])): ?>
	<div class="a-media-item-thumbnail">
	  <a <?php echo $linkAttributes ?> class="a-media-thumb-link" id="<?php echo $domId ?>">

			<?php // Embeddable Media (Videos) ?>
	    <?php if ($mediaItem->getEmbeddable()): ?><span class="a-media-play-btn"></span><?php endif ?>

			<?php // PDFs with an image preview ?>
	    <?php if ($mediaItem->getWidth() && ($mediaItem->getType() == 'pdf')): ?><span class="a-media-pdf-btn"></span><?php endif ?>

			<?php // Audio Files ?>
      <?php if ($mediaItem->getType() == 'audio'): ?>
  			<?php $playerOptions = array('width' => $displayWidth, 'download' => true, 'player' => 'lite') ?>
  			<?php include_partial('aAudioSlot/'.$playerOptions['player'].'Player', array('item' => $mediaItem, 'uniqueID' => $mediaItem->getId(), 'options' => $playerOptions)) ?>			
  		<?php else: ?>
			<?php // Images or anything else with an image thumbnail ?>	
  			<?php if ($mediaItem->getWidth()): ?>
 	      	<img src="<?php echo url_for($mediaItem->getScaledUrl(aMediaTools::getOption('gallery_constraints'))) ?>" />						
  	    <?php else: ?>
			<?php // Files (Word Docs, Powerpoints, Spreadsheets) ?>	
  	      <?php // We can't render this format on this server but we need a placeholder thumbnail ?>
  				<span class="a-media-type <?php echo $mediaItem->getFormat() ?>" ><b><?php echo $mediaItem->getFormat() ?></b></span>
  	    <?php endif ?>			
  		<?php endif ?>
	  </a>
	</div>
	<?php endif ?>
	
	<?php if ($mediaItem->getEmbeddable()): ?>
	<div class="a-media-item-embed<?php echo (!isset($layout['showSuccess']))? ' a-hidden':'' ?>">
		<?php // Until we can get real dimensions from an embed ?>
		<?php // Let's just make a 4:3 aspect object ?>
		<?php echo $mediaItem->getEmbedCode($displayWidth, false, 'c', $mediaItem->getFormat(), false) ?>
	</div>
	<?php endif ?>

	<div class="a-media-item-information">
		<?php include_partial('aMedia/mediaItemMeta', array('mediaItem' => $mediaItem, 'layout' => $layout, 'linkAttributes' => $linkAttributes)) ?>
	</div>

</div>

<?php a_js_call('apostrophe.setObjectId(?, ?)', $domId, $mediaItem->getId()) ?>