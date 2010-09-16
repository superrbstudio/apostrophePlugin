<?php
  // Compatible with sf_escaping_strategy: true
  $mediaItem = isset($mediaItem) ? $sf_data->getRaw('mediaItem') : null;
?>
<?php use_helper('a') ?>
<?php $type = $mediaItem->getType() ?>
<?php $id = $mediaItem->getId() ?>
<?php $domId = 'a-media-thumb-link-' . $id ?>
<?php $serviceUrl = $mediaItem->getServiceUrl() ?>
<?php $slug = $mediaItem->getSlug() ?>
<?php $format = $mediaItem->getFormat() ?>

<?php if (aMediaTools::isSelecting()): ?>
	<?php if (aMediaTools::isMultiple() || ($type === 'image')): ?>
  	<?php // This was more complex before the a.js refactoring ?>
    <?php $linkAttributes = 'href= "#select-image"' ?>
  <?php else: ?>
    <?php // Non-image single select. The multiple add action is a bit of a misnomer here ?>
    <?php // and redirects to aMedia/selected after adding the media item ?>
    <?php $linkAttributes = 'href = "' . url_for("aMedia/multipleAdd?id=$id") . '"' ?>
  <?php endif ?>
<?php else: ?>
  <?php $linkAttributes = 'href = "' . url_for("aMedia/show?" . http_build_query(array("slug" => $slug))) . '"' ?>
<?php endif ?>

<div id="a-media-item-<?php echo $mediaItem->getId() ?>" class="a-ui a-media-item <?php echo ($i%$layout['columns'] < $layout['columns'] - 1)? 'nlast' : 'last' ?> <?php echo $format ?>">

	<div class="a-media-item-thumbnail">
	  <a <?php echo $linkAttributes ?> class="a-media-thumb-link" id="<?php echo $domId ?>">
	    <?php if ($type == 'video'): ?><span class="a-media-play-btn"></span><?php endif ?>
	    <?php if ($mediaItem->getWidth() && ($type == 'pdf')): ?><span class="a-media-pdf-btn"></span><?php endif ?>
	    <?php if ($mediaItem->getWidth()): ?>
	      <img src="<?php echo url_for($mediaItem->getScaledUrl($layout['gallery_constraints'])) ?>" />
	    <?php else: ?>
	      <?php // We can't render this format on this server but we need a placeholder thumbnail ?>
				<span class="a-media-type <?php echo $format ?>" ><b><?php echo $format ?></b></span>
	    <?php endif ?>
	  </a>
	</div>

	<div class="a-media-item-information">
		<ul>
			<?php if(isset($layout['fields']['title'])): ?>
				<li class="a-media-item-title <?php if (!$mediaItem->getWidth()): ?>no-thumbnail<?php endif ?>">
					<h3>
						<div class="a-media-item-controls">
							<?php include_partial('aMedia/editLinks', array('mediaItem' => $mediaItem)) ?>
						</div>							
						<a <?php echo $linkAttributes ?> class="a-media-item-title-link"><?php echo htmlspecialchars($mediaItem->getTitle()) ?></a>
						<?php if ($mediaItem->getViewIsSecure()): ?><span class="a-media-is-secure"></span><?php endif ?>
					</h3>
				</li>
			<?php endif ?>
		
			<?php // John: you could use $mediaItem->format to choose an icon here. Make sure ?>
			<?php // you have a default icon if it's not on your list of awesome icons ?>
			<?php if(isset($layout['fields']['description'])): ?>
				<li class="a-media-item-description"><?php echo $mediaItem->getDescription() ?></li>
			<?php endif ?>

			<?php if(isset($layout['fields']['dimensions'])): ?>
			  <?php if ($mediaItem->getWidth()): ?>
			    <li class="a-media-item-dimensions a-media-item-meta"><?php echo __('<span>Original Dimensions:</span> %width%x%height%', array('%width%' => $mediaItem->getWidth(), '%height%' => $mediaItem->getHeight()), 'apostrophe') ?></li>
			  <?php endif ?>
			<?php endif ?>

			<?php if(isset($layout['fields']['created_at'])): ?>
				<li class="a-media-item-created-at a-media-item-meta"><?php echo __('<span>Uploaded:</span> %date%', array('%date%' => aDate::pretty($mediaItem->getCreatedAt())), 'apostrophe') ?></li>
			<?php endif ?>

			<?php if(isset($layout['fields']['credit'])): ?>
				<li class="a-media-item-credit a-media-item-meta"><?php echo __('<span>Credit:</span> %credit%', array('%credit%' => htmlspecialchars($mediaItem->getCredit())), 'apostrophe') ?></li>
			<?php endif ?>

			<?php if(isset($layout['fields']['categories'])): ?>
				<li class="a-media-item-categories a-media-item-meta"><?php echo __('<span>Categories:</span> %categories%', array('%categories%' => get_partial('aMedia/showCategories', array('categories' => $mediaItem->getMediaCategories()))), 'apostrophe') ?></li>
			<?php endif ?>

			<?php if(isset($layout['fields']['tags'])): ?>
				<li class="a-media-item-tags a-media-item-meta"><?php echo __('<span>Tags:</span> %tags%', array('%tags%' => get_partial('aMedia/showTags', array('tags' => $mediaItem->getTags()))), 'apostrophe') ?></li>
			<?php endif ?>

			<?php if(isset($layout['fields']['link'])): ?>
				<?php if ($mediaItem->getDownloadable()): ?>
				  <li class="a-media-item-link a-media-item-meta">
						<?php echo __('<span>URL:</span> %urlfield%', array('%urlfield%' => 
						'<input type="text" id="a-media-item-link-value-' . $id . '" name="a-media-item-link-value" value="' . url_for("aMediaBackend/original?".http_build_query(array("slug" => $mediaItem->getSlug(),"format" => $mediaItem->getFormat())), true) . '" />'), 'apostrophe') ?>
					</li>
				<?php endif ?>
			<?php endif ?>
		
		</ul>
	</div>
</div>

<?php // TODO: it would be better to have a class for this and enchant all of them in one go ?>
<?php a_js_call('apostrophe.selectOnFocus(?)', '#a-media-item-link-value-' . $id) ?>
<?php a_js_call('apostrophe.setObjectId(?, ?)', $domId, $id) ?>

<script type="text/javascript" charset="utf-8">
// In Progress: Hover expand four-up thumbnails
// Guys, please a_js_call this from the beginning
	// $(document).ready(function() {
				// $('.a-media-item-thumbnail').css('min-height','200px');
		// console.log($('.a-media-item-thumbnail img').height());
	// });
</script>