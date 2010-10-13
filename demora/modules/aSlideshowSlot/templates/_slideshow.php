<?php
  // Compatible with sf_escaping_strategy: true
  $id = isset($id) ? $sf_data->getRaw('id') : null;
  $items = isset($items) ? $sf_data->getRaw('items') : null;
  $n = isset($n) ? $sf_data->getRaw('n') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
?>
<?php use_helper('I18N') ?>
<?php if (count($items)): ?>
	<ul id="a-slideshow-<?php echo $id ?>" class="a-slideshow clearfix">
	<?php $first = true; $n=0; foreach ($items as $item): ?>
	  <?php $dimensions = aDimensions::constrain(
	    $item->width, 
	    $item->height,
	    $item->format, 
	    array("width" => $options['width'],
	      "height" => $options['flexHeight'] ? false : $options['height'],
	      "resizeType" => $options['resizeType'])) ?>
	  <?php $embed = str_replace(
	    array("_WIDTH_", "_HEIGHT_", "_c-OR-s_", "_FORMAT_"),
	    array($dimensions['width'], 
	      $dimensions['height'], 
	      $dimensions['resizeType'],
	      $dimensions['format']),
	    $item->getEmbedCode('_WIDTH_', '_HEIGHT_', '_c-OR-s_', '_FORMAT_', false)) ?>

	  <li class="a-slideshow-item" id="a-slideshow-item-<?php echo $id ?>-<?php echo $n ?>" <?php echo ($first)? 'style="display:list-item;"':''; ?>>
			<?php include_partial('aSlideshowSlot/'.$options['itemTemplate'], array('item' => $item, 'id' => $id, 'embed' => $embed, 'n' => $n,  'options' => $options)) ?>
		</li>
	<?php $first = false; $n++; endforeach ?>
	</ul>
<?php endif ?>

<?php if ($options['arrows'] && (count($items) > 1)): ?>
<ul id="a-slideshow-controls-<?php echo $id ?>" class="a-slideshow-controls">
	<li class="a-arrow-btn icon a-arrow-left"><?php echo __('Previous', null, 'apostrophe') ?></li>
	<?php if ($options['position']): ?>
		<li class="a-slideshow-position">
			<span class="head"></span>/<span class="total"><?php echo count($items); ?></span>
		</li>
	<?php endif ?>
	<li class="a-arrow-btn icon a-arrow-right"><?php echo __('Next', null, 'apostrophe') ?></li>
</ul>
<?php endif ?>

<?php a_js_call('apostrophe.slideshow(?)', array('id' => $id, 'position' => $options['position'], 'interval' => $options['interval'], 'title' => __('Click For Next Image', null, 'apostrophe'))) ?>
