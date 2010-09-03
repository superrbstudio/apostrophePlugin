<?php
  // Compatible with sf_escaping_strategy: true
  $admin = isset($admin) ? $sf_data->getRaw('admin') : null;
  $form = isset($form) ? $sf_data->getRaw('form') : null;
  $inherited = isset($inherited) ? $sf_data->getRaw('inherited') : null;
?>
<?php use_helper('a') ?>
<?php if (isset($form['editors']) || isset($form['managers'])): ?>
	<h3><?php echo a_('Page Permissions') ?></h3>

	<div class="a-page-permissions content">
		<p class="a-form-help-text"><?php echo a_('Select Groups and Individuals to edit this page and it’s children') ?></p>
		<div class="a-page-permissions-section a-page-permissions-by-group">
		  <?php include_partial('a/privileges', 
		    array('form' => $form, 'widget' => 'group_editors',
		      'label' => 'Editor Groups', 'inherited' => $inherited['group_edit'],
		      'admin' => $admin['group_edit'])) ?>
		  <?php include_partial('a/privileges', 
		    array('form' => $form, 'widget' => 'group_managers',
		      'label' => 'Manager Groups', 'inherited' => $inherited['group_manage'],
		      'admin' => $admin['group_manage'])) ?>
		</div>
		<div class="a-page-permissions-section a-page-permissions-by-user">
		  <?php include_partial('a/privileges', 
		    array('form' => $form, 'widget' => 'editors',
		      'label' => 'Individual Editors', 'inherited' => $inherited['edit'],
		      'admin' => $admin['edit'])) ?>
		  <?php include_partial('a/privileges', 
		    array('form' => $form, 'widget' => 'managers',
		      'label' => 'Individual Managers', 'inherited' => $inherited['manage'],
		      'admin' => $admin['manage'])) ?>
		</div>
	</div>
	<?php a_js_call('aMultipleSelect(?, ?)', '.a-page-permissions-by-user', array('choose-one' => a_("Choose a User to Add"))) ?>
	<?php a_js_call('aMultipleSelect(?, ?)', '.a-page-permissions-by-group', array('choose-one' => a_("Choose a Group to Add"))) ?>
<?php endif ?>