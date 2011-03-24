<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
?>

<?php use_helper('a') ?>

<div class="a-form-row a-hidden">
	<?php echo $form->renderHiddenFields() ?>
</div>

<div class="a-form-row value">
	<div class="a-form-field">
		<?php echo $form['value']->render() ?>
	</div>
	<div class="a-form-error"><?php echo $form['value']->renderError() ?></div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		$('textarea.aTextSlot.multi-line').simpleautogrow();
	});
</script>