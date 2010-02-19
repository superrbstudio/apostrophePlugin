<?php use_helper('jQuery') ?>
<?php if ($mediaItem->userHasPrivilege('edit')): ?>
	<ul class="a-controls a-media-edit-links">

    <?php if ($mediaItem->getType() === 'video'): ?>
      <li><?php echo link_to("Edit", "aMedia/editVideo", array("query_string" => http_build_query(array("slug" => $mediaItem->getSlug())), "class" => "a-btn icon a-edit")) ?></li>
    <?php elseif ($mediaItem->getType() === 'pdf'): ?>
      <li><?php echo link_to("Edit", "aMedia/editPdf", array("query_string" => http_build_query(array("slug" => $mediaItem->getSlug())), "class" => "a-btn icon a-edit")) ?></li>
    <?php else: ?>
      <li><?php echo link_to("Edit", "aMedia/editImage", array("query_string" => http_build_query(array("slug" => $mediaItem->getSlug())), "class" => "a-btn icon a-edit")) ?></li>
    <?php endif ?>
  	
		<?php if ($mediaItem->getType() !== 'video' && $sf_params->get('action') == 'show'): ?>
	  <li class="a-media-download-original">
	     <?php // download link ?>
	     <?php echo link_to("Download Original", "aMediaBackend/original?".http_build_query(array(
	             "slug" => $mediaItem->getSlug(),
	             "format" => $mediaItem->getFormat())),
	              array(
					"class"=>"a-btn icon a-download"
					))?>
		</li>
		<?php endif ?>


		<li><?php echo link_to("Delete", "aMedia/delete?" . http_build_query(
    	array("slug" => $mediaItem->getSlug())),
    	array("confirm" => "Are you sure you want to delete this item?", "class"=>"a-btn icon a-delete")) ?></li>
	</ul>
<?php endif ?>
