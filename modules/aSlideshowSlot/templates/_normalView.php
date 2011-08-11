<?php
  // Compatible with sf_escaping_strategy: true
  $editable = isset($editable) ? $sf_data->getRaw('editable') : null;
  $id = isset($id) ? $sf_data->getRaw('id') : null;
  $itemIds = isset($itemIds) ? $sf_data->getRaw('itemIds') : null;
  $items = isset($items) ? $sf_data->getRaw('items') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
  $pageid = isset($pageid) ? $sf_data->getRaw('pageid') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $slot = isset($slot) ? $sf_data->getRaw('slot') : null;
  $slug = isset($slug) ? $sf_data->getRaw('slug') : null;
?>
<?php use_helper('a') ?>

<?php if ($editable): ?>

 <?php // Normally we have an editor inline in the page, but in this ?>
 <?php // case we'd rather use the picker built into the media plugin. ?>
 <?php // So we link to the media picker and specify an 'after' URL that ?>
 <?php // points to our slot's edit action. Setting the ajax parameter ?>
 <?php // to false causes the edit action to redirect to the newly ?>
 <?php // updated page. ?>

 <?php slot("a-slot-controls-$pageid-$name-$permid") ?>
   <li class="a-controls-item choose-images">
     <?php aRouteTools::pushTargetEngineSlug('/admin/media', 'aMedia') ?>
     <?php echo link_to('<span class="icon"></span>' . a_get_option($options, 'chooseLabel', a_('Choose Images')),
       'aMedia/select',
       array(
         'query_string' => 
           http_build_query(
             array_merge(
               $options['constraints'],
               array("multiple" => true,
               "aMediaIds" => implode(",", $itemIds),
               "type" => "image",
               "label" => a_get_option($options, 'browseLabel', a_('You are creating a slideshow of images.')),
               "after" => a_url('aSlideshowSlot', 'edit') . "?" . 
                 http_build_query(
                   array(
                     "slot" => $name, 
                     "slug" => $slug, 
                     "permid" => $permid,
                     // actual_url will be added by JS, window.location is more reliable than
                     // guessing at the full context here when we might be in an AJAX update etc.
                     "noajax" => 1))))),
         'class' => 'a-btn icon a-media a-inject-actual-url')) ?>
     <?php aRouteTools::popTargetEnginePage('aMedia') ?>
   </li>

   <?php include_partial('a/variant', array('pageid' => $pageid, 'name' => $name, 'permid' => $permid, 'slot' => $slot)) ?>

 <?php end_slot() ?>

<?php endif ?>

<?php if (count($items)): ?>
	<?php include_component('aSlideshowSlot', $options['slideshowTemplate'], array('items' => $items, 'id' => $id, 'options' => $options)) ?>
<?php else: ?>
	<?php include_partial('aImageSlot/placeholder', array('placeholderText' => a_("Choose Photos"), 'options' => $options)) ?>
<?php endif ?>