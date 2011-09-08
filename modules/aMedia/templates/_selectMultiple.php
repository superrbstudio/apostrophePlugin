<?php
  // Compatible with sf_escaping_strategy: true
  $limitSizes = isset($limitSizes) ? $sf_data->getRaw('limitSizes') : null;
  $items = isset($items) ? $sf_data->getRaw('items') : array();
?>

<?php // Known as selectMultiple for historic reasons, but we use it for both single and multiple select now that ?>
<?php // we have a need for cropping which requires a pause in both cases anyway. ?>
<?php use_helper('a') ?>
<?php // No need to bring in pre-minified stuff in 1.5 ?>
<?php use_javascript("/apostrophePlugin/js/plugins/jquery.Jcrop.js") ?>
<?php use_javascript("/apostrophePlugin/js/aCrop.js") ?>
<?php use_stylesheet("/apostrophePlugin/css/Jcrop/jquery.Jcrop.css") ?>

<div class="a-ui a-media-select clearfix">
	<h3><?php echo $label ?></h3>

  <div class="a-ui">

  	<div id="a-media-selection-wrapper" class="a-media-selection-wrapper">

			<div class="a-media-selection-help">
			  <?php if (aMediaTools::isMultiple()): ?>
   				<h4><?php echo __('Select images for your slideshow', null, 'apostrophe') ?></h4>
   			<?php else: ?>
   			  <h4><?php echo __('Select an image', null, 'apostrophe') ?></h4>
   			<?php endif ?>		
			</div>

  			<ul id="a-media-selection-list" style="min-height:<?php echo ($thumbHeight = aMediaTools::getSelectedThumbnailHeight()) ? $thumbHeight + 10 : 0 ?>px;">
  			  <?php // Always include this, it brings in some of the relevant JS too ?>
					<?php include_partial("aMedia/multipleList", array("items" => $items)) ?>
  			</ul>

      <?php a_js_call('apostrophe.mediaEnableSelectionSort(?)', a_url('aMedia', 'multipleOrder')) ?>
   		<div id="a-crop-workspace" class="a-crop-workspace">
  		  <ul id="a-media-selection-preview">
  		  	<?php include_partial("aMedia/multiplePreview", array("items" => $items)) ?>
  		  </ul>
  		  <ul class="a-ui a-controls a-media-crop-controls" style="display:none;">
  				<li><?php echo content_tag('a', '<span class="icon"></span>'.__('Crop', null, 'apostrophe'), array('href' => '#', 'class'=>'a-btn save', 'id' => 'a-save-crop', )) ?></li>
  				<li><?php echo content_tag('a', '<span class="icon"></span>'.__('Cancel', null, 'apostrophe'), array('href' => '#', 'class'=>'a-btn icon a-cancel alt', 'id' => 'a-cancel-crop', )) ?></li>
  		  </ul>
  		</div>
  	</div>
  </div>

	<ul class="a-ui a-controls">
		<li><?php echo a_button(a_('Save Selection'), url_for("aMedia/selected"), array('save','big','a-select-save','a-show-busy'), 'a-save-media-selection') ?></li>
		<li><?php echo a_button(a_('Cancel'), a_url('aMedia', 'selectCancel'), array('icon','a-cancel','big','alt','a-select-cancel')) ?></li>
	</ul>
	
</div>
