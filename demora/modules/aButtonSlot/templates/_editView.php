<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
?>

<?php use_helper('a') ?>

<div class="a-form-row a-hidden">
	<?php echo $form->renderHiddenFields() ?>
</div>

<div class="a-form-row url">
	<?php echo $form['url']->renderLabel(__('URL:', array(), 'apostrophe')) ?>
	<div class="a-form-field">
		<?php echo $form['url']->render() ?>
		<?php echo $form['url']->renderHelp() ?>
	</div>
	<div class="a-form-error"><?php echo $form['url']->renderError() ?></div>
</div>

<div class="a-form-row title">
	<?php echo $form['title']->renderLabel(__('Title:', array(), 'apostrophe')) ?>
	<div class="a-form-field">
		<?php echo $form['title']->render() ?>
		<?php echo $form['title']->renderHelp() ?>
	</div>
	<div class="a-form-error"><?php echo $form['title']->renderError() ?></div>
</div>
