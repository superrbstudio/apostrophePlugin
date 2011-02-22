<?php use_helper('a') ?>
<?php // 1. We've introduced group permissions for pages and blogs, so let the admin edit groups too ?>
<?php // 2. Permissions admin is still for superadmin (developer) only, since there is no ?>
<?php // value in adding a permission unless you are adding source code that uses it, ?>
<?php // and deleting one could be harmful ?>

<?php if ($sf_user->hasCredential('cms_admin')): ?>
  <ul class="a-ui a-controls a-admin-action-controls">
	  <li class="dashboard"><h4><?php echo link_to(__('User Dashboard', null, 'apostrophe'), 'aUserAdmin/index') ?></h4></li>
	  <li><?php echo link_to('<span class="icon"></span>'.__('Add User', null, 'apostrophe'), 'aUserAdmin/new', array('class' => 'a-btn icon a-add')) ?></li>

	  <li class="dashboard"><h4><?php echo link_to(__('Group Dashboard', null, 'apostrophe'), 'aGroupAdmin/index') ?></h4></li>
	  <li><?php echo link_to('<span class="icon"></span>'.__('Add Group', null, 'apostrophe'), 'aGroupAdmin/new', array('class' => 'a-btn icon a-add')) ?></li>
    
    <?php if ($sf_user->isSuperAdmin()): ?>
  	  <li class="dashboard"><h4><?php echo link_to(__('Permissions Dashboard', null, 'apostrophe'), 'aPermissionAdmin/index') ?></h4></li>
  	  <li><?php echo link_to('<span class="icon"></span>'.__('Add Permission', null, 'apostrophe'), 'aPermissionAdmin/new', array('class' => 'a-btn icon a-add')) ?></li>
  	<?php endif ?>
  </ul>
<?php endif ?>