<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
?>

<?php use_helper('a') ?>

<div class="a-form-row a-hidden">
	<?php echo $form->renderHiddenFields() ?>
</div>

<div class="a-form-row title">
	<?php echo $form['title']->renderLabel('Title') ?>
	<div class="a-form-field">
		<?php echo $form['title']->render() ?>
	</div>
	<?php echo $form['title']->renderError() ?>
</div>

<div class="a-form-row description">
	<?php echo $form['description']->renderLabel('Description') ?>
	<div class="a-form-field">
		<?php echo $form['description']->render() ?>
	</div>
	<?php echo $form['description']->renderError() ?>
</div>

<div class="a-form-row link">
	<?php echo $form['url']->renderLabel('Link') ?>
	<div class="a-form-field">
		<?php echo $form['url']->render() ?>
	</div>
	<?php echo $form['url']->renderError() ?>
</div>


<script type="text/javascript" charset="utf-8">
window.apostrophe.registerOnSubmit("<?php echo $id ?>", 
  function(slotId)
  {
    <?php # FCK doesn't do this automatically on an AJAX "form" submit on every major browser ?>
    var value = FCKeditorAPI.GetInstance('slotform-<?php echo $id ?>-description').GetXHTML();
    $('#slotform-<?php echo $id ?>-description').val(value);
  }
);
</script>