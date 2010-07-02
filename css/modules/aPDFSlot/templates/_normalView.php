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

<?php slot("a-slot-controls-$pageid-$name-$permid") ?>
	<li class="a-controls-item choose-pdf">
	  <?php include_partial('aImageSlot/choose', array('action' => 'aPDFSlot/edit', 'buttonLabel' => __('Choose PDF', null, 'apostrophe'), 'label' => __('Select a PDF File', null, 'apostrophe'), 'class' => 'a-btn icon a-pdf', 'type' => 'pdf', 'constraints' => $constraints, 'itemId' => $itemId, 'name' => $name, 'slug' => $slug, 'permid' => $permid)) ?>
	</li>
		<?php include_partial('a/variant', array('pageid' => $pageid, 'name' => $name, 'permid' => $permid, 'slot' => $slot)) ?>	
<?php end_slot() ?>

<?php endif ?>

<?php if ($item): ?>
    <div class="a-pdf-slot<?php echo ($pdfPreview)? ' with-preview': ' no-label' ?>">

			<div class="a-media-pdf-icon">
      <?php // Thumbnail image as a link to the original PDF ?>
			<?php if ($pdfPreview): ?>

	      <?php echo link_to(str_replace(
	          array("_WIDTH_", "_HEIGHT_", "_c-OR-s_", "_FORMAT_"),
	          array($dimensions['width'], 
	            $dimensions['height'],
	            $dimensions['resizeType'],
	            $dimensions['format']),
	          $embed), 
	        "aMediaBackend/original?" .
	          http_build_query(
	            array(
	              "slug" => $item->getSlug(),
	              "format" => $item->getFormat()))) ?>

			<?php else: ?>


				<?php echo link_to(__('Download PDF', null, 'apostrophe'), "aMediaBackend/original?" .
								http_build_query(
								array(
								"slug" => $item->getSlug(),
	              "format" => $item->getFormat()
	 							))) ?>	
				
	    <?php endif ?>
			</div>
			
  <ul class="a-pdf-meta">
    <?php if ($title): ?>
      <li class="a-pdf-title"><?php echo $item->title ?></li>
    <?php endif ?>
    <?php if ($description): ?>
      <li class="a-pdf-description"><?php echo $item->description ?>
			</li>
    <?php endif ?>
			<p class="a-pdf-download">
	      <?php echo link_to(__("Download PDF", null, 'apostrophe'), "aMediaBackend/original?" .
								http_build_query(
								array(
								"slug" => $item->getSlug(),
	              "format" => $item->getFormat()
	 							))) ?>
		    </p>
  </ul>
</div>

	<?php if ($pdfPreview): ?>
		<script type="text/javascript" charset="utf-8">
			$(document).ready(function() {
	
				var pdfImg = $("#a-slot-<?php echo $id ?> .a-pdf-slot a img");

				pdfImg.hover(function(){
					pdfImg.fadeTo(0,.5);
				},function(){
					pdfImg.fadeTo(0,1);			
				});

			});
		</script>
	<?php else: ?>
			<script type="text/javascript" charset="utf-8">
				$(document).ready(function() {
					
					var pdfImg = $("#a-slot-<?php echo $id ?> .a-pdf-slot .a-media-pdf-icon");

					pdfImg.hover(function(){
						pdfImg.fadeTo(0,.5);
					},function(){
						pdfImg.fadeTo(0,1);
					});

				});
			</script>
	<?php endif ?>
	
<?php else: ?>
  <?php if ($defaultImage): ?>
    <ul>
      <li class="a-pdf-slot">
        <?php echo image_tag($defaultImage) ?>
      </li>
    </ul>
  <?php endif ?>
<?php endif ?>
