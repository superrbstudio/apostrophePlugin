<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
  $constraints = isset($constraints) ? $sf_data->getRaw('constraints') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
  $itemIds = isset($itemIds) ? $sf_data->getRaw('itemIds') : null;
?>

<h4 class="a-slot-edit-title"><?php echo a_('Smart Slideshow Slot') ?></h4>

<div class='a-form-row a-hidden'>
	<?php echo $form->renderHiddenFields() ?>
</div>

<div class="a-form-row count">
	<?php echo $form['count']->renderLabel(a_('Images')) ?>
	<div class="a-form-field">
		<?php echo $form['count']->render() ?>
		<div class="a-help"><?php echo $form['count']->renderHelp() ?></div>
	</div>
	<div class="a-form-error"><?php echo $form['count']->renderError() ?></div>
</div>

<div class="a-form-row categories">
	<?php echo $form['categories_list']->renderLabel(__('Category', array(), 'apostrophe')) ?>
	<div class="a-form-field">
		<?php echo $form['categories_list']->render() ?>
		<div class="a-help"><?php echo $form['categories_list']->renderHelp() ?></div>
	</div>
	<div class="a-form-error"><?php echo $form['categories_list']->renderError() ?></div>
</div>

<div class="a-form-row tags">
	<?php echo $form['tags_list']->renderLabel(__('Tags', array(), 'apostrophe')) ?>
	<div class="a-form-field">
		<?php echo $form['tags_list']->render() ?>
		<div class="a-help"><?php echo $form['tags_list']->renderHelp() ?></div>
	</div>
	<div class="a-form-error"><?php echo $form['tags_list']->renderError() ?></div>
</div>

<script type="text/javascript" charset="utf-8" src="/sfDoctrineActAsTaggablePlugin/js/pkTagahead.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    pkTagahead(<?php echo json_encode(a_url('taggableComplete', 'complete')) ?>);
    aMultipleSelect('#a-<?php echo $form->getName() ?>', { 'choose-one': 'Add Categories' });
		$('#a-<?php echo $form->getName() ?>').addClass('a-options dropshadow');			
  });
</script>

