<?php
  // Compatible with sf_escaping_strategy: true
  $class = isset($class) ? $sf_data->getRaw('class') : null;
  $controlsSlot = isset($controlsSlot) ? $sf_data->getRaw('controlsSlot') : null;
  $label = isset($label) ? $sf_data->getRaw('label') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $pageid = isset($pageid) ? $sf_data->getRaw('pageid') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $title = isset($title) ? $sf_data->getRaw('title') : null;
  $slot = isset($slot) ? $sf_data->getRaw('slot') : null;
?>
<?php use_helper('a') ?>
<?php if (is_null($slot)): ?>
  Apostrophe 1.5: this slot's normalView partial must be upgraded to pass slot => $slot as one of its parameters to the simpleEditButton partial.
<?php else: ?>
  <?php if (!isset($controlsSlot)): ?>
    <?php $controlsSlot = true ?>
  <?php endif ?>

  <?php if ($controlsSlot): ?>
  	<?php slot("a-slot-controls-$pageid-$name-$permid") ?>
  <?php endif ?>

  <li>
  <?php echo jq_link_to_function(isset($label) ? __($label, null, 'apostrophe') : __("edit", null, 'apostrophe'), "", 
  			array(
  				'id' => "a-slot-edit-$pageid-$name-$permid",
  				'class' => isset($class) ? $class : 'a-btn icon a-edit', 
  				'title' => isset($title) ? $title : __('Edit', null, 'apostrophe'), 
  )) ?>

  <script type="text/javascript" charset="utf-8">
  <?php // TODO: Rewrite this as a button class scoped to ALL edit buttons so there's only a single instance of this Javascript ?>
  	$(document).ready(function() {
  	  <?php // This is now AJAX code to load the edit view on demand ?>
  		var editBtn = $('#a-slot-edit-<?php echo "$pageid-$name-$permid" ?>');
  		var editSlot = $('#a-slot-<?php echo "$pageid-$name-$permid" ?>');
  		editBtn.click(function(event){
  		  if (!editSlot.children('.a-slot-content').children('.a-slot-form').length)
  		  {
    		  $.get(<?php echo json_encode(url_for($slot->type . 'Slot/ajaxEditView') . '?' . http_build_query(array('id' => $pageid, 'slot' => $name, 'permid' => $permid))) ?>, { }, function(data) { 
  		      editSlot.children('.a-slot-content').html(data);
  		      apostrophe.slotShowEditView(editBtn, editSlot);
  		    });
  		  }
  		  else
  		  {
  		    // Reuse edit view
		      apostrophe.slotShowEditView(editBtn, editSlot);
  		  }
  		  return false;
  		});
  	});
  </script>
  </li>
	
  <?php if ($controlsSlot): ?>
  	<?php end_slot() ?>
  <?php endif ?>
<?php endif ?>

