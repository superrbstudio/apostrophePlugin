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

<?php $aspectRatio = aMediaTools::getAttribute('aspect-width') && aMediaTools::getAttribute('aspect-width') ?
    aMediaTools::getAttribute('aspect-width') / aMediaTools::getAttribute('aspect-height') : 0 ?>

  <ul id="a-media-selection-preview">
  <?php foreach ($items as $item): ?>
    <li id="a-media-selection-preview-<?php echo $item->getId() ?>" class="a-media-selection-preview-item">
      <img src="<?php echo url_for($item->getScaledUrl(aMediaTools::getOption('crop_constraints'))) ?>" />
    </li>
  <?php endforeach; ?>
  </ul>
  
  <ul class="a-controls a-media-crop-controls">
		<li><?php echo link_to(__("Set Crop", null, 'apostrophe'), "aMedia/crop", array("class"=>"a-btn save")) ?></li>
 	  <li><?php echo link_to(__("Cancel", null, 'apostrophe'), "aMedia/foo", array("class"=>"a-btn icon a-cancel event-default")) ?></li>
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
	
<script type="text/javascript" charset="utf-8">
$(document).ready(function(){
  aCrop.init({
    aspectRatio: <?php echo $aspectRatio ?>
  });
	$('.a-media-crop-controls').appendTo('.jcrop-holder div:first');
});
</script>
