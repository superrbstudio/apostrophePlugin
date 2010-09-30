<?php use_helper('a') ?>
<script src='/sfDoctrineActAsTaggablePlugin/js/pkTagahead.js'></script>
<script type="text/javascript" charset="utf-8">
	pkTagahead(<?php echo json_encode(url_for("taggableComplete/complete")) ?>);
 	aMultipleSelectAll({'choose-one':<?php echo json_encode(__('Select to Add', null, 'apostrophe')) ?>});
</script>
