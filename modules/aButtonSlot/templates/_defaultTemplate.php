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

<?php if ($item): ?>
  <ul id="a-button-<?php echo $pageid.'-'.$name.'-'.$permid; ?>" class="a-button">
    <li class="a-button-image">
	    <?php if ($options['url']): ?>
	      <?php echo '<a class="a-button-link" href="'.$options['url'].'">'.$embed.'</a>' ?>
			<?php else: ?>
	    	<?php echo $embed ?>			
	    <?php endif ?>
    </li>
    <?php if ($options['title']): ?>
      <li class="a-button-title">		
      	<?php if ($options['url']): ?>
					<a class="a-button-link" href="<?php echo $options['url'] ?>"><?php echo aHtml::entities($options['title']) ?></a>      		
				<?php else: ?>
					<?php echo aHtml::entities($options['title']) ?>
      	<?php endif ?>
      </li>
    <?php endif ?>
    <?php if ($options['description']): ?>
      <li class="a-button-description"><?php echo $options['description'] ?></li>
    <?php endif ?>
  </ul>
<?php else: ?>
	
	<?php if ($options['image'] && (!strlen($options['url']))): ?>
		<?php include_partial('aImageSlot/placeholder', array('placeholderText' => a_("Create a Button"), 'options' => $options)) ?>
	<?php endif ?>
	
  <?php if ($options['defaultImage']): ?>
  	<ul id="a-button-<?php echo $pageid.'-'.$name.'-'.$permid; ?>" class="a-button default">
      <li class="a-button-image">
        <?php // Corner case: they've set the link but are still using the default image ?>
        <?php $img = image_tag($options['defaultImage'], array('alt' => (($options['title']) ? aHtml::entities($options['title']) : ''))) ?>
        <?php if ($options['link']): ?>
          <?php echo link_to($img, $options['url']) ?>
        <?php else: ?>
					<?php echo $img ?>
        <?php endif ?>
      </li>
    </ul>
	<?php else: ?>
		<?php if ($options['link'] || $options['url']): ?>
	  	<ul id="a-button-<?php echo $pageid.'-'.$name.'-'.$permid; ?>" class="a-button link-only">
	      <li class="a-button-title">
	        <?php echo link_to((($options['title'])?aHtml::entities($options['title']) : aHtml::entities($options['url'])), $options['url'], array('class' => 'a-button-link')) ?>
	      </li>
		    <?php if ($options['description']): ?>
	      <li class="a-button-description"><?php echo $options['description'] ?></li>
		    <?php endif ?>
	    </ul>	
		<?php endif ?>
  <?php endif ?>

<?php endif ?>