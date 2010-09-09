<?php
  // Compatible with sf_escaping_strategy: true
  $limitSizes = isset($limitSizes) ? $sf_data->getRaw('limitSizes') : null;
  $items = isset($items) ? $sf_data->getRaw('items') : array();
?>
<?php $type = aMediaTools::getAttribute('type') ?>
<?php if (!$type): ?>
<?php $type = "media item" ?>
<?php endif ?>

<?php // Known as selectMultiple for historic reasons, but we use it for both single and multiple select now that ?>
<?php // we have a need for cropping which requires a pause in both cases anyway. ?>
<?php use_helper('a','I18N') ?>
<?php use_javascript("/apostrophePlugin/js/plugins/jquery.jCrop.min.js") ?>
<?php use_javascript("/apostrophePlugin/js/aCrop.js") ?>
<?php use_stylesheet("/apostrophePlugin/css/Jcrop/jquery.Jcrop.css") ?>

<div class="a-media-select clearfix">
	<h3><?php echo $label ?></h3>

  <div class="a-ui">

  	<div id="a-media-selection-wrapper">

			<div class="a-media-selection-help">
			  <?php if (aMediaTools::isMultiple()): ?>
   				<h4><?php echo __('Select images for your slideshow', null, 'apostrophe') ?></h4>
   			<?php else: ?>
   			  <h4><?php echo __('Select an image', null, 'apostrophe') ?></h4>
   			<?php endif ?>		
			</div>

  			<ul id="a-media-selection-list" style="min-height:<?php echo ($thumbHeight = aMediaTools::getSelectedThumbnailHeight()) ? $thumbHeight + 10 : 0 ?>px;">
					<?php if($items): ?>
  					<?php include_partial("aMedia/multipleList", array("items" => $items)) ?>
			 		<?php endif ?>
  			</ul>

  		<?php echo jq_sortable_element("#a-media-selection-list", array("url" => "aMedia/multipleOrder" )) ?>
  		<div class="a-crop-workspace">
  		  <ul id="a-media-selection-preview">
  		  	<?php include_partial("aMedia/multiplePreview", array("items" => $items)) ?>
  		  </ul>
  		  <ul class="a-ui a-controls a-media-crop-controls" style="display:none;">
  				<li><?php echo jq_link_to_function('<span class="icon"></span>'.__('Crop', null, 'apostrophe'), 'aCrop.setCrop('.json_encode(url_for('aMedia/crop')).')', array('class'=>'a-btn save', 'id' => 'a-save-crop', )) ?></li>
  		 	  <li><?php echo jq_link_to_function('<span class="icon"></span>'.__('Cancel', null, 'apostrophe'), 'aCrop.resetCrop()', array('class'=>'a-btn icon a-cancel')) ?></li>
  		  </ul>
  		</div>
  	</div>
  </div>

	<ul class="a-ui a-controls">
		<li><?php echo link_to(aMediaTools::isMultiple() ? __('Save Slideshow', null, 'apostrophe') : __('Save Selection', null, 'apostrophe'), 'aMedia/selected', array('class'=>'a-btn save big','id' => 'a-save-media-selection', )) ?></li>
 	  <li><?php echo link_to(__('Cancel', null, 'apostrophe'), 'aMedia/selectCancel', array('class'=>'a-btn icon a-cancel big')) ?></li>
	</ul>
	
</div>

<?php a_js_call('apostrophe.aClickOnce(?)', array('selector' => '#a-save-media-selection')) ?>