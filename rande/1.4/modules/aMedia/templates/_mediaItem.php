<?php use_helper('I18N') ?>
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
    <?php if ($mediaItem->getWidth() && ($type == 'pdf')): ?><span class="a-media-pdf-btn"></span><?php endif ?>
    <?php if ($mediaItem->getWidth()): ?>
      <img src="<?php echo url_for($mediaItem->getScaledUrl(aMediaTools::getOption('gallery_constraints'))) ?>" />
    <?php else: ?>
      <?php // We can't render this format on this server but we need a placeholder thumbnail ?>
      <?php $type = $mediaItem->getType() ?>
      <img class="a-media-icon-as-thumbnail" src="/apostrophePlugin/images/a-<?php echo $type ?>-icon-tiny.png" />
    <?php endif ?>
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
<?php if ($mediaItem->getWidth()): ?>
  <li class="a-media-item-dimensions a-media-item-meta"><?php echo __('<span>Original Dimensions:</span> %width%x%height%', array('%width%' => $mediaItem->getWidth(), '%height%' => $mediaItem->getHeight()), 'apostrophe') ?></li>
<?php endif ?>
<li class="a-media-item-created-at a-media-item-meta"><?php echo __('<span>Uploaded:</span> %date%', array('%date%' => aDate::pretty($mediaItem->getCreatedAt())), 'apostrophe') ?></li>
<li class="a-media-item-credit a-media-item-meta"><?php echo __('<span>Credit:</span> %credit%', array('%credit%' => htmlspecialchars($mediaItem->getCredit())), 'apostrophe') ?></li>
<li class="a-media-item-categories a-media-item-meta"><?php echo __('<span>Categories:</span> %categories%', array('%categories%' => get_partial('aMedia/showCategories', array('categories' => $mediaItem->getMediaCategories()))), 'apostrophe') ?></li>
<li class="a-media-item-tags a-media-item-meta"><?php echo __('<span>Tags:</span> %tags%', array('%tags%' => get_partial('aMedia/showTags', array('tags' => $mediaItem->getTags()))), 'apostrophe') ?></li>

<?php if ($mediaItem->getType() === 'pdf'): ?>
  <li class="a-media-item-link a-media-item-meta">
		<?php echo __('<span>URL:</span> %urlfield%', array('%urlfield%' => 
		'<input type="text" id="a-media-item-link-value-' . $id . '" name="a-media-item-link-value" value="' . url_for("aMediaBackend/original?".http_build_query(array("slug" => $mediaItem->getSlug(),"format" => $mediaItem->getFormat())), true) . '" />'), 'apostrophe') ?>
	</li>
	
	<script type="text/javascript" charset="utf-8">
		$(document).ready(function() {
			$('#a-media-item-link-value-<?php echo $id ?>').focus(function(){
				$(this).select();
			});
		});
	</script>
<?php endif ?>
  