<?php use_helper('I18N') ?>
<script src='/sfDoctrineActAsTaggablePlugin/js/pkTagahead.js'></script>
<script type="text/javascript" charset="utf-8">
	pkTagahead(<?php echo json_encode(url_for("taggableComplete/complete")) ?>);
 	aRadioSelect('#a_media_item_view_is_secure', { }); //This is for single editing
 	aMultipleSelectAll({'choose-one':<?php echo json_encode(__('Select to Add', null, 'apostrophe')) ?>});
</script>
