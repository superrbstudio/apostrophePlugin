<?php
  // Compatible with sf_escaping_strategy: true
  $editable = isset($editable) ? $sf_data->getRaw('editable') : null;
  $infinite = isset($infinite) ? $sf_data->getRaw('infinite') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
  $page = isset($page) ? $sf_data->getRaw('page') : null;
  $pageid = isset($pageid) ? $sf_data->getRaw('pageid') : null;
  $preview = isset($preview) ? $sf_data->getRaw('preview') : null;
  $refresh = isset($refresh) ? $sf_data->getRaw('refresh') : null;
  $slots = isset($slots) ? $sf_data->getRaw('slots') : null;
?>

<?php use_helper('a') ?>

<?php if ($editable): ?>
<?php slot('a-history-controls') ?>
	<li>
		<?php $history_button_style = sfConfig::get('app_a_history_button_style', 'no-label big') ?>
		<?php $history_button_id = "a-area-$pageid-$name-history-button" ?>
		<?php echo a_js_button(a_('History'), array('icon', 'a-history-btn', ((!$infinite) ? str_replace('big','',$history_button_style) : $history_button_style)), $history_button_id, a_('Area History')) ?>
		<?php a_js_call('apostrophe.areaEnableHistoryButton(?)', array('buttonId' => $history_button_id, 'pageId' => $pageid, 'name' => $name, 'url' => url_for("a/history?" . http_build_query(array("id" => $pageid, 'name' => $name))), 'moreUrl' => url_for("a/history?" . http_build_query(array("id" => $pageid, 'name' => $name, 'all' => 1))))) ?>
	</li>
<?php end_slot() ?>
<?php endif ?>

<?php if (!$refresh): ?>

  <div id="a-area-<?php echo "$pageid-$name" ?>" class="a-area a-normal <?php echo isset($options['area-class']) ? $options['area-class'] : "a-area-$name" ?><?php echo (!$infinite) ? ' singleton '.$options['type'] :'' ?> clearfix">
    
  <?php // Area Controls ?>
  <?php if ($editable): ?>
    <?php if ($infinite): ?>

		<ul class="a-ui a-controls a-area-controls clearfix">

		<?php # Slot Controls ?>
			<li>

				<?php $addslot_button_style = sfConfig::get('app_a_addslot_button_style', 'big') ?>
				<?php $slotTypesInfo = aTools::getSlotTypesInfo($options) ?>

				<?php if (count($slotTypesInfo) > 1): ?>
					<?php echo a_js_button(a_get_option($options, 'areaLabel', a_('Add Content')), array('a-add', 'a-add-slot', 'icon', 'big'), 'a-add-slot-'.$pageid.'-'.$name) ?>
					<ul class="a-options a-area-options dropshadow">
		      	<?php include_partial('a/addSlot', array('id' => $page->id, 'name' => $name, 'options' => $options, 'slotTypesInfo' => $slotTypesInfo, )) ?>
					</ul>
					<?php a_js_call('apostrophe.menuToggle(?)', array('button' => '#a-add-slot-'.$pageid.'-'.$name, 'classname' => 'a-options-open', 'overlay' => false)) ?>
				<?php else: ?>
	      	<?php include_partial('a/addSlot', array('id' => $page->id, 'name' => $name, 'options' => $options, 'slotTypesInfo' => $slotTypesInfo, 'singleSlot' => true, 'areaLabel' => a_get_option($options, 'areaLabel', null))) ?>
				<?php endif ?>

			</li>	
			<?php include_slot('a-history-controls') ?>
		</ul>
    <?php endif ?>

  <?php endif ?>

  <?php // End area controls ?>

<?php endif ?>

<?php // On an AJAX refresh we are updating a-slots-$pageid-$name, ?>
<?php // so don't nest another one inside it ?>

<?php if (!$refresh): ?>
  <?php // Wraps all of the slots in the area ?>
  <div id="a-slots-<?php echo "$pageid-$name" ?>" class="a-slots clearfix">
<?php endif ?>

<?php // Loop through all of the slots in the area ?>
<?php $i = 0; foreach ($slots as $permid => $slot): ?>

	<?php if ($infinite): ?>
		<?php if (isset($options['type_options'][$slot->type])): ?>
  	  <?php $slotOptions = $options['type_options'][$slot->type]; ?>
  	<?php else: ?>
  	  <?php $slotOptions = array() ?>
  	<?php endif ?>
  <?php else: ?>
  	<?php $slotOptions = $options ?>
  <?php endif ?>

 <?php slot("a-slot-content-$pageid-$name-$permid") ?>
   <?php a_slot_body($name, $slot->type, $permid, array_merge(array('edit' => $editable, 'preview' => $preview), $slotOptions), array(), $slot->isOpen()) ?>
 <?php end_slot() ?>

	<!-- START SLOT -->
	<div class="a-slot a-normal <?php echo $slot->getEffectiveVariant($slotOptions) ?> <?php echo $slot->type ?><?php echo ($slot->isNew())? ' a-new-slot':'' ?> clearfix" id="a-slot-<?php echo "$pageid-$name-$permid" ?>">
 		
		<?php // Slot Controls ?>
    <?php if ($editable): ?>

	  <?php // Make the slot aware of its permid for simpler JS later ?>
	  <?php a_js_call('$(?).data(?, ?)', "#a-slot-$pageid-$name-$permid", 'a-permid', $permid) ?>

		<ul class="a-ui a-controls a-slot-controls clearfix">		
      <?php if ($infinite): ?>
				<li class="a-move up">
				  <a href="#move-up" class="a-btn icon a-arrow-up no-label" title="<?php echo a_('Move Up') ?>" onclick="return false;"><span class="icon"></span><?php echo a_('Move Up') ?></a>
				</li>
				<li class="a-move down">
				  <a href="#move-down" class="a-btn icon a-arrow-down no-label" title="<?php echo a_('Move Down') ?>" onclick="return false;"><span class="icon"></span><?php echo a_('Move Down') ?></a>
				</li>
      <?php endif ?>

      <?php // Include slot-type-specific controls if the slot has any ?>
     	<?php include_slot("a-slot-controls-$pageid-$name-$permid") ?>

			<?php if (!$infinite): ?>
			  <?php include_slot('a-history-controls') ?>
			<?php endif ?>

      <?php if ($infinite): ?>
			<?php // Tom: Just a quick note about this -- Enabling the delete button for singleton slot works, it just clears out the value for that slot instead of deleting the slot. ?>
        <li>
					<?php $delete_button_style = sfConfig::get('app_a_delete_button_style', 'no-label'); ?>
					<?php $delete_button_id = "a-slot-$pageid-$name-$permid-delete-button" ?>
					<?php echo a_js_button(a_('Delete'), array('icon', 'a-delete', $delete_button_style), $delete_button_id, a_('Delete Slot')) ?>
					<?php a_js_call('apostrophe.areaEnableDeleteSlotButton(?)', array('pageId' => $page->id, 'name' => $name, 'permid' => $permid, 'buttonId' => $delete_button_id, 'confirmPrompt' => a_('Are you sure you want to delete this slot?'), "url" => url_for("a/deleteSlot?" .http_build_query(array(
            "id" => $page->id,
            "name" => $name,
            "permid" => $permid))))) ?>
        </li>			
      <?php endif ?>
		</ul>
		
  <?php endif ?>
	<?php // End Slot Controls ?>		
				
    <?php // Wraps the actual content - edit and normal views for this individual slot ?>
  	<div class="a-slot-content clearfix" id="a-slot-content-<?php echo "$pageid-$name-$permid" ?>">
      <?php // Now we can include the slot ?>
      <?php include_slot("a-slot-content-$pageid-$name-$permid") ?>
  	</div>
	</div>

<?php $i++; endforeach ?>

<?php if (!$refresh): ?>
  </div>  <?php // .a-slots ?>
</div> <?php // .a-area ?>
<?php endif ?>

<?php if ($editable): ?>

	<?php if ($preview): ?><?php // Previewing History for Area ?>
	<?php a_js_call("$('.a-history-preview-notice').fadeIn(); $('body').addClass('history-preview');") ?>
	<?php endif ?>

	<?php if (!$infinite): ?><?php // Singleton Slots ?>
		<?php a_js_call('apostrophe.areaSingletonSlot(?)', array('pageId' => $pageid, 'slotName' => $name)) ?>
	<?php endif ?>

	<?php if ($infinite): ?><?php // Normal Areas ?>
		<?php a_js_call('apostrophe.areaHighliteNewSlot(?)', array('pageId' => $pageid, 'slotName' => $name)) ?>
		<?php a_js_call('apostrophe.areaUpdateMoveButtons(?, ?, ?)', url_for('a/moveSlot'), $pageid, $name) ?>
		<?php a_js_call('apostrophe.menuToggle(?)', array('button' => '#a-add-slot-'.$pageid.'-'.$name, 'classname' => 'a-options-open', 'overlay' => false)) ?>	
	<?php endif ?>

<?php endif ?>