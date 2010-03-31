<?php if (0): ?>
<?php // We could echo the entire form here, which would include validation errors etc., but raw HTML slots are unvalidated by definition ?>
<?php echo $form ?>
<?php endif ?>

<?php // For this simple case we just want the form field without a label, and we know there are no validation errors to display ?>
<?php echo $form->renderHiddenFields() ?>
<?php echo $form['value']->render() ?>

<script type="text/javascript" charset="utf-8">
$(document).ready (function() {
	$('textarea.aTextSlot.multi-line').autogrow({
		// maxHeight: 400, //Max Height was causing problems.
		minHeight: 30,
		lineHeight: 16
	});
});
</script>