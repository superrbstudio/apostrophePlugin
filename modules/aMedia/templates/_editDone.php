<?php
  // Compatible with sf_escaping_strategy: true
  $mediaItem = isset($mediaItem) ? $sf_data->getRaw('mediaItem') : null;
?>

<script type="text/javascript">
	apostrophe.mediaItemRefresh(<?php echo json_encode(array('url' => url_for('a_media_image_show', array('slug' => $mediaItem->getSlug())))) ?>);
</script>

<?php // Hello! I can't figure out what this partial is used for. I put the JS function into the apostorphe JS file ?>