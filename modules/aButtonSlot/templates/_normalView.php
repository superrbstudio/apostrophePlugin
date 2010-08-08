<?php
  // Compatible with sf_escaping_strategy: true
  $constraints = isset($constraints) ? $sf_data->getRaw('constraints') : null;
  $defaultImage = isset($defaultImage) ? $sf_data->getRaw('defaultImage') : null;
  $description = isset($description) ? $sf_data->getRaw('description') : null;
  $dimensions = isset($dimensions) ? $sf_data->getRaw('dimensions') : null;
  $editable = isset($editable) ? $sf_data->getRaw('editable') : null;
  $img_title = isset($img_title) ? $sf_data->getRaw('img_title') : null;
  $item = isset($item) ? $sf_data->getRaw('item') : null;
  $itemId = isset($itemId) ? $sf_data->getRaw('itemId') : null;
  $link = isset($link) ? $sf_data->getRaw('link') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
  $page = isset($page) ? $sf_data->getRaw('page') : null;
  $pageid = isset($pageid) ? $sf_data->getRaw('pageid') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $slot = isset($slot) ? $sf_data->getRaw('slot') : null;
  $slug = isset($slug) ? $sf_data->getRaw('slug') : null;
?>
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

  <?php // Very short labels so sidebar slots don't have wrap in their controls. ?>
  <?php // That spoils assumptions that are being made elsewhere that they will ?>
  <?php // amount to only one row. TODO: find a less breakage-prone solution to that problem. ?>

  <?php slot("a-slot-controls-$pageid-$name-$permid") ?>
  	<li class="a-controls-item choose-image">
  	  <?php include_partial('aImageSlot/choose', array('action' => 'aButtonSlot/image', 'buttonLabel' => __('Choose image', null, 'apostrophe'), 'label' => __('Select an Image', null, 'apostrophe'), 'class' => 'a-btn icon a-media', 'type' => 'image', 'constraints' => $constraints, 'itemId' => $itemId, 'name' => $name, 'slug' => $slug, 'permid' => $permid)) ?>
  	</li>
			<?php include_partial('a/simpleEditWithVariants', array('pageid' => $page->id, 'name' => $name, 'permid' => $permid, 'slot' => $slot, 'page' => $page, 'controlsSlot' => false)) ?>
  <?php end_slot() ?>
<?php endif ?>

<?php if (!$item): ?>
	<?php if (isset($options['singleton']) != true): ?>
		<?php (isset($options['width']))?  $style = 'width:' .  $options['width'] .'px;': $style = 'width:100%;'; ?>
		<?php (isset($options['height']))? $height = $options['height'] : $height = ((isset($options['width']))? floor($options['width']*.56):'100'); ?>		
		<?php $style .= 'height:'.$height.'px;' ?>
		<div class="a-media-placeholder" style="<?php echo $style ?>">
			<span style="line-height:<?php echo $height ?>px;"><?php echo __("Create a Button", null, 'apostrophe') ?></span>
		</div>
	<?php endif ?>
<?php endif ?>

<?php if ($item): ?>
  <ul id="a-button-<?php echo $pageid.'-'.$name.'-'.$permid; ?>" class="a-button">
    <li class="a-button-image">
    <?php $embed = str_replace(
      array("_WIDTH_", "_HEIGHT_", "_c-OR-s_", "_FORMAT_"),
      array($dimensions['width'], 
        $dimensions['height'],
        $dimensions['resizeType'],
        $dimensions['format']),
        $embed) ?>
    <?php if ($link): ?>
      <?php $embed = "<a class=\"a-button-link\" href=\"$link\">$embed</a>" ?>
    <?php endif ?>
    <?php echo $embed ?>
    </li>
    <?php if (isset($img_title) && $img_title != ''): ?>
      <li class="a-button-title"><a class="a-button-link" href="<?php echo $link ?>"><?php echo $img_title ?></a></li>
    <?php endif ?>
    <?php if (isset($description)): ?>
      <li class="a-button-description"><?php echo $item->description ?></li>
    <?php endif ?>
  </ul>
<?php else: ?>
  <?php if ($defaultImage): ?>
  	<ul id="a-button-<?php echo $pageid.'-'.$name.'-'.$permid; ?>" class="a-button default">
      <li class="a-button-image">
        <?php // Corner case: they've set the link but are still using the default image ?>
        <?php if ($link): ?>
          <?php echo link_to(image_tag($defaultImage), $link) ?>
        <?php else: ?>
          <?php echo image_tag($defaultImage) ?>
        <?php endif ?>
      </li>
    </ul>
  <?php endif ?>
<?php endif ?>

<?php // TODO: Get this JS out of here and into an external JS file ?>
<script type="text/javascript" charset="utf-8">
	$(document).ready(function() {

		var btnImg = $('#a-button-<?php echo $pageid.'-'.$name.'-'.$permid; ?> li.a-button-image a img');
		var btnTitle = $('#a-button-<?php echo $pageid.'-'.$name.'-'.$permid; ?> a.a-button-link');		

		btnImg.hover(function(){
			btnImg.fadeTo(0,.5);
		},function(){
			btnImg.fadeTo(0,1);			
		});

		btnTitle.hover(function(){
			btnImg.fadeTo(0,.5);
		},function(){
			btnImg.fadeTo(0,1);			
		});		
		
	});
</script>