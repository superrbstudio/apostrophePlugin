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
	<script type="text/javascript">
	$(document).ready(function(){
		var editBtn = $('#a-slot-edit-<?php echo "$pageid-$name-$permid" ?>');
		var editSlot = $('#a-slot-<?php echo "$pageid-$name-$permid" ?>');
		editBtn.click(function(event){
			$(this).parent().addClass('editing-now');
			$(editSlot).children('.a-slot-content').children('.a-slot-content-container').hide(); // Hide content
			$(editSlot).children('.a-slot-content').children('.a-slot-content-container').hide(); // Hide content
			$(editSlot).children('.a-slot-content').children('.a-slot-form').fadeIn();							// Show form
			$(editSlot).children('.a-controls-item variant').hide();
			aUI($(this).parents('.a-slot').attr('id'));
			return false;
		});
	})
	</script>
	</li>
	
<?php if ($controlsSlot): ?>
	<?php end_slot() ?>
<?php endif ?>


