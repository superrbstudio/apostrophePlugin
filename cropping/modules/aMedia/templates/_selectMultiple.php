<?php // Known as selectMultiple for historic reasons, but we use it for both single and multiple select now that ?>
<?php // we have a need for cropping which requires a pause in both cases anyway. ?>
<?php use_helper('I18N') ?>
<?php use_javascript("/apostrophePlugin/js/plugins/jquery.jCrop.min.js") ?>
<?php use_javascript("/apostrophePlugin/js/aCrop.js") ?>
<?php use_stylesheet("/apostrophePlugin/css/Jcrop/jquery.Jcrop.css") ?>
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

  <ul id="a-media-selection-preview">
  <?php include_partial("aMedia/multiplePreview", array("items" => $items)) ?>
  </ul>
  
  <ul class="a-controls a-media-crop-controls">
		<li><?php echo jq_link_to_function(__("Set Crop", null, 'apostrophe'), "aCrop.setCrop('".url_for('aMedia/crop')."')", array("class"=>"a-btn save")) ?></li>
 	  <li><?php echo jq_link_to_function(__("Reset", null, 'apostrophe'), "aCrop.resetCrop()", array("class"=>"a-btn icon a-cancel event-default")) ?></li>
  </ul>

	<ul id="a-media-selection-list">
	<?php include_partial("aMedia/multipleList", array("items" => $items)) ?>
	</ul>

	<?php echo jq_sortable_element("#a-media-selection-list", array("url" => "aMedia/multipleOrder")) ?>

	<br class="c"/>

	<ul class="a-controls a-media-slideshow-controls">
		<li><?php echo link_to(__("Save", null, 'apostrophe'), "aMedia/selected", array("class"=>"a-btn save")) ?></li>
 	  <li><?php echo link_to(__("Cancel", null, 'apostrophe'), "aMedia/selectCancel", array("class"=>"a-btn icon a-cancel event-default")) ?></li>
	</ul>
	
</div>
	<br class="c"/>

