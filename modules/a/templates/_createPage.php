<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
?>
<?php use_helper('I18N') ?>

<a href="#" class="a-btn icon a-add" id="a-create-page-button" onclick="return false;"><?php echo __("Add Page", null, 'apostrophe') ?></a>

<form method="POST" action="<?php echo url_for('a/create') ?>" id="a-create-page-form" class="a-create-page-form dropshadow">
	<?php echo $form->renderHiddenFields() ?>
	<?php echo $form['parent']->render(array('id' => 'a-create-page-parent', )) ?>
	<?php echo $form['title']->render(array('id' => 'a-create-page-title', )) ?>
	<?php echo $form['engine']->renderRow(array('id' => 'a-create-page-title', )) ?>
	<?php echo $form['template']->renderRow(array('id' => 'a-create-page-title', )) ?>
	<ul class="a-ui a-controls">
	  <li><input type="submit" class="a-btn a-submit" value="<?php echo __('Create Page', null, 'apostrophe') ?>" /></li>
	  <li><a href="#" onclick="return false;" class="a-btn a-cancel"><?php echo __("Cancel", null, 'apostrophe') ?></a></li>
	</ul>
</form>

<script type="text/javascript" charset="utf-8">
	$(document).ready(function() {
		aInputSelfLabel('#a-create-page-title', <?php echo json_encode(__('Page Title', null, 'apostrophe')) ?>);
		aMenuToggle($('#a-create-page-button'), $('#a-create-page-button').parent(), '', true);
		$('#a-create-page-button').click(function(){
			$('#a-create-page-title').focus();
		});
	});
</script>
