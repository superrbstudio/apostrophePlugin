<?php
  // Compatible with sf_escaping_strategy: true
  $mediaItem = isset($mediaItem) ? $sf_data->getRaw('mediaItem') : null;
?>

<?php // This linkHref is duplicate code from mediaItem ?>
<?php // This was the quickest / easiest way to ensure $linkHref was defined when mediaItemMeta is returned with Ajax ?>

<?php if (aMediaTools::isSelecting()): ?>
  <?php // When we are selecting downloadables *in general*, we don't want cropping etc., just simple selection ?>
  <?php // When we are selecting single images *specifically*, we do force the cropping UI. ?>
	<?php if (aMediaTools::isMultiple() || (($mediaItem->getType() === 'image') && (aMediaTools::getType() !== '_downloadable'))): ?>
    <?php $linkHref = "#select-media-item"; ?>
  <?php else: ?>
    <?php // Non-image single select. The multiple add action is a bit of a misnomer here ?>
    <?php // and redirects to aMedia/selected after adding the media item ?>
    <?php $linkHref = a_url('aMedia', 'multipleAdd', array('id' => $mediaItem->getId())); ?>
  <?php endif ?>
<?php else: ?>
  <?php $linkHref = url_for('a_media_image_show', array("slug" => $mediaItem->getSlug())); ?>
<?php endif ?>

<ul class="a-ui">
	<?php if(isset($layout['fields']['title'])): ?>
		<li class="a-media-item-title <?php if (!$mediaItem->getWidth()): ?>no-thumbnail<?php endif ?>">
			<h3>
				<div class="a-media-item-controls">
					<?php include_partial('aMedia/editLinks', array('mediaItem' => $mediaItem, 'layout' => $layout)) ?>
				</div>
				<a href="<?php echo $linkHref ?>" class="a-media-item-title-link"><?php echo htmlspecialchars($mediaItem->getTitle()) ?></a>
			</h3>
		</li>
	<?php endif ?>

	<?php // John: you could use $mediaItem->format to choose an icon here. Make sure ?>
	<?php // you have a default icon if it's not on your list of awesome icons ?>
	<?php if(isset($layout['fields']['description'])): ?>
		<li class="a-media-item-description"><?php echo $mediaItem->getDescription() ?></li>
	<?php endif ?>

	<?php if(isset($layout['fields']['link'])): ?>
		<?php if ($mediaItem->getDownloadable()): ?>
		  <li class="a-media-item-link a-media-item-meta a-form-row">
		    <?php // For performance reasons the a_media_image_original route is no longer optional. ?>
		    <?php // See aMediaRouting if you need to know this route's parameters because you have ?>
		    <?php // explicitly disabled our standard routes. ?>
				<?php echo __('<span>Permalink:</span> %urlfield%', array('%urlfield%' =>
				'<input type="text" class="a-select-on-focus" id="a-media-item-link-value-' . $mediaItem->getId() . '" name="a-media-item-link-value" value="' . url_for('@a_media_image_original?' . http_build_query(array("slug" => $mediaItem->getSlug(), "format" => $mediaItem->getFormat())), true) . '" />'), 'apostrophe') ?>
			</li>
		<?php endif ?>
	<?php endif ?>

	<?php if(isset($layout['fields']['created_at'])): ?>
		<li class="a-media-item-created-at a-media-item-meta"><?php echo __('<span>Uploaded:</span> %date%', array('%date%' => aDate::pretty($mediaItem->getCreatedAt())), 'apostrophe') ?></li>
	<?php endif ?>

	<?php if(isset($layout['fields']['dimensions'])): ?>
	  <?php if ($mediaItem->getWidth()): ?>
	    <li class="a-media-item-dimensions a-media-item-meta"><?php echo __('<span>Original Dimensions:</span> %width%x%height%', array('%width%' => $mediaItem->getWidth(), '%height%' => $mediaItem->getHeight()), 'apostrophe') ?></li>
	  <?php endif ?>
	<?php endif ?>

	<?php if(isset($layout['fields']['credit'])): ?>
		<?php if ($mediaItem->getCredit()): ?>
			<li class="a-media-item-credit a-media-item-meta"><?php echo __('<span>Credit:</span> %credit%', array('%credit%' => htmlspecialchars($mediaItem->getCredit())), 'apostrophe') ?></li>
		<?php endif ?>
	<?php endif ?>

	<?php if ($layout['name'] != "four-up"): ?>
		<li class="a-media-item-spacer a-media-item-meta">&nbsp;</li>
	<?php endif ?>

	<?php if(isset($layout['fields']['categories'])): ?>
		<?php if (count($mediaItem->getCategories())): ?>
			<li class="a-media-item-categories a-media-item-meta"><?php echo __('<span>Categories:</span> %categories%', array('%categories%' => get_partial('aMedia/showCategories', array('categories' => $mediaItem->getCategories()))), 'apostrophe') ?></li>
		<?php endif ?>
	<?php endif ?>

	<?php if(isset($layout['fields']['tags'])): ?>
		<?php if (count($mediaItem->getTags())): ?>
			<li class="a-media-item-tags a-media-item-meta"><?php echo __('<span>Tags:</span> %tags%', array('%tags%' => get_partial('aMedia/showTags', array('tags' => $mediaItem->getTags()))), 'apostrophe') ?></li>
		<?php endif ?>
	<?php endif ?>

	<?php if(isset($layout['fields']['view_is_secure'])): ?>
			<li class="a-media-item-permissions a-media-item-meta">
				<?php if ($mediaItem->getViewIsSecure()): ?>
				  <?php // i18n the type name, then insert it as a placeholder in an i18n'd phrase. Avoids having to i18n a dozen separate phrases ?>
					<span class="a-media-item-permissions-icon private"></span><?php echo a_('This %type% is private.', array('%type%' => a_($mediaItem->type))) ?>
				<?php else: ?>
					<?php if (0): ?>
						<span class="a-media-item-permissions-icon public"></span><?php echo a_('This %type% can be viewed by everyone.', array('%type%' => a_($mediaItem->type))) ?>
					<?php endif ?>
				<?php endif ?>
			</li>
	<?php endif ?>

	<?php //this li for the replace and download links can be a partial so it can be used in the edit view. ?>
	<?php if(isset($layout['fields']['downloadable'])): ?>
		<?php if ($mediaItem->getType() !== 'video'): ?>
			<li class="a-media-item-download a-media-item-meta">
				<?php echo link_to(__("%buttonspan%Download Original", array('%buttonspan%' => '<span class="icon"></span>'), 'apostrophe'),	"aMediaBackend/original?" .http_build_query(array("slug" => $mediaItem->getSlug(), "format" => $mediaItem->getFormat())), array("class"=>"a-btn icon a-download lite alt")) ?>
			</li>
		<?php endif ?>
	<?php endif ?>
</ul>
