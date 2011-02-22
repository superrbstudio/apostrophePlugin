<?php use_helper('a', 'jQuery', 'I18N') ?>

<?php // We don't replace the area controls on an AJAX refresh, just the contents ?>

<?php if ($editable): ?>

	<?php slot('a-cancel') ?>
		<?php if (0): ?>	
			<li class="a-controls-item cancel">
				<a href="#" class="a-btn a-cancel a-cancel-area" title="<?php echo __('Cancel', null, 'apostrophe') ?>"><?php echo __('Cancel', null, 'apostrophe') ?></a>					
			</li>
		<?php endif ?>
	<?php end_slot() ?>

	<?php slot('a-history-controls') ?>
	<li class="a-controls-item history">
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

<?php endif ?>

<?php if (!$refresh): ?>
  <?php // Wraps the whole thing, including the area controls ?>
  <?php // Existing CSS code often targets the old area IDs, which were a-area-$name. Since ?>
  <?php // we now have multiple areas with the same name coming from separate virtual pages, ?>
  <?php // we need the page ID in the DOM ID, which means it can't be used in CSS. Instead we ?>
  <?php // provide a-area-$name as a class, which should make it easy to change your CSS rules. ?>
  <?php // It is also possible to explicitly pass an area-class option to an area (or singleton slot). ?>
  <div id="a-area-<?php echo "$pageid-$name" ?>" class="a-area <?php echo isset($options['area-class']) ? $options['area-class'] : "a-area-$name" ?>">
    
  <?php // The area controls ?>
  <?php if ($editable): ?>
    <?php if ($infinite): ?>

		<ul class="a-controls a-area-controls">

		<?php # Slot Controls ?>
			<li class="a-controls-item slots">
				<?php $addslot_button_style = sfConfig::get('app_a_addslot_button_style', "big"); ?>				
				<?php echo link_to_function(__('Add Content', null, 'apostrophe'), "", array('class' => 'a-btn icon a-add a-add-slot '.$addslot_button_style, 'id' => 'a-add-slot-'.$pageid.'-'.$name, )) ?>
				<ul class="a-options a-area-options dropshadow">
	      	<?php include_partial('a/addSlot', array('id' => $page->id, 'name' => $name, 'options' => $options)) ?>
				</ul>
			</li>	
			<?php include_slot('a-history-controls') ?>
			<?php include_slot('a-cancel') ?>
		</ul>
    <?php endif ?>

  <?php endif ?>

  <?php // End area controls ?>

<?php endif ?>

<?php $i = 0 ?>

<?php // On an AJAX refresh we are updating a-slots-$pageid-$name, ?>
<?php // so don't nest another one inside it ?>

<?php if (!$refresh): ?>
  <?php // Wraps all of the slots in the area ?>
  <div id="a-slots-<?php echo "$pageid-$name" ?>" class="a-slots">
<?php endif ?>

<?php // Loop through all of the slots in the area ?>
<?php foreach ($slots as $permid => $slot): ?>
   <?php if ($infinite): ?>
  	<?php if (isset($options['type_options'][$slot->type])): ?>
  	  <?php $slotOptions = $options['type_options'][$slot->type]; ?>
  	<?php else: ?>
  	  <?php $slotOptions = array() ?>
  	<?php endif ?>
  <?php else: ?>
  	<?php $slotOptions = $options ?>
  <?php endif ?>
  <?php $outlineEditableClass = "" ?>
  <?php if ($editable && ((isset($slotOptions['outline_editable']) && $slotOptions['outline_editable']) || $slot->isOutlineEditable())): ?>
    <?php $outlineEditableClass = "a-slot-is-editable" ?>
  <?php endif ?>
 <?php // Generate the content of the CMS slot early and capture it to a ?>
 <?php // Symfony slot so we can insert it at an appropriate point... and we ?>
 <?php // will also insert its slot-specific controls via a separate ?>
 <?php // a-slot-controls-$pageid-$name-$permid slot that the slot implementation ?>
 <?php // provides for us ?>

 <?php slot("a-slot-content-$pageid-$name-$permid") ?>
   <?php a_slot_body($name, $slot->type, $permid, array_merge(array('edit' => $editable, 'preview' => $preview), $slotOptions), array(), $slot->isOpen()) ?>
 <?php end_slot() ?>

 <?php // Wraps an individual slot, with its controls ?>
	<div class="a-slot <?php echo $slot->getEffectiveVariant($slotOptions) ?> <?php echo $slot->type ?> <?php echo $outlineEditableClass ?>" id="a-slot-<?php echo "$pageid-$name-$permid" ?>">
    <?php // John shouldn't we suppress this entirely if !$editable? ?>
    <?php // Controls for that individual slot ?>
    <?php if ($editable): ?>
		<ul class="a-controls a-slot-controls">		
      <?php if ($infinite): ?>
          <?php if ($i > 0): ?>
						<li class="move-up">
            <?php echo jq_link_to_remote(__('Move', null, 'apostrophe'), array(
                "url" => "a/moveSlot?" .http_build_query(array(
									"id" => $page->id,
									"name" => $name,
									"up" => 1,
									"permid" => $permid)),
									"update" => "a-slots-$pageid-$name",
									'complete' => 'aUI()'), 
									array(
										'class' => 'a-btn icon a-arrow-up', 
										'title' => __('Move Up', null, 'apostrophe'), 
						)) ?>
						</li>
          <?php endif ?>

          <?php if (($i + 1) < count($slots)): ?>
						<li class="move-down">
            <?php echo jq_link_to_remote(__('Move', null, 'apostrophe'), array(
                "url" => "a/moveSlot?" .http_build_query(array(
									"id" => $page->id,
									"name" => $name,
									"permid" => $permid)),
									"update" => "a-slots-$pageid-$name",
									'complete' => 'aUI()'), 
									array(
										'class' => 'a-btn icon a-arrow-down', 
										'title' => __('Move Down', null, 'apostrophe'), 
						)) ?>
            </li>
        <?php endif ?>
      <?php endif ?>

      <?php // Include slot-type-specific controls if the slot has any ?>
     	<?php include_slot("a-slot-controls-$pageid-$name-$permid") ?>

			<?php if (!$infinite): ?>
			  <?php include_slot('a-history-controls') ?>
				<?php include_slot('a-cancel') ?>
			<?php endif ?>

      <?php if ($infinite): ?>
        <li class="delete">
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
	<?php // End controls for this individual slot ?>		
				
    <?php // Wraps the actual content - edit and normal views for this individual slot ?>
  	<div class="a-slot-content" id="a-slot-content-<?php echo "$pageid-$name-$permid" ?>">
      <?php // Now we can include the slot ?>
      <?php include_slot("a-slot-content-$pageid-$name-$permid") ?>
  	</div>
	</div>
<?php $i++; endforeach ?>

<?php if (!$refresh): ?>
  </div>  <?php // Closes the div wrapping all of the slots ?>
</div> <?php // Closes the div wrapping all of the slots AND the area controls ?>
<?php endif ?>
<!-- END SLOT -->



<script type="text/javascript" charset="utf-8">
	$(document).ready(function() {

		<?php if ($editable): ?>

			<?php if ($infinite): ?>
				<?php // Add Slot Dropdown ?>
				$('#a-add-slot-<?php echo $pageid.'-'.$name ?>').unbind('click').click(function(){ 
					var area = $(this).parents('.a-area');
					area.toggleClass('add-slot-now');
					$(document).click(function(e){
						var target = e.target, // e.target grabs the node that triggered the event.
						$target = $(target);  // wraps the node in a jQuery object
							if (target.id !== 'a-add-slot-<?php echo $pageid.'-'.$name ?>') {
								area.removeClass('add-slot-now');
							}
					});
				});			
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

		<?php endif ?>

		<?php if ($preview): ?>
			<?php // Previewing History for Area ?>
			$('.a-history-preview-notice').fadeIn();
			$('body').addClass('history-preview');
		<?php endif ?>
		
	});
</script>