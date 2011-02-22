<?php
  // Compatible with sf_escaping_strategy: true
  $dimensions = isset($dimensions) ? $sf_data->getRaw('dimensions') : null;
  $editable = isset($editable) ? $sf_data->getRaw('editable') : null;
  $item = isset($item) ? $sf_data->getRaw('item') : null;
  $itemId = isset($itemId) ? $sf_data->getRaw('itemId') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
  $page = isset($page) ? $sf_data->getRaw('page') : null;
  $pageid = isset($pageid) ? $sf_data->getRaw('pageid') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $slot = isset($slot) ? $sf_data->getRaw('slot') : null;
  $slug = isset($slug) ? $sf_data->getRaw('slug') : null;
  $embed = isset($embed) ? $sf_data->getRaw('embed') : null;
?>
<?php use_helper('a') ?>

<?php if ($editable): ?>
  <?php // Normally we have an editor inline in the page, but in this ?>
  <?php // case we'd rather use the picker built into the media plugin. ?>
  <?php // So we link to the media picker and specify an 'after' URL that ?>
  <?php // points to our slot's edit action. Setting the ajax parameter ?>
  <?php // to false causes the edit action to redirect to the newly ?>
  <?php // updated page. ?>

  <?php // Wrap controls in a slot to be inserted in a slightly different ?>
  <?php // context by the _area.php template ?>

  <?php // Very short labels so sidebar slots don't have wrap in their controls. ?>
  <?php // That spoils assumptions that are being made elsewhere that they will ?>
  <?php // amount to only one row. TODO: find a less breakage-prone solution to that problem. ?>

  <?php slot("a-slot-controls-$pageid-$name-$permid") ?>
			<?php if ($options['image']): ?>
  			<?php include_partial('aImageSlot/choose', array('action' => 'aButtonSlot/image', 'buttonLabel' => a_get_option($options, 'chooseLabel', a_('Choose Image')), 'label' => a_get_option($options, 'browseLabel', a_('Select an Image')), 'class' => 'a-btn icon a-media', 'type' => 'image', 'constraints' => $options['constraints'], 'itemId' => $itemId, 'name' => $name, 'slug' => $slug, 'permid' => $permid)) ?>				
			<?php endif ?>
			<?php include_partial('a/simpleEditWithVariants', array('pageid' => $pageid, 'name' => $name, 'permid' => $permid, 'slot' => $slot, 'page' => $page, 'controlsSlot' => false, 'label' => a_get_option($options, 'editLabel', a_('Edit')))) ?>
  <?php end_slot() ?>

<?php endif ?>

<?php include_partial('aButtonSlot/'.$options['itemTemplate'].'Template', array(
	'dimensions' => $dimensions, 
	'constraints' => $options['constraints'], 
	'editable' => $editable, 
	'item' => $item, 
	'itemId' => $itemId, 
	'name' => $name, 
	'options' => $options, 
	'page' => $page, 
	'pageid' => $pageid,
	'permid' => $permid,
	'slot' => $slot, 
	'slug' => $slug, 
	'embed' => $embed 
)) ?>

<?php a_js_call('apostrophe.buttonSlot(?)', array('button' => '#a-button-'.$pageid.'-'.$name.'-'.$permid, 'rollover' => (($options['rollover'] && $options['link'])?$options['rollover']:false))) ?>