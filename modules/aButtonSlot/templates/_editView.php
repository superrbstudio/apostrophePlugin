<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
  $embed = isset($embed) ? $sf_data->getRaw('embed') : null;
  $dimensions = isset($dimensions) ? $sf_data->getRaw('dimensions') : null;
  $item = isset($item) ? $sf_data->getRaw('item') : null;
?>

<?php use_helper('a') ?>

<h4 class="a-slot-edit-title"><?php echo a_('Button Slot') ?></h4>

<?php if ($item): ?>
  <div class="a-form-row image">
		<label for="a-button-edit-view-<?php echo $pageid.'-'.$name.'-'.$permid; ?>">Image</label>
    <div class="a-form-field" id="a-button-edit-view-<?php echo $pageid.'-'.$name.'-'.$permid; ?>">
    	<?php $embed = str_replace(array("_WIDTH_", "_HEIGHT_", "_c-OR-s_", "_FORMAT_"), array($dimensions['width'], $dimensions['height'], $dimensions['resizeType'],  $dimensions['format']), $embed) ?>
    	<?php echo $embed ?>			
    </div>
  </div>
<?php endif ?>

<div class="a-form-row a-hidden">
	<?php echo $form->renderHiddenFields() ?>
</div>

<?php if ($options['title']): ?>
	<div class="a-form-row title">
		<?php echo $form['title']->renderLabel('Title') ?>
		<div class="a-form-field">
			<?php echo $form['title']->render() ?>
		</div>
		<?php echo $form['title']->renderError() ?>
	</div>
<?php endif ?>

<div class="a-form-row link">
	<?php echo $form['url']->renderLabel('Link') ?>
	<div class="a-form-field">
		<?php echo $form['url']->render() ?>
	</div>
	<?php echo $form['url']->renderError() ?>
</div>

<?php if ($options['description']): ?>
	<div class="a-form-row description">
		<?php echo $form['description']->renderLabel('Description') ?>
		<div class="a-form-field">
			<?php echo $form['description']->render() ?>
		</div>
		<?php echo $form['description']->renderError() ?>
	</div>

	<script type="text/javascript">
	window.apostrophe.registerOnSubmit("<?php echo $id ?>", 
	  function(slotId)
	  {
	    <?php # FCK doesn't do this automatically on an AJAX "form" submit on every major browser ?>
	    var value = FCKeditorAPI.GetInstance('slot-form-<?php echo $id ?>-description').GetXHTML();
	    $('#slot-form-<?php echo $id ?>-description').val(value);
	  }
	);
	</script>
<?php endif ?>

<?php a_js_call('apostrophe.slotEnhancements(?)', array('slot' => '#a-slot-'.$pageid.'-'.$name.'-'.$permid, 'editClass' => 'a-options')) ?>
