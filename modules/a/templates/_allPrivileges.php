<?php
  // Compatible with sf_escaping_strategy: true
  $admin = isset($admin) ? $sf_data->getRaw('admin') : null;
  $form = isset($form) ? $sf_data->getRaw('form') : null;
  $inherited = isset($inherited) ? $sf_data->getRaw('inherited') : null;
  $hasSubpages = isset($hasSubpages) ? $sf_data->getRaw('hasSubpages') : null;
?>


<?php // Careful, leave "apply to subpages" alone. Striking through the Remove button ?>
<?php // seems like a good way to suggest you can click again to undo it though ?>
<style>
.a-removing .a-who, .a-removing .a-extra, .a-removing .a-actions
{
  text-decoration: line-through;
}
#a-page-permissions-view-extended, #a-page-permissions-edit-extended
{
  display: none;
}
</style>

<?php use_helper('a') ?>

<div class="a-options-section permissions a-accordion clearfix">

  <h3 class="a-permissions-heading a-view-permissions"><?php echo a_('View Permissions') ?></h3>

  <div class="a-accordion-content">
  	<div class="a-form-row a-page-permissions">

  	  <h4><?php echo a_('Who can see this page?') ?></h4>

   			<div class="a-page-permissions-section view-options-widget-container clearfix">
    	    <?php // Tristate radio button: public, login required, admins only ?>
          <?php echo $form['view_options']->render(array('class' => 'view-options-widget')) ?>
    	  </div>

        <?php if (isset($form['view_options_apply_to_subpages'])): ?>
      	  <div class="a-cascade-option cascade-checkbox a-apply-to-subpages-row">
      	    <?php echo $form['view_options_apply_to_subpages']->render() ?>
      	    <?php echo $form['view_options_apply_to_subpages']->renderLabel(a_('apply to subpages')) ?>
      	  </div>
        <?php endif ?>
        
    	  <?php // Extended permissions, hidden when the setting is public or admins only ?>
    	  <div id="a-page-permissions-view-extended">

					<hr class="a-hr" />

	    		<p class="a-help"><?php echo a_('Select Groups and Individuals who can view this page and subpages') ?></p>

      		<div class="a-page-permissions-section clearfix">
      			<h4><label><?php echo a_('Groups') ?></label></h4>
      			<?php // This div is replaced by JS ?>
      			<div class="a-page-permissions-widget clearfix" id="a-page-permissions-view-groups"></div>
      			<?php a_js_call('apostrophe.enablePermissions(?)', array('id' => 'a-page-permissions-view-groups', 'hiddenField' => 'a_settings_settings_view_groups', 'name' => 'groups', 'removeLabel' => a_('Remove'), 'addLabel' => a_('+ Add a Group'), 'extra' => false, 'applyToSubpagesLabel' => a_('apply to subpages'), 'hasSubpages' => $hasSubpages)) ?>
      		</div>

      		<div class="a-page-permissions-section clearfix">
      			<h4><label><?php echo a_('Individuals') ?></label></h4>
      			<?php // This div is replaced by JS ?>
      			<div class="a-page-permissions-widget clearfix" id="a-page-permissions-view-individuals"></div>
        		<?php a_js_call('apostrophe.enablePermissions(?)', array('id' => 'a-page-permissions-view-individuals', 'hiddenField' => 'a_settings_settings_view_individuals', 'name' => 'individuals', 'removeLabel' => a_('Remove'), 'addLabel' => a_('+ Add an Individual'), 'extra' => false, 'applyToSubpagesLabel' => a_('apply to subpages'), 'hasSubpages' => $hasSubpages)) ?>
      		</div>

      	</div>

  	</div>

  </div>
</div>

<hr class="a-hr" />

<?php if ($admin): ?>
  <div class="a-options-section permissions a-accordion clearfix">
    <h3 class="a-permissions-heading a-edit-permissions"><?php echo a_('Edit Permissions') ?></h3>

    <div class="a-accordion-content">
    	<div class="a-form-row a-page-permissions">
 
   	  	<h4><?php echo a_('Who can edit this page?') ?></h4>

     	  <div class="a-edit-admin-lock"><?php echo $form['edit_admin_lock'] ?><?php echo a_('Lock this page so only admins can edit or delete.') ?></div>

     	  <div id="a-page-permissions-edit-extended">

  				<hr class="a-hr" />
  	   		<p class="a-help"><?php echo a_('Select Groups and Individuals who can edit this page') ?></p>

       		<div class="a-page-permissions-section clearfix">
       			<h4><label><?php echo a_('Groups') ?></label></h4>
       			<?php // This div is replaced by JS ?>
       			<div class="a-page-permissions-widget clearfix" id="a-page-permissions-groups"></div>
       			<?php a_js_call('apostrophe.enablePermissions(?)', array('id' => 'a-page-permissions-groups', 'hiddenField' => 'a_settings_settings_edit_groups', 'name' => 'groups', 'removeLabel' => a_('Remove'), 'addLabel' => a_('+ Add a Group'), 'data' => array(array('id' => 1, 'name' => 'Guests & Editors', 'selected' => true, 'extra' => true), array('id' => 2, 'name' => 'Faculty'), array('id' => 3, 'name' => 'Deans')), 'extra' => true, 'extraLabel' => a_('add / delete pages'), 'applyToSubpagesLabel' => a_('apply to subpages'), 'hasSubpages' => $hasSubpages)) ?>
       		</div>

       		<div class="a-page-permissions-section clearfix">
       			<h4><label><?php echo a_('Individuals') ?></label></h4>
       			<?php // This div is replaced by JS ?>
       			<div class="a-page-permissions-widget clearfix" id="a-page-permissions-individuals"></div>
         		<?php a_js_call('apostrophe.enablePermissions(?)', array('id' => 'a-page-permissions-individuals', 'hiddenField' => 'a_settings_settings_edit_individuals', 'name' => 'individuals', 'removeLabel' => a_('Remove'), 'addLabel' => a_('+ Add an Individual'), 'data' => array(array('id' => 1, 'name' => 'dick', 'selected' => true, 'extra' => true), array('id' => 2, 'name' => 'jane'), array('id' => 3, 'name' => 'larry')), 'extra' => true, 'extraLabel' => a_('add / delete pages'), 'applyToSubpagesLabel' => a_('apply to subpages'), 'hasSubpages' => $hasSubpages)) ?>
       		</div>

      	</div>

    	</div>
    </div>
  </div>
<?php endif ?>
<?php a_js_call('apostrophe.enablePermissionsToggles()') ?>
