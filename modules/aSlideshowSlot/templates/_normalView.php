<?php if ($editable): ?>
  <?php // Normally we have an editor inline in the page, but in this ?>
  <?php // case we'd rather use the picker built into the media plugin. ?>
  <?php // So we link to the media picker and specify an 'after' URL that ?>
  <?php // points to our slot's edit action. Setting the ajax parameter ?>
  <?php // to false causes the edit action to redirect to the newly ?>
  <?php // updated page. ?>

  <?php slot("a-slot-controls-$pageid-$name-$permid") ?>
    <li class="a-controls-item choose-images">
    <?php echo link_to('Choose images',
      'aMedia/select',
      array(
        'query_string' => 
          http_build_query(
            array_merge(
              $options['constraints'],
              array("multiple" => true,
              "aMediaIds" => implode(",", $itemIds),
              "type" => "image",
              "label" => "Create a Slideshow",
              "after" => url_for("aSlideshowSlot/edit") . "?" . 
                http_build_query(
                  array(
                    "slot" => $name, 
                    "slug" => $slug, 
                    "permid" => $permid,
                    "actual_slug" => aTools::getRealPage()->getSlug(),
                    "noajax" => 1))))),
        'class' => 'a-btn icon a-media')) ?>
    </li>
  <?php end_slot() ?>
<?php endif ?>

<?php include_component('aSlideshowSlot', 'slideshow', array('items' => $items, 'id' => $id, 'options' => $options)) ?>

