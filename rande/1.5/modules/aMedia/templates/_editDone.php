<?php
  // Compatible with sf_escaping_strategy: true
  $mediaItem = isset($mediaItem) ? $sf_data->getRaw('mediaItem') : null;
?>

<script type="text/javascript" charset="utf-8">
	apostrophe.mediaItemRefresh({'url':<?php echo json_encode(url_for("aMedia/show?slug=" . $mediaItem->getSlug())) ?>});
</script>

<?php // Hello! I can't figure out what this partial is used for. I put the JS function into the apostorphe JS file ?>