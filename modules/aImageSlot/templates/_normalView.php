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
	<li class="a-controls-item choose-image">
  <?php echo link_to('Choose image',
    sfConfig::get('app_aMedia_client_site', false) . "/media/select?" .
      http_build_query(
        array_merge(
          $constraints,
          array(
          "aMediaId" => $itemId,
          "type" => "image",
          "label" => "Select an Image",
          "after" => url_for("aImageSlot/edit") . "?" .
            http_build_query(
              array(
                "slot" => $name, 
                "slug" => $slug, 
                "actual_slug" => aTools::getRealPage()->getSlug(),
                "permid" => $permid,
                "noajax" => 1)), true))),
    array('class' => 'a-btn icon a-media')) ?>
	</li>
<?php end_slot() ?>
<?php endif ?>

<?php // one set of code with or without a real item so I don't goof ?>
<?php if ((!$item) && ($defaultImage)): ?>
  <?php $item = new stdclass() ?>
  <?php $item->title = '' ?>
  <?php $item->description = '' ?>
  <?php $item->embed = "<img src='$defaultImage' />" ?>
<?php endif ?>
<?php if ($item): ?>
  <ul>
    <li class="a-context-image">
    <?php if (isset($dimensions)): ?>
      <?php $embed = str_replace(
        array("_WIDTH_", "_HEIGHT_", "_c-OR-s_", "_FORMAT_"),
        array($dimensions['width'], 
          $dimensions['height'],
          $dimensions['resizeType'],
          $dimensions['format']),
        $item->embed) ?>
    <?php else: ?>
      <?php $embed = $item->embed ?>
    <?php endif ?>
    <?php if ($link): ?>
      <?php $embed = "<a href=\"$link\">$embed</a>" ?>
    <?php endif ?>
    <?php echo $embed ?>
    </li>
    <?php if ($title): ?>
      <li class="a-image-title"><?php echo $item->title ?></li>
    <?php endif ?>
    <?php if ($description): ?>
      <li class="a-image-description"><?php echo $item->description ?></li>
    <?php endif ?>
  </ul>
<?php endif ?>