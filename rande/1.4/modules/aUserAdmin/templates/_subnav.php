<?php use_helper('I18N') ?>
<?php // Regular admins don't get to change which groups and permissions exist, ?>
<?php // that has serious consequences and doesn't make much sense unless you're a ?>
<?php // PHP developer extending the system. To add a user to one of the two standard groups ?>
<?php // (admin and editor) or other groups we already added to the system, just edit that user ?>

<?php if ($sf_user->isSuperAdmin()): ?>
  <ul class="a-controls a-admin-action-controls">
	  <li class="dashboard"><h4><?php echo link_to(__('User Dashboard', null, 'apostrophe'), 'aUserAdmin/index') ?></h4></li>
	  <li><?php echo link_to(__('Add User', null, 'apostrophe'), 'aUserAdmin/new', array('class' => 'a-btn icon a-add')) ?></li>

	  <li class="dashboard"><h4><?php echo link_to(__('Group Dashboard', null, 'apostrophe'), 'aGroupAdmin/index') ?></h4></li>
	  <li><?php echo link_to(__('Add Group', null, 'apostrophe'), 'aGroupAdmin/new', array('class' => 'a-btn icon a-add')) ?></li>

	  <li class="dashboard"><h4><?php echo link_to(__('Permissions Dashboard', null, 'apostrophe'), 'aPermissionAdmin/index') ?></h4></li>
	  <li><?php echo link_to(__('Add Permission', null, 'apostrophe'), 'aPermissionAdmin/new', array('class' => 'a-btn icon a-add')) ?></li>
  </ul>
<?php endif ?>