<?php
  // Compatible with sf_escaping_strategy: true
  $id = isset($id) ? $sf_data->getRaw('id') : null;
  $items = isset($items) ? $sf_data->getRaw('items') : null;
  $n = isset($n) ? $sf_data->getRaw('n') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;

  $title = count($items) > 1 ? __('Click For Next Image', null, 'apostrophe') : false;
	$id = ($options['idSuffix']) ? $id.'-'.$options['idSuffix']:$id;
?>
<?php use_helper('a') ?>

<?php if (count($items)): ?>
  <?php // Crossfade doesn't work well without a height unless you do special gymnastics. The simple ?>
  <?php // workaround is to specify maxHeight as a fallback ?>
  
	<ul id="a-slideshow-<?php echo $id ?>" class="a-slideshow clearfix <?php echo (count($items === 1) ? 'single-image' : 'multi-image') ?> transition-<?php echo $options['transition'] ?>"<?php echo ($options['transition'] == 'crossfade')? ' style="height:'.($options['height'] ? $options['height'] : ($options['maxHeight'] ? $options['maxHeight'] : 0)) . 'px; width:'.$options['width'].'px;"':'' ?>>
	<?php $first = true; $n=0; foreach ($items as $item): ?>
	  <?php $dimensions = aDimensions::constrain(
	    $item->width, 
	    $item->height,
	    $item->format, 
	    array("width" => $options['width'],
	      "height" => $options['flexHeight'] ? false : $options['height'],
	      "resizeType" => $options['resizeType'])) ?>
		<?php // Implement maximum height ?>
		<?php if ($options['maxHeight']): ?>
			<?php if ($dimensions['height'] > $options['maxHeight']): ?>
			  <?php $dimensions = aDimensions::constrain(
			    $item->width,
			    $item->height,
			    $item->format,
			    array("width" => false,
			      "height" => $options['maxHeight'],
			      "resizeType" => $options['resizeType'])) ?>
			<?php endif ?>
		<?php endif ?>
		
	  <?php $embed = $item->getEmbedCode($dimensions['width'], $dimensions['height'], $dimensions['resizeType'], $dimensions['format']) ?>
	  <li class="a-slideshow-item" id="a-slideshow-item-<?php echo $id ?>-<?php echo $n ?>">
			<?php include_partial('aSlideshowSlot/'.$options['itemTemplate'], array('items' => $items, 'item' => $item, 'id' => $id, 'embed' => $embed, 'n' => $n,  'options' => $options)) ?>
		</li>
  <?php if (a_get_option($options, 'firstOnly')): ?>
    <?php break ?>
  <?php endif ?>
	<?php $first = false; $n++; endforeach ?>
	</ul>
<?php endif ?>

<?php if ($options['arrows'] && (count($items) > 1) && (!a_get_option($options, 'firstOnly'))): ?>
<ul id="a-slideshow-controls-<?php echo $id ?>" class="a-slideshow-controls">
	<li class="a-arrow-btn icon a-arrow-left<?php echo ($options['arrows'] === 'alt') ? ' alt' : '' ?>"><span class="icon"></span><?php echo __('Previous', null, 'apostrophe') ?></li>
	<?php if ($options['position']): ?>
		<li class="a-slideshow-position">
			<span class="a-slideshow-position-head">1</span> of <span class="a-slideshow-position-total"><?php echo count($items); ?></span>
		</li>
	<?php endif ?>
	<li class="a-arrow-btn icon a-arrow-right<?php echo ($options['arrows'] === 'alt') ? ' alt' : '' ?>"><span class="icon"></span><?php echo __('Next', null, 'apostrophe') ?></li>
</ul>
<?php endif ?>

<?php if (!a_get_option($options, 'firstOnly')): ?>
  <?php a_js_call('apostrophe.slideshowSlot(?)', array('debug' => false, 'id' => $id, 'position' => $options['position'], 'interval' => $options['interval'],  'transition' => $options['transition'], 'duration' => $options['duration'], 'title' => $title)) ?>
<?php endif ?>