<?php use_helper('I18N') ?>
<?php if ($editable): ?>
  <?php // Normally we have an editor inline in the page, but in this ?>
  <?php // case we'd rather use the picker built into the media plugin. ?>
  <?php // So we link to the media picker and specify an 'after' URL that ?>
  <?php // points to our slot's edit action. Setting the ajax parameter ?>
  <?php // to false causes the edit action to redirect to the newly ?>
  <?php // updated page. ?>

  <?php slot("a-slot-controls-$pageid-$name-$permid") ?>
    <li class="a-controls-item choose-video">
	    <?php include_partial('aImageSlot/choose', array('action' => 'aVideoSlot/edit', 'buttonLabel' => __('Choose Video', null, 'apostrophe'), 'label' => __('Select a Video', null, 'apostrophe'), 'class' => 'a-btn icon a-media', 'type' => 'video', 'constraints' => $constraints, 'itemId' => $itemId, 'name' => $name, 'slug' => $slug, 'permid' => $permid)) ?>
	  </li>
			<?php include_partial('a/variant', array('pageid' => $pageid, 'name' => $name, 'permid' => $permid, 'slot' => $slot)) ?>	
  <?php end_slot() ?>
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
    <li class="a-media-video-title"><?php echo $item->title ?></li>
  <?php endif ?>
  <?php if ($description): ?>
    <li class="a-media-video-description"><?php echo $item->description ?></li>
  <?php endif ?>
  </ul>
<?php endif ?>