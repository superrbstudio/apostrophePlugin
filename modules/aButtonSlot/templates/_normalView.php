<?php
  // Compatible with sf_escaping_strategy: true
  $dimensions = isset($dimensions) ? $sf_data->getRaw('dimensions') : null;
  $constraints = isset($constraints) ? $sf_data->getRaw('constraints') : null;
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
  			<?php include_partial('aImageSlot/choose', array('action' => 'aButtonSlot/image', 'buttonLabel' => __('Choose image', null, 'apostrophe'), 'label' => __('Select an Image', null, 'apostrophe'), 'class' => 'a-btn icon a-media', 'type' => 'image', 'constraints' => $constraints, 'itemId' => $itemId, 'name' => $name, 'slug' => $slug, 'permid' => $permid)) ?>				
			<?php endif ?>
			<?php include_partial('a/simpleEditWithVariants', array('pageid' => $page->id, 'name' => $name, 'permid' => $permid, 'slot' => $slot, 'page' => $page, 'controlsSlot' => false)) ?>
  <?php end_slot() ?>

<?php endif ?>

<?php if ($item): ?>
  <ul id="a-button-<?php echo $pageid.'-'.$name.'-'.$permid; ?>" class="a-button">
    <li class="a-button-image">
    	<?php $embed = str_replace(array("_WIDTH_", "_HEIGHT_", "_c-OR-s_", "_FORMAT_"), array($dimensions['width'], $dimensions['height'], $dimensions['resizeType'],  $dimensions['format']), $embed) ?>
	    <?php if ($options['url']): ?>
	      <?php echo '<a class="a-button-link" href="'.$options['url'].'">'.$embed.'</a>' ?>
			<?php else: ?>
	    	<?php echo $embed ?>			
	    <?php endif ?>
    </li>
    <?php if ($options['title']): ?>
      <li class="a-button-title">				
      	<?php if ($options['url']): ?>
					<a class="a-button-link" href="<?php echo $options['url'] ?>"><?php echo $options['title'] ?></a>      		
				<?php else: ?>
					<?php echo $options['title'] ?>
      	<?php endif ?>
      </li>
    <?php endif ?>
    <?php if ($options['description']): ?>
      <li class="a-button-description"><?php echo $options['description'] ?></li>
    <?php endif ?>
  </ul>
<?php else: ?>
	
	<?php if ($options['image']): ?>
		<?php include_partial('aImageSlot/placeholder', array('placeholderText' => a_("Create a Button"), 'options' => $options)) ?>
	<?php endif ?>
	
  <?php if ($options['defaultImage']): ?>
  	<ul id="a-button-<?php echo $pageid.'-'.$name.'-'.$permid; ?>" class="a-button default">
      <li class="a-button-image">
        <?php // Corner case: they've set the link but are still using the default image ?>
        <?php if ($options['link']): ?>
          <?php echo link_to(image_tag($options['defaultImage']), $options['url']) ?>
        <?php else: ?>
          <?php echo image_tag($options['defaultImage']) ?>
        <?php endif ?>
      </li>
    </ul>
	<?php else: ?>
		<?php if ($options['link'] || $options['url']): ?>
	  	<ul id="a-button-<?php echo $pageid.'-'.$name.'-'.$permid; ?>" class="a-button link-only">
	      <li class="a-button-title">
	        <?php echo link_to((($options['title'])?$options['title']:$options['url']), $options['url'], array('class' => 'a-button-link')) ?>
	      </li>
		    <?php if ($options['description']): ?>
	      <li class="a-button-description"><?php echo $options['description'] ?></li>
		    <?php endif ?>
	    </ul>	
		<?php endif ?>
  <?php endif ?>

<?php endif ?>	

<?php a_js_call('apostrophe.buttonSlot(?)', array('button' => '#a-button-'.$pageid.'-'.$name.'-'.$permid, 'rollover' => (($options['rollover'] && $options['link'])?$options['rollover']:false))) ?>