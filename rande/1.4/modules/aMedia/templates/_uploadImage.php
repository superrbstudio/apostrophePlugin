<?php use_helper('I18N') ?>
<?php $previewable = aValidatorFilePersistent::previewAvailable($form['file']->getValue()) ?>
<?php $errors = $form['file']->hasError() ?>

<div class="a-form-row newfile <?php echo(($first || $previewable || $errors) ? "" : "initially-inactive") ?>">
	<?php echo $form['file']->renderError() ?>
	<?php echo $form['file']->render() ?>
	<?php // If you tamper with this, the next form will be missing a default radio button choice ?>
	<?php // This ought to work but the value winds up empty ?>
  <?php echo $form['view_is_secure']->render() ?>
	<?php if (!$first): ?>
	  <ul class="a-controls a-media-upload-subform-controls"><li><a href="#" class="a-btn icon no-label a-close"><?php echo __('Remove', null, 'apostrophe') ?></a></li></ul>
	<?php endif ?>
</div>
