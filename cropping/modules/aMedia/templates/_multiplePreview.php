<?php foreach ($items as $item): ?>
  <li id="a-media-selection-preview-<?php echo $item->getId() ?>" class="a-media-selection-preview-item">
    <img src="<?php echo url_for($item->getScaledUrl(aMediaTools::getOption('crop_constraints'))) ?>" />
  </li>
<?php endforeach; ?>