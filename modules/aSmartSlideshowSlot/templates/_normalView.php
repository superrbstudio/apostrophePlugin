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
  <?php include_partial('a/simpleEditWithVariants', array('pageid' => $page->id, 'name' => $name, 'permid' => $permid, 'slot' => $slot, 'page' => $page)) ?>
<?php endif ?>

<?php if (count($items)): ?>
	<?php include_component('aSlideshowSlot', 'slideshow', array('items' => $items, 'id' => $id, 'options' => $options)) ?>
<?php else: ?>

	<?php if (isset($options['singleton']) != true): ?>
				
		<?php (isset($options['width']))?  $style = 'width:' .  $options['width'] .'px;': $style = 'width:100%;'; ?>
		<?php (isset($options['height']))? $height = $options['height'] : $height = ((isset($options['width']))? floor($options['width']*.56):'100'); ?>		
		<?php $style .= 'height:'.$height.'px;' ?>
	
		<div class="a-media-placeholder" style="<?php echo $style ?>">
			<span style="line-height:<?php echo $height ?>px;"><?php echo __("No Matching Photos", null, 'apostrophe') ?></span>
		</div>
	
	<?php endif ?>

<?php endif ?>