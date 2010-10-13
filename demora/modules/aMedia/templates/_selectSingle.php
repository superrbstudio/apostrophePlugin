<?php
  // Compatible with sf_escaping_strategy: true
  $limitSizes = isset($limitSizes) ? $sf_data->getRaw('limitSizes') : null;
?>
<?php use_helper('a') ?>
<?php // Pretties up metatypes like _downloadable, returns "media item" if there is no specific type ?>
<?php $type = aMediaTools::getNiceTypeName() ?>

<div class="a-media-select clearfix">
  <?php // Thanks to Galileo for i18n corrections here ?>
  <p><?php echo __('Use the browsing and searching features to locate the %type% you want, then click on that %type% to select it.', array('%type%' => __($type, null, 'apostrophe')), 'apostrophe') ?>
  <?php if ($limitSizes): ?>
  <?php // separately I18N the plural ?>
  <?php echo __('Only appropriately sized %typeplural% are shown.', array('%typeplural%' => __($type . 's', null, 'apostrophe')), 'apostrophe') ?>
  <?php endif ?>
  </p>
  <ul class="a-controls">
    <li><?php echo link_to('<span class="icon"></span>'.a_('Cancel'), "aMedia/selectCancel", array("class"=>"a-btn icon a-cancel")) ?></li>
  </ul>
</div>
