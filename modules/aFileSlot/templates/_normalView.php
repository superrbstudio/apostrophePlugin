<?php
  // Compatible with sf_escaping_strategy: true
  $description = isset($description) ? $sf_data->getRaw('description') : null;
  $editable = isset($editable) ? $sf_data->getRaw('editable') : null;
  $id = isset($id) ? $sf_data->getRaw('id') : null;
  $item = isset($item) ? $sf_data->getRaw('item') : null;
  $itemId = isset($itemId) ? $sf_data->getRaw('itemId') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $pageid = isset($pageid) ? $sf_data->getRaw('pageid') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $slot = isset($slot) ? $sf_data->getRaw('slot') : null;
  $slug = isset($slug) ? $sf_data->getRaw('slug') : null;
  $title = isset($title) ? $sf_data->getRaw('title') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
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
		  <?php include_partial('aImageSlot/choose', array('action' => 'aFileSlot/edit', 'buttonLabel' => a_get_option($options, 'chooseLabel', a_('Choose File')), 'label' => a_get_option($options, 'browseLabel', a_('Select a File')), 'type' => '_downloadable', 'class' => 'a-btn icon a-file', 'downloadable' => '1', 'itemId' => $itemId, 'name' => $name, 'slug' => $slug, 'permid' => $permid, 'now' => false)) ?>
			<?php include_partial('a/variant', array('pageid' => $pageid, 'name' => $name, 'permid' => $permid, 'slot' => $slot)) ?>	
	<?php end_slot() ?>
	
<?php endif ?>

<?php if ($item): ?>
    <div class="a-file-slot">
			<div class="a-media-file-icon">
  			<?php slot('a_button') ?>
  			  <span class="a-media-type <?php echo $item->format ?>" ><b><?php echo $item->format ?></b></span>
        <?php end_slot() ?>
				<?php echo link_to(get_slot('a_button'), "aMediaBackend/original?" .
								http_build_query(
								array(
								"slug" => $item->getSlug(),
	              "format" => $item->getFormat()
	 							)), array('target' => 'false', )) ?>	
			</div>
			
      <ul class="a-file-meta">
        <?php if ($title): ?>
          <li class="a-file-title"><?php echo $item->title ?></li>
        <?php endif ?>
        <?php if ($description): ?>
          <li class="a-file-description"><?php echo $item->description ?>
    			</li>
        <?php endif ?>
    			<li class="a-file-download">
    	      <?php echo link_to(__("Download file", null, 'apostrophe'), "aMediaBackend/original?" .
    								http_build_query(
    								array(
    								"slug" => $item->getSlug(),
    	              "format" => $item->getFormat()
    	 							)), array('target' => 'false', )) ?>
    		   </li>
      </ul>
    </div>

    <?php // TODO John, this should be some kind of CSS sauce thing and not explicit on every slot ?>
		<script type="text/javascript">
			$(document).ready(function() {
				
				var fileImg = $("#a-slot-<?php echo $id ?> .a-file-slot .a-media-file-icon");

				fileImg.hover(function(){
					fileImg.fadeTo(0,.5);
				},function(){
					fileImg.fadeTo(0,1);
				});

			});
		</script>
<?php endif ?>
