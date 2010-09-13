<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
?>

<?php use_helper('a') ?>

<a href="#add-page" class="a-btn icon a-add a-create-page" id="a-create-page-button" onclick="return false;"><span class="icon"></span><?php echo __("Add Page", null, 'apostrophe') ?></a>

<form method="POST" action="<?php echo url_for('a/create') ?>" id="a-create-page-form" class="a-ui a-options a-page-form a-create-page-form dropshadow">

	<div class="a-form-row a-hidden"><?php echo $form->renderHiddenFields() ?></div>
	<div class="a-form-row a-hidden"><?php echo $form['parent']->render(array('id' => 'a-create-page-parent', )) ?></div>

	<?php echo $form->renderGlobalErrors() ?>	

	<div class="a-options-section">	
		<div class="a-form-row a-page-title">
			<div class="a-form-field">
				<?php echo $form['title']->render(array('id' => 'a-create-page-title',  'class' => 'a-page-title-field')) ?>
			</div>
			<?php echo $form['title']->renderError() ?>
		</div>
	</div>
	
 	<hr/>

	<div class="a-options-section">	
		<div class="a-form-row a-page-type">
			<?php echo $form['engine']->renderLabel(__('Page Type', array(), 'apostrophe')) ?>
			<div class="a-form-field">
				<?php echo $form['engine']->render(array('id' => 'a-create-page-type', )) ?>
			</div>
			<?php echo $form['engine']->renderError() ?>
		</div>
		
		<div class="a-form-row a-page-template">
			<?php echo $form['template']->renderLabel(__('Page Template', array(), 'apostrophe')) ?>
			<div class="a-form-field">
				<?php echo $form['template']->render(array('id' => 'a-create-page-template', )) ?>
			</div>
			<?php echo $form['template']->renderError() ?>
		</div>
	</div>	

	<hr/>	

	<div class="a-options-section">
		<ul class="a-ui a-controls">
	  	<li><input type="submit" class="a-btn a-submit" value="<?php echo __('Create Page', null, 'apostrophe') ?>" /></li>
	  	<li><a href="#cancel" onclick="return false;" class="a-btn icon a-cancel a-options-cancel" title="<?php echo __('Cancel', null, 'apostrophe') ?>"><?php echo __("Cancel", null, 'apostrophe') ?></a></li>
		</ul>
	</div>
	
</form>

<?php a_js_call('apostrophe.selfLabel(?)', array('selector' => '#a-create-page-title', 'title' => a_('Page Title'))) ?>
<?php a_js_call('apostrophe.createPage(?)', array()) ?>