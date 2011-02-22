<?php
  // Compatible with sf_escaping_strategy: true
  $editable = isset($editable) ? $sf_data->getRaw('editable') : null;
  $id = isset($id) ? $sf_data->getRaw('id') : null;
  $itemIds = isset($itemIds) ? $sf_data->getRaw('itemIds') : null;
  $items = isset($items) ? $sf_data->getRaw('items') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
  $pageid = isset($pageid) ? $sf_data->getRaw('pageid') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $slot = isset($slot) ? $sf_data->getRaw('slot') : null;
  $slug = isset($slug) ? $sf_data->getRaw('slug') : null;
?>

<?php use_helper('a') ?>

<?php if ($editable): ?>
  <?php include_partial('a/simpleEditWithVariants', array('pageid' => $page->id, 'name' => $name, 'permid' => $permid, 'slot' => $slot, 'page' => $page, 'label' => a_get_option($options, 'editLabel', a_('Edit')))) ?>
<?php endif ?>

<?php if (count($items)): ?>
	<?php include_component('aSlideshowSlot', 'slideshow', array('items' => $items, 'id' => $id, 'options' => $options)) ?>
<?php else: ?>				
	<?php include_partial('aImageSlot/placeholder', array('placeholderText' => a_("No Matching Photos Found"), 'options' => $options)) ?>
<?php endif ?>