<div class="a-media-select">
<?php $type = aMediaTools::getAttribute('type') ?>
<?php if (!$type): ?>
<?php $type = "media item" ?>
<?php endif ?>
	<p>Select one or more <?php echo $type ?>s by clicking on them below. Drag and drop <?php echo $type ?>s to reorder them within the list of selected items. Remove <?php echo $type ?>s by clicking on the trashcan.
  <?php if ($limitSizes): ?>
  Only appropriately sized <?php echo $type ?>s are shown.
  <?php endif ?>
  When you're done, click "Save."</p>

	<ul id="a-media-selection-list">
	<?php include_component("aMedia", "multipleList") ?>
	</ul>

	<?php echo jq_sortable_element("#a-media-selection-list", array("url" => "aMedia/multipleOrder")) ?>

	<br class="c"/>

	<ul class="a-controls a-media-slideshow-controls">
		<li><?php echo link_to("Save", "aMedia/selected", array("class"=>"a-btn save")) ?></li>
 	  <li><?php echo link_to("cancel", "aMedia/selectCancel", array("class"=>"a-btn icon a-cancel event-default")) ?></li>
	</ul>
	
</div>
	<br class="c"/>