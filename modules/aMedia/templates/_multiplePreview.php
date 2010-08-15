<?php // Make sure we're compatible with sf_escaping_strategy: true ?>
<?php $items = isset($items) ? $sf_data->getRaw('items') : array() ?>
<?php foreach ($items as $item): ?>
  <li id="a-media-selection-preview-<?php echo $item->getId() ?>" class="a-media-selection-preview-item">
		<h4 class="a-media-selection-title">You are cropping "<?php echo ($item->getTitle()) ?>"</h4>
    <img src="<?php echo url_for($item->getScaledUrl(aMediaTools::getOption('crop_constraints'))) ?>" />
  </li>
<?php endforeach; ?>
<script type="text/javascript" charset="utf-8">
  $(function() { 
	  // the preview images are by default set to display:none
    $('#a-media-selection-preview li:first').addClass('current');
    // set up cropping again; do hard reset to reinstantiate Jcrop
    aCrop.resetCrop(true);
  });
</script>
