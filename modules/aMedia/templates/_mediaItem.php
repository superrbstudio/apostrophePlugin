<?php $type = $mediaItem->getType() ?>
<?php $id = $mediaItem->getId() ?>
<?php $serviceUrl = $mediaItem->getServiceUrl() ?>
<?php $slug = $mediaItem->getSlug() ?>

<?php if (aMediaTools::isSelecting()): ?>

  <?php if (aMediaTools::isMultiple()): ?>
    <?php $linkAttributes = 'href = "#" onClick="'. 
      jq_remote_function(array(
				"update" => "a-media-selection-list",
				'complete' => "aUI('a-media-selection-list');",  
        "url" => "aMedia/multipleAdd?id=$id")).'; return false;"' ?>
  <?php else: ?>
    <?php $linkAttributes = 'href = "' . url_for("aMedia/selected?id=$id") . '"' ?>
  <?php endif ?>

<?php else: ?>

  <?php $linkAttributes = 'href = "' . url_for("aMedia/show?" . http_build_query(array("slug" => $slug))) . '"' ?>

<?php endif ?>

<li class="a-media-item-thumbnail">
<?php include_partial('aMedia/editLinks', array('mediaItem' => $mediaItem)) ?>
  <a <?php echo $linkAttributes ?> class="a-media-thumb-link">
    <?php if ($type == 'video'): ?><span class="a-media-play-btn"></span><?php endif ?>
    <?php if ($type == 'pdf'): ?><span class="a-media-pdf-btn"></span><?php endif ?>
    <img src="<?php echo url_for($mediaItem->getScaledUrl(aMediaTools::getOption('gallery_constraints'))) ?>" />
  </a>
</li>

<?php // Stored as HTML ?>
<li class="a-media-item-title">
	<h3>
		<a <?php echo $linkAttributes ?>><?php echo htmlspecialchars($mediaItem->getTitle()) ?></a>
		<?php if ($mediaItem->getViewIsSecure()): ?><span class="a-media-is-secure"></span><?php endif ?>
	</h3>
</li>

<li class="a-media-item-description"><?php echo $mediaItem->getDescription() ?></li>
<li class="a-media-item-dimensions a-media-item-meta"><span>Original Dimensions:</span> <?php echo $mediaItem->getWidth(); ?>x<?php echo $mediaItem->getHeight(); ?></li>
<li class="a-media-item-createdat a-media-item-meta"><span>Uploaded:</span> <?php echo aDate::pretty($mediaItem->getCreatedAt()) ?></li>
<li class="a-media-item-credit a-media-item-meta"><span>Credit:</span> <?php echo htmlspecialchars($mediaItem->getCredit()) ?></li>
<li class="a-media-categories a-media-item-meta"><span>Categories:</span> <?php include_partial('aMedia/showCategories', array('categories' => $mediaItem->getMediaCategories())) ?></li>
<li class="a-media-item-tags a-media-item-meta"><span>Tags:</span> <?php include_partial('aMedia/showTags', array('tags' => $mediaItem->getTags())) ?></li>

<?php if ($mediaItem->getType() === 'pdf'): ?>
  <li class="a-media-item-link a-media-item-meta">
		<span>URL:</span>
		<input type="text" id="a-media-item-link-value-<?php echo $id ?>" name="a-media-item-link-value" value="<?php echo url_for("aMedia/original?".http_build_query(array("slug" => $mediaItem->getSlug(),"format" => $mediaItem->getFormat())), true) ?>">
	</li>
	
	<script type="text/javascript" charset="utf-8">
		$(document).ready(function() {
			$('#a-media-item-link-value-<?php echo $id ?>').focus(function(){
				$(this).select();
			})
		});
		
	</script>
<?php endif ?>
  