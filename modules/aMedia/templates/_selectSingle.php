<?php
  // Compatible with sf_escaping_strategy: true
  $limitSizes = isset($limitSizes) ? $sf_data->getRaw('limitSizes') : null;
?>
<?php use_helper('a') ?>
<?php // Pretties up metatypes like _downloadable, returns "media item" if there is no specific type ?>
<?php $type = aMediaTools::getNiceTypeName() ?>

<div class="a-ui a-media-select clearfix">

	<h3><?php echo __('You are selecting a %type%.', array('%type%' => __($type, null, 'apostrophe')), 'apostrophe') ?></h3>

 	<div id="a-media-selection-wrapper" class="a-ui a-media-selection-wrapper">
	<div class="a-media-selection-help">
	  <?php // Thanks to Galileo for i18n corrections here ?>
	  <?php // I adjusted the wording here to avoid saying "click on" as sometimes there is an explicit select button etc. ?>
	  <h4>
			<?php echo __('Use the browsing and searching features to locate the %type% you want, then click to select it.', array('%type%' => __($type, null, 'apostrophe')), 'apostrophe') ?>
	  	<?php if ($limitSizes): ?>
	  		<?php // separately I18N the plural ?>
	  		<?php echo __('Only appropriately sized %typeplural% are shown.', array('%typeplural%' => __($type . 's', null, 'apostrophe')), 'apostrophe') ?>
	  	<?php endif ?>
	  </h4>
	</div>
	</div>
	
  <ul class="a-ui a-controls">
		<li><?php echo a_button(a_('Cancel'), a_url('aMedia', 'selectCancel'), array('icon','a-cancel','big','a-select-cancel','alt')) ?></li>
  </ul>
</div>
