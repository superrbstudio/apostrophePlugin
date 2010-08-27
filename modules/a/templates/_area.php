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
<?php use_helper('a', 'jQuery', 'I18N') ?>

<?php slot('a-history-controls') ?>
	<li>
	  <?php $moreAjax = "jQuery.ajax({type:'POST',dataType:'html',success:function(data, textStatus){jQuery('#a-history-items-$pageid-$name').html(data);},url:'/admin/a/history/id/".$page->id."/name/$name/all/1'}); return false;"; ?>
		<?php $history_button_style = sfConfig::get('app_a_history_button_style', "no-label big"); ?>
		<?php echo jq_link_to_remote(__("History", null, 'apostrophe'), array(
	      "url" => "a/history?" . http_build_query(array("id" => $page->id, "name" => $name)),
				'before' => '$(".a-history-browser .a-history-items").attr("id","a-history-items-'.$pageid.'-'.$name.'");
										 $(".a-history-browser .a-history-items").attr("rel","a-area-'.$pageid.'-'.$name.'");
	                   $(".a-history-browser .a-history-browser-view-more").attr("onClick", "'.$moreAjax.'").hide();
										 $(".a-history-browser .a-history-browser-view-more .spinner").hide();',
	      "update" => "a-history-items-$pageid-$name"), 
				array(
					'title' => 'Area History', 
					'class' => 'a-btn icon a-history-btn '.$history_button_style, 
		)); ?>					
	</li>
<?php end_slot() ?>

<?php if (!$refresh): ?>

  <div id="a-area-<?php echo "$pageid-$name" ?>" class="a-area <?php echo isset($options['area-class']) ? $options['area-class'] : "a-area-$name" ?> clearfix">
    
  <?php // Area Controls ?>
  <?php if ($editable): ?>
    <?php if ($infinite): ?>

		<ul class="a-ui a-controls a-area-controls clearfix">

		<?php # Slot Controls ?>
			<li>
				<?php $addslot_button_style = sfConfig::get('app_a_addslot_button_style', "big"); ?>				
				<?php echo link_to_function(__('Add Content', null, 'apostrophe'), "", array('class' => 'a-btn icon a-add a-add-slot '.$addslot_button_style, 'id' => 'a-add-slot-'.$pageid.'-'.$name, )) ?>
				<ul class="a-options a-area-options dropshadow">
	      	<?php include_partial('a/addSlot', array('id' => $page->id, 'name' => $name, 'options' => $options)) ?>
				</ul>
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
	<div class="a-slot <?php echo $slot->getEffectiveVariant($slotOptions) ?> <?php echo $slot->type ?><?php echo ($slot->isNew())? ' a-new-slot':'' ?> clearfix" id="a-slot-<?php echo "$pageid-$name-$permid" ?>">
		<?php // Slot Controls ?>
    <?php if ($editable): ?>
		<ul class="a-ui a-controls a-slot-controls clearfix">		
      <?php if ($infinite): ?>
          <?php if ($i > 0): ?>
						<li>
            <?php echo jq_link_to_remote(__('Move', null, 'apostrophe'), array(
                "url" => "a/moveSlot?" .http_build_query(array(
									"id" => $page->id,
									"name" => $name,
									"up" => 1,
									"permid" => $permid)),
									"update" => "a-slots-$pageid-$name",
									'complete' => 'aUI()'), 
									array(
										'class' => 'a-btn icon a-arrow-up no-label', 
										'title' => __('Move Up', null, 'apostrophe'), 
						)) ?>
						</li>
          <?php endif ?>
          <?php if (($i + 1) < count($slots)): ?>
						<li>
            <?php echo jq_link_to_remote(__('Move', null, 'apostrophe'), array(
                "url" => "a/moveSlot?" .http_build_query(array(
									"id" => $page->id,
									"name" => $name,
									"permid" => $permid)),
									"update" => "a-slots-$pageid-$name",
									'complete' => 'aUI()'), 
									array(
										'class' => 'a-btn icon a-arrow-down no-label', 
										'title' => __('Move Down', null, 'apostrophe'), 
						)) ?>
            </li>
        <?php endif ?>
      <?php endif ?>

      <?php // Include slot-type-specific controls if the slot has any ?>
     	<?php include_slot("a-slot-controls-$pageid-$name-$permid") ?>

			<?php if (!$infinite): ?>
			  <?php include_slot('a-history-controls') ?>
			<?php endif ?>

      <?php if ($infinite): ?>
			<?php // Tom: Just a quick note about this -- Enabling the delete button for singleton slot works, it just clears out the value for that slot instead of deleting the slot. ?>
        <li>
					<?php $delete_button_style = sfConfig::get('app_a_delete_button_style', "no-label"); ?>
          <?php echo jq_link_to_remote(__('Delete', null, 'apostrophe'), array(
            "url" => "a/deleteSlot?" .http_build_query(array(
              "id" => $page->id,
              "name" => $name,
              "permid" => $permid)),
              "update" => "a-slots-$pageid-$name",
							'before' => '$(this).parents(".a-slot").fadeOut();', 
							'complete' => 'aUI()'), 
              array(
                'class' => 'a-btn icon a-delete '.$delete_button_style, 
                'title' => __('Delete Slot', null, 'apostrophe'),
								'confirm' => __('Are you sure you want to delete this slot?', null, 'apostrophe'), )) ?>
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
  </div>  <?php // Closes the div wrapping all of the slots ?>
</div> <?php // Closes the div wrapping all of the slots AND the area controls ?>
<?php endif ?>

<?php if ($editable): ?>
<script type="text/javascript" charset="utf-8">
	$(document).ready(function() {

			<?php if ($infinite): ?>
				aMenuToggle('#a-add-slot-<?php echo $pageid.'-'.$name ?>', $('#a-add-slot-<?php echo $pageid.'-'.$name ?>').parent(), 'a-options-open', false);

				var newSlot = $('#a-area-<?php echo "$pageid-$name" ?>').find('.a-new-slot');
				if (newSlot.length) {
					newSlot.effect("highlight", {}, 1000);
					$('#a-add-slot-<?php echo $pageid.'-'.$name ?>').parent().trigger('toggleClosed');
				};
			<?php endif ?>

			<?php if (!$infinite): ?>
				<?php // Singleton Slot Controls ?>
				$('#a-area-<?php echo "$pageid-$name" ?>').addClass('singleton <?php echo $options['type'] ?>');
				if ($('#a-area-<?php echo "$pageid-$name" ?>.singleton .a-slot-controls-moved').length) {	<?php // This is far from optimal. We are still using jQuery to "toss up" the slot controls if it's a Singleton slot. ?>
					$('#a-area-<?php echo "$pageid-$name" ?>.singleton .a-slot-controls').remove(); <?php // If the slot controls have already been pushed up, remove any new instances from an Ajax return ?>
				}
				else
				{
					$('#a-area-<?php echo "$pageid-$name" ?>.singleton .a-slot-controls').prependTo($('#a-area-<?php echo "$pageid-$name" ?>')).addClass('a-area-controls a-slot-controls-moved').removeClass('a-slot-controls');	<?php // Move up the slot controls and give them some class names. ?>
					$('ul.a-slot-controls-moved a.a-btn.a-history-btn').removeClass('big'); // Singleton Slots can't have big history buttons, Sorry Charlie!
				};				
			<?php endif ?>

		<?php if ($preview): ?>
			<?php // Previewing History for Area ?>
			$('.a-history-preview-notice').fadeIn();
			$('body').addClass('history-preview');
		<?php endif ?>
		
	});
</script>
<?php endif ?>