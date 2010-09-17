<?php
  // Compatible with sf_escaping_strategy: true
  $mediaItem = isset($mediaItem) ? $sf_data->getRaw('mediaItem') : null;
?>

<?php use_helper('a') ?>

<?php $editClass = 'a-btn icon a-edit lite alt' ?>
<?php if ($layout && $layout['name'] == "four-up"): ?>
	<?php $editClass = 'a-btn icon a-edit lite alt no-label' ?>
<?php endif ?>

<?php if ($mediaItem->userHasPrivilege('edit')): ?>
	<ul class="a-ui a-controls">
    <li><?php echo link_to('<span class="icon"></span>'.a_("Edit"), "aMedia/edit", array("query_string" => http_build_query(array("slug" => $mediaItem->getSlug())), "class" => $editClass)) ?></li>
		<li><?php echo link_to('<span class="icon"></span>'.a_("Delete"), "aMedia/delete?" . http_build_query(array("slug" => $mediaItem->getSlug())),array("confirm" => __("Are you sure you want to delete this item?", null, 'apostrophe'), "class"=>"a-btn icon a-delete no-label lite")) ?></li>
	</ul>
<?php endif ?>


<?php if (0): ?>
	<?php if ($mediaItem->getDownloadable() && $sf_params->get('action') == 'show'): ?>
  <li class="a-media-download-original">
     <?php // download link ?>
     <?php echo link_to(__("Download Original", null, 'apostrophe'), "aMediaBackend/original?".http_build_query(array(
             "slug" => $mediaItem->getSlug(),
             "format" => $mediaItem->getFormat())),
              array(
				"class"=>"a-btn icon a-download"
				))?>
	</li>
	<?php endif ?>
<?php endif ?>