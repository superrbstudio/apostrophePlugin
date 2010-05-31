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
	  <?php include_partial('aImageSlot/choose', array('action' => 'aPDFSlot/edit', 'buttonLabel' => 'Choose PDF', 'label' => 'Select a PDF File', 'class' => 'a-btn icon a-pdf', 'type' => 'pdf', 'constraints' => $constraints, 'itemId' => $itemId, 'name' => $name, 'slug' => $slug, 'permid' => $permid)) ?>
	</li>
		<?php include_partial('a/variant', array('pageid' => $pageid, 'name' => $name, 'permid' => $permid, 'slot' => $slot)) ?>	
<?php end_slot() ?>

<?php endif ?>

<?php if ($item): ?>
  <ul>
    <li class="a-context-pdf">
      <?php // Thumbnail image as a link to the original PDF ?>
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
    </li>
    <?php if ($title): ?>
      <li class="a-pdf-title"><?php echo $item->title ?></li>
    <?php endif ?>
    <?php if ($description): ?>
      <li class="a-pdf-description"><?php echo $item->description ?></li>
    <?php endif ?>
  </ul>
	<script type="text/javascript" charset="utf-8">
		$(document).ready(function() {
			$("#a-slot-<?php echo $id ?> .a-context-pdf a").prepend('<div class="a-media-pdf-icon-overlay">Click to Download PDF</div>').attr('title','Click to Download PDF')
		});
	</script>
<?php else: ?>
  <?php if ($defaultImage): ?>
    <ul>
      <li class="a-context-pdf">
        <?php echo image_tag($defaultImage) ?>
      </li>
    </ul>
  <?php endif ?>
<?php endif ?>
