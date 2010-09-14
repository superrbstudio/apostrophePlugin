<?php // Make sure we're compatible with sf_escaping_strategy: true ?>
<?php $items = isset($items) ? $sf_data->getRaw('items') : array() ?>
<?php foreach ($items as $item): ?>
  <li id="a-media-selection-preview-<?php echo $item->getId() ?>" class="a-media-selection-preview-item">
		<h4 class="a-media-selection-title">You are cropping "<?php echo ($item->getTitle()) ?>"</h4>
    <img src="<?php echo url_for($item->getScaledUrl(aMediaTools::getOption('crop_constraints'))) ?>" />
  </li>
<?php endforeach; ?>
<?php a_js_call('apostrophe.mediaEnableMultiplePreview()') ?>
