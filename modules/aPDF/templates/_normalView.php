<?php if ($editable): ?>
  <?php // Normally we have an editor inline in the page, but in this ?>
  <?php // case we'd rather use the picker built into the media plugin. ?>
  <?php // So we link to the media picker and specify an 'after' URL that ?>
  <?php // points to our slot's edit action. Setting the ajax parameter ?>
  <?php // to false causes the edit action to redirect to the newly ?>
  <?php // updated page. ?>
  <?php // Wrap controls in a slot to be inserted in a slightly different ?>
  <?php // context by the _area.php template ?>

<?php slot("a-slot-controls-$name-$permid") ?>
	<li class="a-controls-item choose-pdf">
  <?php echo link_to('Choose PDF',
    sfConfig::get('app_aMedia_client_site', false) . "/media/select?" .
      http_build_query(
        array_merge(
          $constraints,
          array(
          "aMediaId" => $itemId,
          "type" => "pdf",
          "label" => "Select a PDF Document",
          "after" => url_for("aPDF/edit") . "?" .
            http_build_query(
              array(
                "slot" => $name, 
                "slug" => $slug, 
                "actual_slug" => aTools::getRealPage()->getSlug(),
                "permid" => $permid,
                "noajax" => 1)), true))),
    array('class' => 'a-btn icon a-pdf')) ?>
	</li>
<?php end_slot() ?>

<?php endif ?>

<?php if ($item): ?>
  <ul>
    <li class="a-context-pdf">
      <a href="<?php echo $item->original ?>">
        <?php // JOHN: make the PDF-ness visible here, perhaps as a semiopaque overlay ?>
        <?php // of the Adobe PDF icon ?>
        <?php $embed = str_replace(
          array("_WIDTH_", "_HEIGHT_", "_c-OR-s_", "_FORMAT_"),
          array($dimensions['width'], 
            $dimensions['height'],
            $dimensions['resizeType'],
            $dimensions['format']),
          $item->embed) ?>
        <?php echo $embed ?>
      </a>
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
