<?php use_helper('a') ?>

<?php $areaOptions = isset($areaOptions) ? $sf_data->getRaw('areaOptions') : null ?>
<?php $slots = isset($slots) ? $sf_data->getRaw('slots') : null ?>
<?php $type_options = isset($type_options) ? $sf_data->getRaw('type_options') : null ?>
<?php $edit = isset($edit) ? $sf_data->getRaw('edit') : null ?>
<?php $slug = isset($slug) ? $sf_data->getRaw('slug') : null?>

<?php $options = array_merge($areaOptions, array(
	'allowed_types' => $slots, 
	'type_options' => $type_options, 
	'edit' => isset($edit) ? $edit : null, 
	'slug' => isset($slug) ? $slug : null
)) ?>

<?php a_area($name, $options) ?>