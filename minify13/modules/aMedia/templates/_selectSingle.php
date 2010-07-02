<?php $type = aMediaTools::getAttribute('type') ?>
<?php if (!$type): ?>
<?php $type = "media item" ?>
<?php endif ?>

<div class="a-media-select">
  <p>Use the browsing and searching features to locate the <?php echo $type ?> you want, then click on that <?php echo $type ?> to select it.
  <?php if ($limitSizes): ?>
  Only appropriately sized <?php echo $type ?>s are shown.
  <?php endif ?>
  </p>
	<?php 
		//removing this cancel link for now, it exists above in the header
		// echo link_to("cancel", "aMedia/selectCancel", array("class"=>"a-cancel")) 
	?>
</div>
