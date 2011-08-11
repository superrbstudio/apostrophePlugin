<?php
  // Compatible with sf_escaping_strategy: true
  $mediaItem = isset($mediaItem) ? $sf_data->getRaw('mediaItem') : null;
?>

<?php use_helper('a') ?>

<?php $editClass = 'a-btn icon a-edit lite alt' ?>
<?php if (isset($layout) && $layout['name'] == "four-up"): ?>
	<?php $editClass = 'a-btn icon a-edit lite alt no-label' ?>
<?php endif ?>

<?php if ($mediaItem->userHasPrivilege('edit')): ?>

	<ul class="a-ui a-controls">
    <li><?php echo link_to('<span class="icon"></span>'.a_("Edit"), "aMedia/edit", array("query_string" => http_build_query(array("slug" => $mediaItem->getSlug())), "class" => $editClass, 'id' => 'a-media-item-edit-button-'.$mediaItem->getId())) ?></li>
		<li><?php echo link_to('<span class="icon"></span>'.a_("Delete"), "aMedia/delete?" . http_build_query(array("slug" => $mediaItem->getSlug())),array("confirm" => __("Are you sure you want to delete this item?", null, 'apostrophe'), "class"=>"a-btn icon a-delete no-label lite alt")) ?></li>
	</ul>

	<?php if ($layout['name'] != "four-up"): ?>		
		<?php a_js_call('apostrophe.linkToRemote(?)', array(
			'link' => '#a-media-item-edit-button-'.$mediaItem->getId(), 
			'url' => url_for('a_media_edit', array("slug" => $mediaItem->getSlug())), 
			'update' => '#a-media-item-'.$mediaItem->getId().' .a-media-item-information', 
			'method' => 'GET',
			'restore' => true, 
		)) ?>
	<?php endif ?>

<?php endif ?>
