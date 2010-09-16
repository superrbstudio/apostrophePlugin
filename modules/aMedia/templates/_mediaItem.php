<?php
  // Compatible with sf_escaping_strategy: true
  $mediaItem = isset($mediaItem) ? $sf_data->getRaw('mediaItem') : null;
?>
<?php use_helper('a') ?>
<?php $type = $mediaItem->getType() ?>
<?php $id = $mediaItem->getId() ?>
<?php $domId = 'a-media-thumb-link-' . $id ?>
<?php $serviceUrl = $mediaItem->getServiceUrl() ?>
<?php $slug = $mediaItem->getSlug() ?>
<?php $format = $mediaItem->getFormat() ?>

<?php if (aMediaTools::isSelecting()): ?>
	<?php if (aMediaTools::isMultiple() || ($type === 'image')): ?>
  	<?php // This was more complex before the a.js refactoring ?>
    <?php $linkAttributes = 'href= "#select-image"' ?>
  <?php else: ?>
    <?php // Non-image single select. The multiple add action is a bit of a misnomer here ?>
    <?php // and redirects to aMedia/selected after adding the media item ?>
    <?php $linkAttributes = 'href = "' . url_for("aMedia/multipleAdd?id=$id") . '"' ?>
  <?php endif ?>
<?php else: ?>
  <?php $linkAttributes = 'href = "' . url_for("aMedia/show?" . http_build_query(array("slug" => $slug))) . '"' ?>
<?php endif ?>

<div id="a-media-item-<?php echo $mediaItem->getId() ?>" class="a-ui a-media-item <?php echo ($i%$layout['columns'] < $layout['columns'] - 1)? 'nlast' : 'last' ?> <?php echo $format ?>">

	<div class="a-media-item-thumbnail">
	  <a <?php echo $linkAttributes ?> class="a-media-thumb-link" id="<?php echo $domId ?>">
	    <?php if ($type == 'video'): ?><span class="a-media-play-btn"></span><?php endif ?>
	    <?php if ($mediaItem->getWidth() && ($type == 'pdf')): ?><span class="a-media-pdf-btn"></span><?php endif ?>
	    <?php if ($mediaItem->getWidth()): ?>
	      <img src="<?php echo url_for($mediaItem->getScaledUrl($layout['gallery_constraints'])) ?>" />
	    <?php else: ?>
	      <?php // We can't render this format on this server but we need a placeholder thumbnail ?>
				<span class="a-media-type <?php echo $format ?>" ><b><?php echo $format ?></b></span>
	    <?php endif ?>
	  </a>
	</div>

	<div class="a-media-item-information">
		<ul>
			<?php if(isset($layout['fields']['title'])): ?>
				<li class="a-media-item-title <?php if (!$mediaItem->getWidth()): ?>no-thumbnail<?php endif ?>">
					<h3>
						<div class="a-media-item-controls">
							<?php include_partial('aMedia/editLinks', array('mediaItem' => $mediaItem)) ?>
						</div>							
						<a <?php echo $linkAttributes ?> class="a-media-item-title-link"><?php echo htmlspecialchars($mediaItem->getTitle()) ?></a>
						<?php if ($mediaItem->getViewIsSecure()): ?><span class="a-media-is-secure"></span><?php endif ?>
					</h3>
				</li>
			<?php endif ?>
		
			<?php // John: you could use $mediaItem->format to choose an icon here. Make sure ?>
			<?php // you have a default icon if it's not on your list of awesome icons ?>
			<?php if(isset($layout['fields']['description'])): ?>
				<li class="a-media-item-description"><?php echo $mediaItem->getDescription() ?></li>
			<?php endif ?>

			<?php if(isset($layout['fields']['link'])): ?>
				<?php if ($mediaItem->getDownloadable()): ?>
				  <li class="a-media-item-link a-media-item-meta">
						<?php echo __('<span>Permalink:</span> %urlfield%', array('%urlfield%' => 
						'<input type="text" class="a-select-on-focus" id="a-media-item-link-value-' . $id . '" name="a-media-item-link-value" value="' . url_for("aMediaBackend/original?".http_build_query(array("slug" => $mediaItem->getSlug(),"format" => $mediaItem->getFormat())), true) . '" />'), 'apostrophe') ?>
					</li>
				<?php endif ?>
			<?php endif ?>			

			<?php if(isset($layout['fields']['created_at'])): ?>
				<li class="a-media-item-created-at a-media-item-meta"><?php echo __('<span>Uploaded:</span> %date%', array('%date%' => aDate::pretty($mediaItem->getCreatedAt())), 'apostrophe') ?></li>
			<?php endif ?>

			<?php if(isset($layout['fields']['dimensions'])): ?>
			  <?php if ($mediaItem->getWidth()): ?>
			    <li class="a-media-item-dimensions a-media-item-meta"><?php echo __('<span>Original Dimensions:</span> %width%x%height%', array('%width%' => $mediaItem->getWidth(), '%height%' => $mediaItem->getHeight()), 'apostrophe') ?></li>
			  <?php endif ?>
			<?php endif ?>

			<?php if(isset($layout['fields']['credit'])): ?>
				<?php if ($mediaItem->getCredit()): ?>
					<li class="a-media-item-credit a-media-item-meta"><?php echo __('<span>Credit:</span> %credit%', array('%credit%' => htmlspecialchars($mediaItem->getCredit())), 'apostrophe') ?></li>					
				<?php endif ?>
			<?php endif ?>

			<li class="a-media-item-spacer a-media-item-meta">&nbsp;</li>

			<?php if(isset($layout['fields']['categories'])): ?>
				<?php if (count($mediaItem->getMediaCategories())): ?>
					<li class="a-media-item-categories a-media-item-meta"><?php echo __('<span>Categories:</span> %categories%', array('%categories%' => get_partial('aMedia/showCategories', array('categories' => $mediaItem->getMediaCategories()))), 'apostrophe') ?></li>					
				<?php endif ?>
			<?php endif ?>

			<?php if(isset($layout['fields']['tags'])): ?>
				<?php if (count($mediaItem->getTags())): ?>
					<li class="a-media-item-tags a-media-item-meta"><?php echo __('<span>Tags:</span> %tags%', array('%tags%' => get_partial('aMedia/showTags', array('tags' => $mediaItem->getTags()))), 'apostrophe') ?></li>					
				<?php endif ?>
			<?php endif ?>
			
			<?php //Not sure how to make the permissions display ?>
			<?php if(isset($layout['fields']['view_is_secure'])): ?>
					<li class="a-media-item-permissions a-media-item-meta">
						<?php if ($mediaItem->getViewIsSecure()): ?>						
							<span class="a-media-item-permissions-icon private"></span><?php echo __('This %type% is private.', array(), 'apostrophe') ?>
						<?php else: ?>
							<span class="a-media-item-permissions-icon public"></span><?php echo __('This %type% can be viewed by everyone.', array(), 'apostrophe') ?>
						<?php endif ?>
					</li>					
			<?php endif ?>
			
			<?php //this li for the replace and download links can be a partial so it can be used in the edit view. ?>
			<li class="a-media-item-download-and-replace a-media-item-meta">
				
				<?php if ($mediaItem->getType() !== 'video'): ?>
		      <div class="a-media-item-download-link">  
						<?php echo link_to(__("Download Original%buttonspan%", array('%buttonspan%' => "<span></span>"), 'apostrophe'),	"aMediaBackend/original?" .http_build_query(array("slug" => $mediaItem->getSlug(), "format" => $mediaItem->getFormat())), array("class"=>"a-btn icon a-download lite alt")) ?>
					</div>
				<?php endif ?>
				
				<div class="a-form-row replace a-ui">		
					<div class="a-options-container">		
						<a href="#replace-image" onclick="return false;" id="a-media-replace-image-<?php echo $i ?>" class="a-btn icon a-replace lite alt"><span class="icon"></span>Replace File</a>
						<div class="a-options dropshadow">
							<?php // This form isn't available in this view, it was throwing an error ?>
	  		      <?php // echo $form['file']->renderLabel() ?>
	  		      <?php // echo $form['file']->renderError() ?>
	  		      <?php // echo $form['file']->render() ?>
	  		    </div>
	  				<?php a_js_call('apostrophe.menuToggle(?)', array('button' => '#a-media-replace-image-'.$i, 'classname' => '', 'overlay' => false)) ?>
	  			</div>
	  		</div>
	
			</li>
		
		</ul>
	</div>
</div>

<?php a_js_call('apostrophe.setObjectId(?, ?)', $domId, $id) ?>

<script type="text/javascript" charset="utf-8">
$(document).ready(function() {
	var thumbnail = $('.four-up .a-media-item-thumbnail');
	thumbnail.each(function(){
		newHeight = $(this).find('img').attr('height');
		$(this).css('height',newHeight);
	});
});

</script>