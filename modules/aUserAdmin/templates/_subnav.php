<?php use_helper('a') ?>
<?php // 1. We've introduced group permissions for pages and blogs, so let the admin edit groups too ?>
<?php // 2. Permissions admin is still for superadmin (developer) only, since there is no ?>
<?php // value in adding a permission unless you are adding source code that uses it, ?>
<?php // and deleting one could be harmful ?>

<?php if ($sf_user->hasCredential('cms_admin')): ?>
  <ul class="a-ui a-controls a-admin-action-controls">

    <li class="a-admin-action-controls-item">
      <?php echo link_to(__('User Dashboard', null, 'apostrophe'), 'aUserAdmin/index', array('class' => 'a-btn')) ?>
      <?php echo link_to('<span class="icon"></span>'.__('Add User', null, 'apostrophe'), 'aUserAdmin/new', array('class' => 'a-btn icon a-add no-label')) ?>
	  </li>

    <li class="a-admin-action-controls-item">
      <?php echo link_to(__('Group Dashboard', null, 'apostrophe'), 'aGroupAdmin/index', array('class' => 'a-btn')) ?>
      <?php echo link_to('<span class="icon"></span>'.__('Add Group', null, 'apostrophe'), 'aGroupAdmin/new', array('class' => 'a-btn icon a-add no-label')) ?>
    </li>

    <?php if ($sf_user->isSuperAdmin()): ?>
    <li class="a-admin-action-controls-item">
      <?php echo link_to(__('Permissions Dashboard', null, 'apostrophe'), 'aPermissionAdmin/index', array('class' => 'a-btn')) ?>
      <?php echo link_to('<span class="icon"></span>'.__('Add Permission', null, 'apostrophe'), 'aPermissionAdmin/new', array('class' => 'a-btn icon a-add no-label')) ?>
    </li>
    <?php endif ?>

  </ul>
<?php endif ?>
