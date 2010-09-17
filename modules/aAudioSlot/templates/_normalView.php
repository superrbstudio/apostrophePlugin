<?php
  // Compatible with sf_escaping_strategy: true
  $editable = isset($editable) ? $sf_data->getRaw('editable') : null;
  $item = isset($item) ? $sf_data->getRaw('item') : null;
  $itemId = isset($itemId) ? $sf_data->getRaw('itemId') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
  $pageid = isset($pageid) ? $sf_data->getRaw('pageid') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $slot = isset($slot) ? $sf_data->getRaw('slot') : null;
  $slug = isset($slug) ? $sf_data->getRaw('slug') : null;
?>
<?php use_helper('a') ?>

<?php use_javascript('/apostrophePlugin/js/jquery.jplayer.js') ?>

<?php if ($editable): ?>
  <?php // Normally we have an editor inline in the page, but in this ?>
  <?php // case we'd rather use the picker built into the media plugin. ?>
  <?php // So we link to the media picker and specify an 'after' URL that ?>
  <?php // points to our slot's edit action. Setting the ajax parameter ?>
  <?php // to false causes the edit action to redirect to the newly ?>
  <?php // updated page. ?>
  <?php // Wrap controls in a slot to be inserted in a slightly different ?>
  <?php // context by the _area.php template ?>

<?php slot("a-slot-controls-$pageid-$name-$permid") ?>
	<li class="a-controls-item choose-audio">
	  <?php include_partial('aImageSlot/choose', array('action' => 'aAudioSlot/edit', 'buttonLabel' => __('Choose Audio File', null, 'apostrophe'), 'label' => __('Select an Audio File', null, 'apostrophe'), 'class' => 'a-btn icon a-audio', 'type' => 'audio', 'itemId' => $itemId, 'name' => $name, 'slug' => $slug, 'permid' => $permid)) ?>
	</li>
		<?php include_partial('a/variant', array('pageid' => $pageid, 'name' => $name, 'permid' => $permid, 'slot' => $slot)) ?>	
<?php end_slot() ?>
<?php endif ?>

<?php $uniqueID = $pageid.'-'.$name.'-'.$permid ?>

<?php if (!$item): ?>
<?php if (isset($options['singleton']) != true): ?>
			
	<?php (isset($options['width']))?  $style = 'width:' .  $options['width'] .'px;': $style = 'width:100%;'; ?>
	<?php (isset($options['height']))? $height = $options['height'] : $height = 80; ?>		
	<?php $style .= 'height:'.$height.'px;' ?>

	<div class="a-media-placeholder" style="<?php echo $style ?>">
		<span style="line-height:<?php echo $height ?>px;"><?php echo __("Choose Audio File", null, 'apostrophe') ?></span>
	</div>
<?php endif ?>
<?php endif ?>

<?php if ($item): ?>
	<?php include_partial('aAudioSlot/'.$options['player'].'Player', array('item' => $item, 'uniqueID' => $uniqueID, 'options' => $options)) ?>		
<?php endif ?>
