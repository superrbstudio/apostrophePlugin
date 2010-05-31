<?php use_helper('I18N') ?>

<a href="#" class="a-btn icon a-add" id="a-create-page-button" onclick="return false;"><?php echo __("Add Page", null, 'apostrophe') ?></a>

<form method="POST" action="<?php echo url_for('a/create') ?>" id="a-create-page-form" class="a-create-page-form dropshadow">

	<?php echo $form->renderHiddenFields() ?>
	<?php echo $form['parent']->render(array('id' => 'a-create-page-parent', )) ?>
	<?php echo $form['title']->render(array('id' => 'a-create-page-title', )) ?>
	<?php echo $form['engine']->renderRow(array('id' => 'a-create-page-title', )) ?>
	<?php echo $form['template']->renderRow(array('id' => 'a-create-page-title', )) ?>

	<ul class="a-controls">
	  <li>
			<input type="submit" class="a-submit" value="<?php echo __('Create Page', null, 'apostrophe') ?>" />			
		</li>
	  <li>
			<a href="#" onclick="return false;" class="a-btn a-cancel"><?php echo __("Cancel", null, 'apostrophe') ?></a>
		</li>
	</ul>

	<script type="text/javascript" charset="utf-8">
		aInputSelfLabel('#a-create-page-title', <?php echo json_encode(__('Page Title', null, 'apostrophe')) ?>);
	</script>

</form>


<script type="text/javascript" charset="utf-8">
	$(document).ready(function() {

		aMenuToggle('#a-create-page-button', '#a-create-page-form', '', true);
		
		$('#a-create-page-button').click(function(){
			$('#a-create-page-title').focus();
		});
		
		<?php // Disable Add Page Button if we have reached our max depth, max peers, or if it is an engine page ?>
		<?php // Your Site Tree can only get so deep ?>
		<?php $maxPageLevels = (sfConfig::get('app_a_max_page_levels'))? sfConfig::get('app_a_max_page_levels') : 0; ?>
		<?php // Your Site Tree can only get so wide ?>
		<?php $maxChildPages = (sfConfig::get('app_a_max_children_per_page'))? sfConfig::get('app_a_max_children_per_page') : 0; ?>

		<?php if (($maxPageLevels && ($page->getLevel() == $maxPageLevels)) || ($maxChildPages && (count($page->getChildren()) == $maxChildPages)) || strlen($page->getEngine())): ?>

				var renameButton = $('#a-create-page-button');
				renameButton.addClass('a-disabled')
					.after('<span id="a-create-page-childpage-max-message"><?php echo (sfConfig::get("app_a_max_page_limit_message")) ? sfConfig::get("app_a_max_page_limit_message") : __('Cannot create pages here.', null, 'apostrophe') ?></span>')
					.mousedown(function(){
						var message = $('#a-create-page-childpage-max-message');
						message.show();
						message.oneTime(1050, function(){
							message.fadeOut('slow');
						});
					}).text(<?php echo json_encode(__('Add Page Disabled', null, 'apostrophe')) ?>);

		<?php endif ?>
		
	});
</script>
