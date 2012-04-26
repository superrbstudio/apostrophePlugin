<?php
  // Compatible with sf_escaping_strategy: true
  $item = isset($item) ? $sf_data->getRaw('item') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
  $embed = isset($embed) ? $sf_data->getRaw('embed') : null;
  // Defaults true for backwards compatibility
  $stretch16x9 = isset($stretch16x9) ? $sf_data->getRaw('stretch16x9') : true;
?>

<?php // Please do not remove a-media-container as the idea is to make it easy to get the slug for the wrapper of any media item ?>
<?php // rendered by a slot. Thanks. -Tom ?>

<ul class="a-media-container a-media-video" data-slug="<?php echo $item->slug ?>">
	<li class="a-media-video-embed <?php echo $stretch16x9 ? 'a-stretch-16x9' : '' ?>">
		<?php echo $embed ?>
	</li>
	<?php if ($options['title']): ?>
  	<li class="a-media-meta a-media-video-title"><?php echo $item->title ?></li>
	<?php endif ?>
	<?php if ($options['description']): ?>
  	<li class="a-media-meta a-media-video-description"><?php echo $item->description ?></li>
	<?php endif ?>
</ul>