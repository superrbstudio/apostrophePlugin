<?php use_helper('a', 'jQuery') ?>

<?php // We don't replace the area controls on an AJAX refresh, ?>
<?php // just the contents ?>
<?php if ($editable): ?>

<?php slot('a-cancel') ?>
<!-- .a-controls.area Cancel Button -->
<li class="a-controls-item cancel">
	<a href="#" class="a-btn a-cancel" title="Cancel">Cancel</a>					
</li>
<?php end_slot() ?>

<?php slot('a-history-controls') // START - PK-HISTORY SLOT ====================================  ?>
<!-- .a-controls.a-area-controls History Module -->
<li class="a-controls-item history">
  <?php $moreAjax = "jQuery.ajax({type:'POST',dataType:'html',success:function(data, textStatus){jQuery('#a-history-items-$name').html(data);},url:'/admin/a/history/id/".$page->id."/name/$name/all/1'}); return false;"; ?>
	<?php echo jq_link_to_remote("History", array(
      "url" => "a/history?" . http_build_query(array("id" => $page->id, "name" => $name)),
			'before' => '$(".a-history-browser .a-history-items").attr("id","a-history-items-'.$name.'");
									 $(".a-history-browser .a-history-items").attr("rel","a-area-'.$name.'");
                   $(".a-history-browser .a-history-browser-view-more").attr("onClick", "'.$moreAjax.'").hide();
									 $(".a-history-browser .a-history-browser-view-more .spinner").hide();',
      "update" => "a-history-items-$name"), 
			array(
				'class' => 'a-btn icon a-history', 
	)); ?>
	<ul class="a-history-options">
		<li><a href="#" class="a-btn icon a-history-revert">Save as Current Revision</a></li>
	</ul>
</li>
<?php end_slot() // END - PK-HISTORY SLOT ==================================== ?>


<?php endif ?>

<?php if (!$refresh): ?>
  <?php // Wraps the whole thing, including the area controls ?>
  <div id="a-area-<?php echo $name ?>" class="a-area">
    
  <?php // The area controls ?>
  <?php if ($editable): ?>
    <?php if ($infinite): ?>
		<ul class="a-controls a-area-controls">
			<!-- .a-controls.a-area-controls Add Slot Module -->
			<li class="a-controls-item slot">
				<?php echo link_to_function("Add Slot", "", array('class' => 'a-btn icon a-add slot', )) ?>
				<ul class="a-area-options slot">
	      	<?php include_partial('a/addSlot', array('id' => $page->id, 'name' => $name, 'options' => $options)) ?>
				</ul>
			</li>	
			
			<?php include_slot('a-history-controls') ?>
			<?php include_slot('a-cancel') ?>
			
		</ul>
    <?php endif ?>


  <?php endif ?>
	<?php if (!$infinite): ?>
		<script type="text/javascript" charset="utf-8">
			$(document).ready(function(){
				$('#a-area-<?php echo $name ?>').addClass('singleton');
				$('#a-area-<?php echo $name ?>.singleton .a-slot-controls').prependTo($('#a-area-<?php echo $name ?>')).addClass('a-area-controls').removeClass('a-slot-controls');
			});
		</script>
	<?php endif ?>

  <?php // End area controls ?>

<?php endif ?>

<?php if ($preview): ?>
<script type="text/javascript" charset="utf-8">
	$(document).ready(function(){
		$('.a-history-preview-notice').fadeIn();
	})
</script>
<?php endif ?>

<?php $i = 0 ?>
<?php // On an AJAX refresh we are updating a-slots-$name, ?>
<?php // so don't nest another one inside it ?>
<?php if (!$refresh): ?>
  <?php // Wraps all of the slots in the area ?>
  <div id="a-slots-<?php echo $name ?>" class="a-slots">
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
 <?php // a-slot-controls-$name-$permid slot that the slot implementation ?>
 <?php // provides for us ?>

 <?php slot("a-slot-content-$name-$permid") ?>
   <?php a_slot_body($name, $slot->type, $permid, array_merge(array('edit' => $editable, 'preview' => $preview), $slotOptions), array(), $slot->isOpen()) ?>
 <?php end_slot() ?>

 <?php // Wraps an individual slot, with its controls ?>
	<div class="a-slot <?php echo $slot->getEffectiveVariant() ?> <?php echo $slot->type ?> <?php echo $outlineEditableClass ?>" id="a-slot-<?php echo $name ?>-<?php echo $permid ?>">
    <?php // John shouldn't we suppress this entirely if !$editable? ?>
    <?php // Controls for that individual slot ?>
    <?php if ($editable): ?>
		<ul class="a-controls a-slot-controls">		
      <?php if ($infinite): ?>
						<!-- <li class="drag-handle"><a href="#" class="a-btn icon drag" title="Drag to Re-Order Slot">Drag to Re-Order Slot</a></li> -->
						<!-- <li class="slot-history"><a href="#" class="a-btn icon history">Slot History</a></li> -->
          <?php if ($i > 0): ?>
						<li class="move-up">
            <?php echo jq_link_to_remote("Move", array(
                "url" => "a/moveSlot?" .http_build_query(array(
									"id" => $page->id,
									"name" => $name,
									"up" => 1,
									"permid" => $permid)),
									"update" => "a-slots-$name",
									'complete' => 'aUI()'), 
									array(
										'class' => 'a-btn icon a-arrow-up', 
										'title' => 'Move Up', 
						)) ?>
						</li>
          <?php endif ?>

          <?php if (($i + 1) < count($slots)): ?>
						<li class="move-down">
            <?php echo jq_link_to_remote("Move", array(
                "url" => "a/moveSlot?" .http_build_query(array(
									"id" => $page->id,
									"name" => $name,
									"permid" => $permid)),
									"update" => "a-slots-$name",
									'complete' => 'aUI()'), 
									array(
										'class' => 'a-btn icon a-arrow-down', 
										'title' => 'Move Down', 
						)) ?>
            </li>
        <?php endif ?>
      <?php endif ?>

      <?php // Include slot-type-specific controls if the ?>
      <?php // slot has any ?>
      <?php include_slot("a-slot-controls-$name-$permid") ?>

			<?php if (!$infinite): ?>
			  <?php include_slot('a-history-controls') ?>
				<?php include_slot('a-cancel') ?>
			<?php endif ?>

      <?php if ($infinite): ?>
        <li class="delete">
          <?php echo jq_link_to_remote("Delete", array(
            "url" => "a/deleteSlot?" .http_build_query(array(
              "id" => $page->id,
              "name" => $name,
              "permid" => $permid)),
              "update" => "a-slots-$name",
							'before' => '$(this).parents(".a-slot").fadeOut();', 
							'complete' => 'aUI()'), 
              array(
                'class' => 'a-btn icon a-delete', 
                'title' => 'Delete Slot',
								'confirm' => 'Are you sure you want to delete this slot?', )) ?>
        </li>			
      <?php endif ?>
		</ul>
		
  <?php endif ?>

		<?php // End controls for this individual slot ?>		
		
    <?php if ($editable): ?>
		<!-- <ul class="a-messages a-slot-messages">
			<li class="background"></li>
			<li><span class="message">Double Click to Edit</span></li>
		</ul> -->
		<?php endif ?>
		
    <?php // Wraps the actual content - edit and normal views - ?>
    <?php // for this individual slot ?>
  	<div class="a-slot-content" id="a-slot-content-<?php echo $name ?>-<?php echo $permid?>">
      <?php // Now we can include the slot ?>
      <?php include_slot("a-slot-content-$name-$permid") ?>
  	</div>
	</div>
<?php $i++; endforeach ?>

<?php if (!$refresh): ?>
  <?php // Closes the div wrapping all of the slots ?>
  </div>
<?php // Closes the div wrapping all of the slots AND the area controls ?>
</div>
<?php endif ?>
<!-- END SLOT -->