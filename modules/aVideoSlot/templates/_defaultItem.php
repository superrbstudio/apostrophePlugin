<?php
  // Compatible with sf_escaping_strategy: true
  $item = isset($item) ? $sf_data->getRaw('item') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
  $embed = isset($embed) ? $sf_data->getRaw('embed') : null;
?>

<ul class="a-media-video">
	<li class="a-media-video-embed">
		<?php echo $embed ?>
	</li>
	<?php if ($options['title']): ?>
  	<li class="a-media-meta a-media-video-title"><?php echo $item->title ?></li>
	<?php endif ?>
	<?php if ($options['description']): ?>
  	<li class="a-media-meta a-media-video-description"><?php echo $item->description ?></li>
	<?php endif ?>
</ul>