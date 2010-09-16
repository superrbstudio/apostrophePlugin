<?php
  // Compatible with sf_escaping_strategy: true
  $mediaItem = isset($mediaItem) ? $sf_data->getRaw('mediaItem') : null;
?>
<?php use_helper('a') ?>
<?php slot('body_class') ?>a-media<?php end_slot() ?>
<?php $id = $mediaItem->getId() ?>

<?php // If we can't render this format on this particular server platform (ie PDF on Windows), ?>
<?php // leave the embed code unset and don't try to echo it later ?>
<?php if ($mediaItem->getWidth()): ?>
  <?php $options = aDimensions::constrain($mediaItem->getWidth(), $mediaItem->getHeight(),
    $mediaItem->getFormat(), aMediaTools::getOption('show_constraints')) ?>
  <?php $embedCode = $mediaItem->getEmbedCode(
    $options['width'], $options['height'], $options['resizeType'], $options['format']) ?>
<?php else: ?>
  <?php $format = $mediaItem->getFormat() ?>
  <?php $embedCode = '<span class="a-media-type '.$format.'"><b>'.$format.'</b></span>' ?>
<?php endif ?>


<div class="a-media-library">	
	<div class="a-media-items">
		<?php echo link_to('<span class="icon"></span>'.__('Media Library', null, 'apostrophe'), '@a_media_index', array('class' => 'a-btn big icon a-arrow-left lite', 'id' => 'media-library-back-button', ))?>

		<ul class="a-media-item-content" id="a-media-item-content-<?php echo $mediaItem->getId()?>">
			<li class="a-media-item-source">
				<?php include_partial('aMedia/editLinks', array('mediaItem' => $mediaItem)) ?>
			</li>
			<li class="a-media-item-image">
				<?php if (isset($embedCode)): ?>
		  	  <?php echo $embedCode ?>
		  	<?php endif ?>
			</li>
		  <?php // Stored as HTML ?>
			<li class="a-media-item-title <?php if (!$mediaItem->getWidth()): ?>no-thumbnail<?php endif ?>"><h3><?php echo htmlspecialchars($mediaItem->getTitle()) ?></h3></li>
		  <li class="a-media-item-description"><?php echo $mediaItem->getDescription() ?></li>
		  <?php if ($mediaItem->getWidth()): ?>
		  	<li class="a-media-item-dimensions a-media-item-meta"><span><?php echo __('Original Dimensions:', null, 'apostrophe') ?></span> <?php echo __('%width%x%height%', array('%width%' => $mediaItem->getWidth(), '%height%' =>  $mediaItem->getHeight()), 'apostrophe') ?></li>
		  <?php endif ?>
		  <li class="a-media-item-created-at a-media-item-meta"><span><?php echo __('Uploaded:', null, 'apostrophe') ?></span> <?php echo aDate::pretty($mediaItem->getCreatedAt()) ?></li>
		  <li class="a-media-item-credit a-media-item-meta"><span><?php echo __('Credit:', null, 'apostrophe') ?></span> <?php echo htmlspecialchars($mediaItem->getCredit()) ?></li>
		  <li class="a-media-item-categories a-media-item-meta"><span><?php echo __('Categories:', null, 'apostrophe') ?></span> <?php include_partial('aMedia/showCategories', array('categories' => $mediaItem->getMediaCategories())) ?></li>
		  <li class="a-media-item-tags a-media-item-meta"><span><?php echo __('Tags:', null, 'apostrophe') ?></span> <?php include_partial('aMedia/showTags', array('tags' => $mediaItem->getTags())) ?></li>
			<li class="a-media-item-download">
				<?php if ($mediaItem->getType() !== 'video'): ?>
		        <?php // download link ?>
		        <?php echo link_to(
		          __("Download Original%buttonspan%", array('%buttonspan%' => "<span></span>"), 'apostrophe'),
		          "aMediaBackend/original?" .
		            http_build_query(
		              array(
		                "slug" => $mediaItem->getSlug(),
		                "format" => $mediaItem->getFormat())), 
		                array("class"=>"a-btn icon a-download")) ?>
		      <?php endif ?>
			</li>
		</ul>

	</div>
</div>

<?php // Media Sidebar is wrapped slot('a-subnav') ?>
<?php include_component('aMedia', 'browser') ?>

<?php a_js_call('apostrophe.selectOnFocus(?)', '.a-select-on-focus') ?>