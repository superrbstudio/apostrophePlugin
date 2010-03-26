<?php use_helper('I18N') ?>
<div class="a-media-select">
<?php $type = aMediaTools::getAttribute('type') ?>
<?php if (!$type): ?>
<?php $type = "media item" ?>
<?php endif ?>
	<p><?php echo __('Select one or more %typeplural% by clicking on them below. Drag and drop %typeplural%  to reorder them within the list of selected items. Remove %typeplural% by clicking on the trashcan.', array('%typeplural%' => __($type . 's')), 'apostrophe') ?>
  <?php if ($limitSizes): ?>
  <?php echo __('Only appropriately sized %typeplural% are shown.', array('%typeplural%' => __($type . 's')), 'apostrophe') ?>
  <?php endif ?>
  <?php echo __('When you\'re done, click "Save."', null, 'apostrophe') ?></p>

	<ul id="a-media-selection-list">
	<?php include_component("aMedia", "multipleList") ?>
	</ul>

	<?php echo jq_sortable_element("#a-media-selection-list", array("url" => "aMedia/multipleOrder")) ?>

	<br class="c"/>

	<ul class="a-controls a-media-slideshow-controls">
		<li><?php echo link_to(__("Save", null, 'apostrophe'), "aMedia/selected", array("class"=>"a-btn save")) ?></li>
 	  <li><?php echo link_to(__("Cancel", null, 'apostrophe'), "aMedia/selectCancel", array("class"=>"a-btn icon a-cancel event-default")) ?></li>
	</ul>
	
</div>
	<br class="c"/>