<?php use_helper('jQuery') ?>

<?php include_component('a', 'area', array('name' => $name, 'refresh' => true, 'addSlot' => $type, 'preview' => false, 'options' => $options))?>

<script type="text/javascript" charset="utf-8">
	$('#a-add-slot-form-<?php echo $name ?>').hide();
</script>
