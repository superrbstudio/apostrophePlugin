<?php // Compatible with sf_escaping_strategy: true
  $items = isset($items) ? $sf_data->getRaw('items') : null;
?>

<?php use_helper('a') ?>

<?php foreach ($items as $item): ?>
	<?php $id = $item->getId() ?>
	<?php $domId = "a-media-selection-list-item-$id" ?>
	<li id="<?php echo $domId ?>" class="a-media-selection-list-item">
		<ul class="a-ui a-controls a-over">	
			<li>
				<?php echo link_to('<span class="icon"></span>'.__("Edit", null, 'apostrophe'), "aMedia/edit", array("query_string" => http_build_query(array("slug" => $item->getSlug())), "class" => "a-btn icon no-label a-edit")) ?>
			</li>
			<li>
			  <?php echo content_tag('a', '<span class="icon"></span>'.a_("Crop"), array('href' => '#', 'class' => 'a-btn icon a-crop no-label', 'title' => a_('Crop'))) ?>
			</li>
			<li>
			  <?php echo content_tag('a', '<span class="icon"></span>'.a_("remove this item"), array('href' => '#', 'class' => 'a-btn icon a-delete no-label', 'title' => a_('Remove'))) ?>
			</li>
		</ul>	
	  <?php if (aMediaTools::isMultiple()): ?>
	 		<div class="a-media-selected-item-drag-overlay" title="<?php echo __('Drag &amp; Drop to Order', null, 'apostrophe') ?>"></div>
	  <?php endif ?>	
		<div class="a-media-selected-item-overlay"></div>
	  <div class="a-thumbnail-container" style="background: url('<?php echo url_for($item->getCropThumbnailUrl()) ?>'); overflow: hidden;"><img src="<?php echo url_for($item->getCropThumbnailUrl()) ?>" class="a-thumbnail" style="visibility:hidden;" /></div>
	</li>
	<?php a_js_call('apostrophe.setObjectId(?, ?)', $domId, $id) ?>
<?php endforeach ?>

<?php a_js_call('apostrophe.mediaEnableSelect(?)', array(
  'setCropUrl' => url_for('aMedia/crop'),
  'removeUrl' => url_for('aMedia/multipleRemove'), 
  'updateMultiplePreviewUrl' => url_for('aMedia/updateMultiplePreview'),
  'multipleAddUrl' => url_for('aMedia/multipleAdd'),
  'ids' => aMediaTools::getSelection(),
  'aspectRatio' => aMediaTools::getAspectRatio(),
  'minimumSize' => array(aMediaTools::getAttribute('minimum-width'), aMediaTools::getAttribute('minimum-height')),
  'maximumSize' => array(aMediaTools::getAttribute('maximum-width'), aMediaTools::getAttribute('maximum-height')),
  // width height cropLeft cropTop cropWidth cropHeight hashed by image id
  'imageInfo' => aMediaTools::getAttribute('imageInfo'))) ?>