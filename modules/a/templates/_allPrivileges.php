<?php
  // Compatible with sf_escaping_strategy: true
  $admin = isset($admin) ? $sf_data->getRaw('admin') : null;
  $form = isset($form) ? $sf_data->getRaw('form') : null;
  $inherited = isset($inherited) ? $sf_data->getRaw('inherited') : null;
?>

<?php // Careful, leave "Apply to Subpages" alone. Striking through the Remove button ?>
<?php // seems like a good way to suggest you can click again to undo it though ?>
<style>
.a-removing .a-who, .a-removing .a-extra, .a-removing .a-actions
{
  text-decoration: line-through;
}
</style>

<?php use_helper('a') ?>
	<h3><?php echo a_('Edit Permissions') ?></h3>

	<div class="a-accordion-content">
		<div class="a-form-row a-page-permissions">
		  <h3><?php echo a_('Who can edit this page?') ?></h3>
		  <ul>
		    <li><?php echo $form['edit_admin_lock'] ?><?php echo a_('Lock this page so only admins can edit or delete.') ?></li>
		  </ul>
			<p class="a-form-help-text"><?php echo a_('Select Groups and Individuals who can edit this page') ?></p>
			<div class="a-page-permissions-section">
  			<h4><?php echo a_('Individuals') ?></h4>
  			<?php // This div is replaced by JS ?>
  			<div class="a-page-permissions-widget" id="a-page-permissions-individuals"></div>
    		<?php a_js_call('apostrophe.enablePermissions(?)', array('id' => 'a-page-permissions-individuals', 'hiddenField' => 'a_settings_settings_edit_individuals', 'name' => 'individuals', 'removeLabel' => a_('Remove'), 'addLabel' => a_('+ Add an Individual'), 'data' => array(array('id' => 1, 'name' => 'dick', 'selected' => true, 'extra' => true), array('id' => 2, 'name' => 'jane'), array('id' => 3, 'name' => 'larry')), 'extra' => true, 'extraLabel' => a_('Add / Delete Pages'), 'applyToSubpagesLabel' => a_('Apply to Subpages'))) ?>
			</div>
			<div class="a-page-permissions-section">
  			<h4><?php echo a_('Groups') ?></h4>
  			<?php // This div is replaced by JS ?>
  			<div class="a-page-permissions-widget" id="a-page-permissions-groups"></div>
  			<?php a_js_call('apostrophe.enablePermissions(?)', array('id' => 'a-page-permissions-groups', 'hiddenField' => 'a_settings_settings_edit_groups', 'name' => 'groups', 'removeLabel' => a_('Remove'), 'addLabel' => a_('+ Add a Group'), 'data' => array(array('id' => 1, 'name' => 'Guests & Editors', 'selected' => true, 'extra' => true), array('id' => 2, 'name' => 'Faculty'), array('id' => 3, 'name' => 'Deans')), 'extra' => true, 'extraLabel' => a_('Add / Delete Pages'), 'applyToSubpagesLabel' => a_('Apply to Subpages'))) ?>
			</div>
		</div>
	</div>
