<?php
  // Compatible with sf_escaping_strategy: true
  $mediaItem = isset($mediaItem) ? $sf_data->getRaw('mediaItem') : null;
  $layout = isset($layout) ? $sf_data->getRaw('layout') : null;
?>

<?php use_helper('a') ?>
<?php $domId = 'a-media-thumb-link-' . $mediaItem->getId() ?>
<?php $galleryConstraints = aMediaTools::getOption('gallery_constraints'); ?>
<?php (isset($layout['showSuccess'])) ? $embedConstraints = aMediaTools::getOption('show_constraints') : $embedConstraints = $galleryConstraints ?>
<?php $showUrl = url_for('a_media_image_show', array("slug" => $mediaItem->getSlug())) ?>
<?php $embeddable = $mediaItem->getEmbeddable() ?>
<?php isset($autoplay) ? $autoplay : $autoplay = false ?>
<?php if ($selecting): ?>
  <?php // When we are selecting downloadables *in general*, we don't want cropping etc., just simple selection ?>
  <?php // When we are selecting single images *specifically*, we do force the cropping UI. ?>
	<?php if (aMediaTools::isMultiple() || (($mediaItem->getType() === 'image') && (aMediaTools::getType() !== '_downloadable'))): ?>
    <?php $linkHref = "#select-media-item"; ?>
    <?php $multipleStyleSelect = true ?>
  <?php else: ?>
    <?php // Non-image single select. The multiple add action is a bit of a misnomer here ?>
    <?php // and redirects to aMedia/selected after adding the media item ?>
    <?php $linkHref = a_url('aMedia', 'multipleAdd', array('id' => $mediaItem->getId())) ?>
    <?php $multipleStyleSelect = false ?>
  <?php endif ?>
<?php else: ?>
  <?php $linkHref = $showUrl; ?>
<?php endif ?>

<div id="a-media-item-<?php echo $mediaItem->getId() ?>" class="a-ui a-media-item <?php echo ($i%$layout['columns'] == 0)? 'first':'' ?> <?php echo ($i%$layout['columns'] < $layout['columns'] - 1)? '' : 'last' ?> a-format-<?php echo $mediaItem->getFormat() ?> a-type-<?php echo $mediaItem->getType() ?><?php echo ($embeddable) ? ' a-embedded-item':'' ?>">
    
	<div class="a-media-item-thumbnail">
	  
		<?php if ($embeddable && !(!$selecting && isset($layout['showSuccess']))): ?>
			<div class="a-media-select-overlay<?php echo ($selecting) ? ' selecting':'' ?>">
				<?php if ($selecting): ?>
          <?php // Include $domId so we can support multiple video selections ?>
          <?php // Use a DOM attribute so replacing this with a span doesn't clobber it ?>
					<a class="a-media-select-video" id="<?php echo $domId ?>" href="<?php echo $linkHref ?>" title="<?php echo a_('Click to select this item.') ?>">
						<span class="label">
							<span class="label-wrapper"><?php echo a_('Select') ?></span>
						</span>
					</a>
				<?php endif ?>
					<a class="a-media-play-video" href="<?php echo $showUrl ?>" title="<?php echo a_('Click to play this item.') ?>">
						<span class="label">
							<span class="label-wrapper"><?php echo a_('Play') ?><span class="icon"><?php echo image_tag('/apostrophePlugin/images/a-icon-video.png', array('height' => 20, 'width' => 20)) ?></span></span>
						</span>
					</a>
			</div>
		<?php endif ?>
		
		<?php if (!($embeddable && isset($layout['showSuccess']))): ?>	
		  <a href="<?php echo $linkHref ?>" class="a-media-thumb-link" id="<?php echo $domId ?>">      
			
				<?php // PDFs with an image preview ?>
		    <?php if ($mediaItem->getWidth() && ($mediaItem->getType() == 'pdf')): ?><span class="a-media-pdf-btn"></span><?php endif ?>

				<?php // Images or anything else with an image thumbnail ?>	
	 			<?php if ($mediaItem->getImageAvailable()): ?>
	      	<span class="a-media-item-thumbnail-span" style="background-image: url('<?php echo url_for($mediaItem->getScaledUrl(($embedConstraints))) ?>')">
						<img class="a-media-item-thumbnail-image" src="<?php echo url_for($mediaItem->getScaledUrl(($embedConstraints))) ?>" />						
					</span>
	 	    <?php else: ?>
				
					<?php if ($embeddable): ?>
						<span class="a-media-video-placeholder"></span>
					<?php else: ?>
						<?php // Files (Word Docs, Powerpoints, Spreadsheets) and embedded items with no preview available ?>	
		 	      <?php // We can't render this format on this server but we need a placeholder thumbnail ?>
		 				<span class="a-media-type <?php echo $mediaItem->getType() ?> <?php echo $mediaItem->getFormat() ?>" ><b><?php echo strlen($mediaItem->getFormat()) ? $mediaItem->getFormat() : a_($mediaItem->getType()) ?></b></span>
					<?php endif ?>
	 	    <?php endif ?>			
		  </a>
		<?php endif ?>
		
		<?php if (($layout['name'] != 'four-up') && $embeddable): ?>
		<?php // four-up is not playing videos in place, they clickthrough to show-success. So there's no reason to weight down the page with hidden embed elements ?>
		<div class="a-media-item-embed<?php echo (!isset($layout['showSuccess']))? ' a-hidden':'' ?>">
			<?php $embedCode = $mediaItem->getEmbedCode($embedConstraints['width'], $embedConstraints['height'], $embedConstraints['resizeType'], $mediaItem->getFormat(), false, 'opaque', $autoplay) ?>
			<?php if (isset($layout['showSuccess']) && $layout['showSuccess']): ?>
				<?php // Just output the embed if it is showSuccess ?>
				<?php echo $embedCode ?>
			<?php else: ?>
				<?php // Attach the embed code as data to the media item if it's indexSuccess ?>
				<?php a_js_call('apostrophe.mediaAttachEmbed(?)', array('id' => $mediaItem->getId(), 'embed' => $embedCode)) ?>				
			<?php endif ?>
		</div>
		<?php endif ?>

	</div>

  <?php if ($mediaItem->getType() == 'audio'): ?>
		<?php $playerOptions = array('width' => (($layout['name'] == 'four-up') ? 165 : $galleryConstraints['width']), 'download' => false, 'player' => 'lite') ?>
		<?php include_partial('aAudioSlot/'.$playerOptions['player'].'Player', array('item' => $mediaItem, 'uniqueID' => $mediaItem->getId(), 'options' => $playerOptions)) ?>			
	<?php endif ?>
	
	<div class="a-media-item-information">
		<?php include_partial('aMedia/mediaItemMeta', array('mediaItem' => $mediaItem, 'layout' => $layout)) ?>
	</div>

</div>

<?php a_js_call('apostrophe.setObjectId(?, ?)', $domId, $mediaItem->getId()) ?>