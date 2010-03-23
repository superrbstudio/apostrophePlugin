<?php use_helper('I18N') ?>
<?php $type = aMediaTools::getAttribute('type') ?>
<?php if (!$type): ?>
<?php $type = "media item" ?>
<?php endif ?>

<div class="a-media-select">
  <?php // Thanks to Galileo for i18n corrections here ?>
  <p><?php echo __('Use the browsing and searching features to locate the %type% you want, then click on that %type% to select it.', array('%type%' => __($type, null, 'apostrophe')), 'apostrophe') ?>
  <?php if ($limitSizes): ?>
  <?php // separately I18N the plural ?>
  <?php echo __('Only appropriately sized %typeplural% are shown.', array('%typeplural%' => __($type . 's', null, 'apostrophe')), 'apostrophe') ?>
  <?php endif ?>
  </p>
	<?php 
		//removing this cancel link for now, it exists above in the header
		// echo link_to("cancel", "aMedia/selectCancel", array("class"=>"a-cancel")) 
	?>
</div>
