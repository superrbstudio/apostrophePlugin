<?php use_helper('I18N') ?>
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
	    <?php include_partial('aImageSlot/choose', array('action' => 'aVideoSlot/edit', 'buttonLabel' => __('Choose Video', null, 'apostrophe'), 'label' => __('Select a Video', null, 'apostrophe'), 'class' => 'a-btn icon a-media', 'type' => 'video', 'constraints' => $constraints, 'itemId' => $itemId, 'name' => $name, 'slug' => $slug, 'permid' => $permid)) ?>
			<?php include_partial('a/variant', array('pageid' => $pageid, 'name' => $name, 'permid' => $permid, 'slot' => $slot)) ?>	
  <?php end_slot() ?>

<?php endif ?>

<?php if (!$item): ?>
	<?php if (isset($options['singleton']) != true): ?>
		<?php (isset($options['width']))?  $style = 'width:' .  $options['width'] .'px;': $style = 'width:100%;'; ?>
		<?php (isset($options['height']))? $height = $options['height'] : $height = ((isset($options['width']))? floor($options['width']*.56):'100'); ?>		
		<?php $style .= 'height:'.$height.'px;' ?>
		<div class="a-media-placeholder" style="<?php echo $style ?>">
			<span style="line-height:<?php echo $height ?>px;"><?php echo __("Add a Video", null, 'apostrophe') ?></span>
		</div>
	<?php endif ?>
<?php endif ?>

<?php if ($item): ?>
  <ul class="a-media-video">

  <li class="a-media-video-embed">
  <?php if (isset($dimensions)): ?>
    <?php $embed = str_replace(
      array("_WIDTH_", "_HEIGHT_", "_c-OR-s_", "_FORMAT_"),
      array($dimensions['width'], 
        $dimensions['height'],
        $dimensions['resizeType'],
        $dimensions['format']),
      $embed) ?>
  <?php endif ?>
  <?php echo $embed ?>
	</li>
  <?php if ($title): ?>
    <li class="a-media-meta a-media-video-title"><?php echo $item->title ?></li>
  <?php endif ?>
  <?php if ($description): ?>
    <li class="a-media-meta a-media-video-description"><?php echo $item->description ?></li>
  <?php endif ?>
  </ul>
<?php endif ?>