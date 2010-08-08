<?php
  // Compatible with sf_escaping_strategy: true
  $mediaItem = isset($mediaItem) ? $sf_data->getRaw('mediaItem') : null;
?>
<script type="text/javascript" charset="utf-8">
	window.parent.aMediaItemRefresh(<?php echo $mediaItem->getId() ?>);
</script>
