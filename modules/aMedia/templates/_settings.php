<?php use_helper('I18N') ?>
<?php echo $form ?>
<script>
aMultipleSelectAll({'choose-one':<?php echo json_encode(__('Select to Add', null, 'apostrophe')) ?>});
</script>