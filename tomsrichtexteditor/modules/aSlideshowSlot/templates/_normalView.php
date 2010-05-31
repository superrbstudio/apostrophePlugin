<?php use_helper('I18N') ?>
<?php if ($editable): ?>
  <?php // Normally we have an editor inline in the page, but in this ?>
  <?php // case we'd rather use the picker built into the media plugin. ?>
  <?php // So we link to the media picker and specify an 'after' URL that ?>
  <?php // points to our slot's edit action. Setting the ajax parameter ?>
  <?php // to false causes the edit action to redirect to the newly ?>
  <?php // updated page. ?>

  <?php slot("a-slot-controls-$pageid-$name-$permid") ?>
    <li class="a-controls-item choose-images">
    <?php echo link_to(__('Choose images', null, 'apostrophe'),
      'aMedia/select',
      array(
        'query_string' => 
          http_build_query(
            array_merge(
              $options['constraints'],
              array("multiple" => true,
              "aMediaIds" => implode(",", $itemIds),
              "type" => "image",
              "label" => __("Create a Slideshow", null, 'apostrophe'),
              "after" => url_for("aSlideshowSlot/edit") . "?" . 
                http_build_query(
                  array(
                    "slot" => $name, 
                    "slug" => $slug, 
                    "permid" => $permid,
                    "actual_slug" => aTools::getRealPage()->getSlug(),
                    'actual_url' => aTools::getRealUrl(),
                    "noajax" => 1))))),
        'class' => 'a-btn icon a-media')) ?>
    </li>

		<?php include_partial('a/variant', array('pageid' => $pageid, 'name' => $name, 'permid' => $permid, 'slot' => $slot)) ?>
		
  <?php end_slot() ?>

<?php endif ?>

<?php if (count($items)): ?>
	<?php include_component('aSlideshowSlot', 'slideshow', array('items' => $items, 'id' => $id, 'options' => $options)) ?>
<?php else: ?>

	<?php if (isset($options['singleton']) != true): ?>
		
		<?php (isset($options['width']))?  $style = 'width:' .  $options['width'] .'px;': $style = 'width:100%;'; ?>
		<?php (isset($options['height']))? $height = $options['height'] : $height = (($options['width'])? floor($options['width']*.56):'100'); ?>		
		<?php $style .= 'height:'.$height.'px;' ?>
	
		<div class="a-slideshow-placeholder" style="<?php echo $style ?>">
			<span style="line-height:<?php echo $height ?>px;"><?php echo __("Create a Slideshow", null, 'apostrophe') ?></span>
		</div>
	
	<?php endif ?>

<?php endif ?>