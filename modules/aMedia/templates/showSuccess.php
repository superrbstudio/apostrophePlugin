<?php slot('body_class') ?>a-media<?php end_slot() ?>

<?php use_helper('jQuery') ?>

<div id="a-media-plugin">
	
<?php include_component('aMedia', 'browser') ?>

<div class="a-media-library">
	
<?php $id = $mediaItem->getId() ?>
<?php $options = aDimensions::constrain($mediaItem->getWidth(), $mediaItem->getHeight(),
  $mediaItem->getFormat(), aMediaTools::getOption('show_constraints')) ?>
<?php $embedCode = $mediaItem->getEmbedCode(
  $options['width'], $options['height'], $options['resizeType'], $options['format']) ?>

<?php // This was inside a ul which doesn't make sense ?>


<?php echo link_to('Media Library', '@a_media_index', array('class' => 'a-btn big icon a-arrow-left thin', 'id' => 'media-library-back-button', ))?>

<ul class="a-media-item-content" id="a-media-item-content-<?php echo $mediaItem->getId()?>">
	<li class="a-media-item-source">
		<?php include_partial('aMedia/editLinks', array('mediaItem' => $mediaItem)) ?>
  	<?php echo $embedCode ?>
	</li>

  <?php // Stored as HTML ?>
	<li class="a-media-item-title"><h3><?php echo htmlspecialchars($mediaItem->getTitle()) ?></h3></li>
  <li class="a-media-item-description"><?php echo $mediaItem->getDescription() ?></li>
	<li class="a-media-item-dimensions a-media-item-meta"><span>Original Dimensions:</span> <?php echo $mediaItem->getWidth(); ?>x<?php echo $mediaItem->getHeight(); ?></li>
  <li class="a-media-item-created-at a-media-item-meta"><span>Uploaded:</span> <?php echo aDate::pretty($mediaItem->getCreatedAt()) ?></li>
  <li class="a-media-item-credit a-media-item-meta"><span>Credit:</span> <?php echo htmlspecialchars($mediaItem->getCredit()) ?></li>
  <li class="a-media-item-categories a-media-item-meta"><span>Categories:</span> <?php include_partial('aMedia/showCategories', array('categories' => $mediaItem->getMediaCategories())) ?></li>
  <li class="a-media-item-tags a-media-item-meta"><span>Tags:</span> <?php include_partial('aMedia/showTags', array('tags' => $mediaItem->getTags())) ?></li>
	<li class="a-media-item-download">
		<?php if ($mediaItem->getType() !== 'video'): ?>
        <?php // download link ?>
        <?php echo link_to(
          "Download Original<span></span>",
          "aMediaBackend/original?" .
            http_build_query(
              array(
                "slug" => $mediaItem->getSlug(),
                "format" => $mediaItem->getFormat())), 
                array("class"=>"a-btn icon a-download")) ?>
      <?php endif ?>
	</li>
</ul>

<script type="text/javascript">
function aMediaItemRefresh(id)
{
  <?php // We're updating essentially the whole page, it's not worth building ?>
  <?php // a custom ajax action for it. Also we can ignore the id passed to this ?>
  <?php // function which will always be the one this page was generated for. ?>
  window.location = <?php echo json_encode(url_for("aMedia/show?slug=" . $mediaItem->getSlug())) ?>;
}
</script>

</div>

</div>