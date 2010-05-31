<script src='/sfDoctrineActAsTaggablePlugin/js/aTagahead.js'></script>
<script type='text/javascript'>
	aTagahead(<?php echo json_encode(url_for("taggableComplete/complete")) ?>);
 	aRadioSelect('#a_media_item_view_is_secure', { }); //This is for single editing
</script>
