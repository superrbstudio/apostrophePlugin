<?php use_helper('I18N') ?>
<?php if (!isset($controlsSlot)): ?>
  <?php $controlsSlot = true ?>
<?php endif ?>

<?php if ($controlsSlot): ?>
	<?php slot("a-slot-controls-$pageid-$name-$permid") ?>
<?php endif ?>

<li class="a-controls-item edit">
<?php echo jq_link_to_function(isset($label) ? __($label, null, 'apostrophe') : __("edit", null, 'apostrophe'), "", 
			array(
				'id' => "a-slot-edit-$pageid-$name-$permid",
				'class' => isset($class) ? $class : 'a-btn icon a-edit', 
				'title' => isset($title) ? $title : __('Edit', null, 'apostrophe'), 
)) ?>

<script type="text/javascript" charset="utf-8">
<?php // TODO: Rewrite this as a button class scoped to ALL edit buttons so there's only a single instance of this Javascript ?>
	$(document).ready(function(){
		var editBtn = $('#a-slot-edit-<?php echo "$pageid-$name-$permid" ?>');
		var editSlot = $('#a-slot-<?php echo "$pageid-$name-$permid" ?>');
		editBtn.click(function(event){
			editBtn.parents('.a-slot, .a-area').addClass('editing-now'); <?php // Apply a class to the Area and Slot Being Edited ?>
			editSlot.children('.a-slot-content').children('.a-slot-content-container').hide(); <?php // Hide the Content Container ?>
			editSlot.children('.a-slot-content').children('.a-slot-form').fadeIn(); <?php // Fade In the Edit Form ?>
			editSlot.children('.a-controls-item variant').hide(); <?php // Hide the Variant Options ?>
			aUI(editBtn.parents('.a-slot').attr('id')); <?php // Refresh the UI scoped to this Slot ?>
			return false;
		});
	});
</script>
</li>
	
<?php if ($controlsSlot): ?>
	<?php end_slot() ?>
<?php endif ?>


