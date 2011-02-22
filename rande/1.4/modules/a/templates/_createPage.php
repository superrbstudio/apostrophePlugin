<?php use_helper('I18N') ?>
<?php echo jq_link_to_function(__("Add Page", null, 'apostrophe'), 
	'$("#a-breadcrumb-create-childpage-form").fadeIn(250, function(){ $(".a-breadcrumb-create-childpage-title").focus(); }); 
	$("#a-breadcrumb-create-childpage-button").hide(); 
	$("#a-breadcrumb-create-childpage-button").prev().hide();
	$(".a-breadcrumb-create-childpage-controls a.a-cancel").parent().show();', 
	array(
		'id' => 'a-breadcrumb-create-childpage-button', 
		'class' => 'a-btn icon a-add', 
)) ?>

<form method="POST" action="<?php echo url_for('a/create') ?>" id="a-breadcrumb-create-childpage-form" class="a-breadcrumb-form add">

	<?php $form = new aCreateForm($page) ?>
	<?php echo $form->renderHiddenFields() ?>
	<?php echo $form['parent']->render(array('id' => 'a-breadcrumb-create-parent', )) ?>
	<?php echo $form['title']->render(array('id' => 'a-breadcrumb-create-title', )) ?>

	<ul class="a-form-controls a-breadcrumb-create-childpage-controls">
	  <li>
			<input type="submit" class="a-submit" value="<?php echo __('Create Page', null, 'apostrophe') ?>" />			
		</li>
	  <li>
			<?php echo jq_link_to_function(__("Cancel", null, 'apostrophe'), 
				'$("#a-breadcrumb-create-childpage-form").hide(); 
				$("#a-breadcrumb-create-childpage-button").fadeIn(); 
				$("#a-breadcrumb-create-childpage-button").prev(".a-i").fadeIn();', 
				array(
					'class' => 'a-btn a-cancel', 
			)) ?>
		</li>
	</ul>

	<script type="text/javascript" charset="utf-8">
		aInputSelfLabel('#a-breadcrumb-create-title', <?php echo json_encode(__('Page Title', null, 'apostrophe')) ?>);
	</script>

</form>

<?php // Disable Add Page Button if we have reached our max depth, max peers, or if it is an engine page ?>
<?php $maxPageLevels = (sfConfig::get('app_a_max_page_levels'))? sfConfig::get('app_a_max_page_levels') : 0; ?>
<?php $maxChildPages = (sfConfig::get('app_a_max_children_per_page'))? sfConfig::get('app_a_max_children_per_page') : 0; ?>
<?php if (($maxPageLevels && ($page->getLevel() == $maxPageLevels)) || ($maxChildPages && (count($page->getChildren()) == $maxChildPages)) || strlen($page->getEngine())): ?>
	<script type="text/javascript" charset="utf-8">
		$(document).ready(function() {

			var renameButton = $('#a-breadcrumb-create-childpage-button');

			renameButton.addClass('a-disabled')
			.after('<span id="a-breadcrumb-create-childpage-max-message"><?php echo (sfConfig::get("app_a_max_page_limit_message"))? sfConfig::get("app_a_max_page_limit_message") : "Cannot create pages here." ?></span>')
			.mousedown(function(){
				var message = $('#a-breadcrumb-create-childpage-max-message');
				message.show();
				message.oneTime(1050, function(){
					message.fadeOut('slow');
				});
			}).text(<?php echo json_encode('Add Page Disabled') ?>);
			
			aUI('#a-breadcrumb');
		});
	</script>
<?php endif ?>