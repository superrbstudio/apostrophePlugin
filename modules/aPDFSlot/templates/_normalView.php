<?php
  // Compatible with sf_escaping_strategy: true
  $constraints = isset($constraints) ? $sf_data->getRaw('constraints') : null;
  $defaultImage = isset($defaultImage) ? $sf_data->getRaw('defaultImage') : null;
  $description = isset($description) ? $sf_data->getRaw('description') : null;
  $dimensions = isset($dimensions) ? $sf_data->getRaw('dimensions') : null;
  $editable = isset($editable) ? $sf_data->getRaw('editable') : null;
  $embed = isset($embed) ? $sf_data->getRaw('embed') : null;
  $id = isset($id) ? $sf_data->getRaw('id') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
  $item = isset($item) ? $sf_data->getRaw('item') : null;
  $itemId = isset($itemId) ? $sf_data->getRaw('itemId') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $pageid = isset($pageid) ? $sf_data->getRaw('pageid') : null;
  $pdfPreview = isset($pdfPreview) ? $sf_data->getRaw('pdfPreview') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $slot = isset($slot) ? $sf_data->getRaw('slot') : null;
  $slug = isset($slug) ? $sf_data->getRaw('slug') : null;
  $title = isset($title) ? $sf_data->getRaw('title') : null;
?>
<?php use_helper('a') ?>
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
		  <?php include_partial('aImageSlot/choose', array('action' => 'aPDFSlot/edit', 'chooseLabel' => a_get_option($options, 'chooseLabel', a_('Choose PDF')), 'label' => a_get_option($options, 'browseLabel', a_('Select a PDF File')), 'class' => 'a-btn icon a-pdf', 'type' => 'pdf', 'constraints' => $constraints, 'itemId' => $itemId, 'name' => $name, 'slug' => $slug, 'permid' => $permid)) ?>
			<?php include_partial('a/variant', array('pageid' => $pageid, 'name' => $name, 'permid' => $permid, 'slot' => $slot)) ?>	
	<?php end_slot() ?>
	
<?php endif ?>

<?php if ($item): ?>
    <div class="a-pdf-slot<?php echo ($pdfPreview)? ' with-preview': ' no-label' ?>">

			<div class="a-media-pdf-icon">
      <?php // Thumbnail image as a link to the original PDF ?>
			<?php if ($pdfPreview): ?>

	      <?php echo link_to($embed,
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
			<li class="a-pdf-download">
	      <?php echo link_to(__("Download PDF", null, 'apostrophe'), "aMediaBackend/original?" .
								http_build_query(
								array(
								"slug" => $item->getSlug(),
	              "format" => $item->getFormat()
	 							))) ?>
		   </li>
  </ul>
</div>

	<?php if ($pdfPreview): ?>
		<script type="text/javascript">
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
			<script type="text/javascript">
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
