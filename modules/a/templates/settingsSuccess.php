<?php
  // Compatible with sf_escaping_strategy: true
  $admin = isset($admin) ? $sf_data->getRaw('admin') : null;
  $engineForm = isset($engineForm) ? $sf_data->getRaw('engineForm') : null;
  $engineSettingsPartial = isset($engineSettingsPartial) ? $sf_data->getRaw('engineSettingsPartial') : null;
  $form = isset($form) ? $sf_data->getRaw('form') : null;
  $inherited = isset($inherited) ? $sf_data->getRaw('inherited') : null;
  $page = isset($page) ? $sf_data->getRaw('page') : null;
  $popularTags = isset($popularTags) ? $sf_data->getRaw('popularTags') : null;
  $existingTags = isset($existingTags) ? $sf_data->getRaw('existingTags') : null;
?>
<?php use_helper('Url', 'jQuery', 'I18N', 'a') ?>

	<?php echo jq_form_remote_tag(
	  array(
	    'update' => 'a-page-settings',
	    'url' => 'a/settings',
			'complete' => '$(".a-page-overlay").hide();', 
	    'script' => true),
	  	array(
		    'name' => 'a-page-settings-form', 
		    'id' => 'a-page-settings-form',
				'class' => 'dropshadow a-options a-page-form', )) ?>

	<div class="a-form-row a-hidden">
		<?php echo $form->renderHiddenFields() ?>
	</div>
	<?php echo $form->renderGlobalErrors() ?>

	<div class="a-page-settings-section page-info">

		<div class="a-form-row a-page-title">
		  <?php // "Why realtitle?" To avoid excessively magic features of sfFormDoctrine. There is another way but I think it might still try to initialize the field in an unwanted fashion even if it allows them to be saved right ?>
			<?php echo $form['realtitle']->renderLabel(__('Page Title', array(), 'apostrophe')) ?>
			<div class="a-form-field">
				<?php echo $form['realtitle']->render(array('id' => 'a-edit-page-title', 'class' => 'a-page-title-field')) ?>
			</div>
			<?php echo $form['realtitle']->renderError() ?>
		</div>

		<?php if (isset($form['slug'])): ?>
		  <div class="a-form-row a-page-slug">
				<?php echo $form['slug']->renderLabel(__('Page Slug', array(), 'apostrophe')) ?>
		    <?php echo $form['slug'] ?>
		    <?php echo $form['slug']->renderError() ?>
		  </div>
		<?php endif ?>

		<div class="a-form-row engine a-page-type">
			<?php echo $form['engine']->renderLabel(__('Page Type', array(), 'apostrophe')) ?>
		  <?php echo $form['engine']->render(array('onChange' => 'aUpdateEngineAndTemplate()')) ?>
		  <?php echo $form['engine']->renderError() ?>
		</div>

		<div class="a-form-row a-edit-page-template">
			<?php echo $form['template']->renderLabel(__('Page Template', array(), 'apostrophe')) ?>
		  <?php echo $form['template'] ?>
		  <?php echo $form['template']->renderError() ?>
		</div>

		<div class="a-form-row keywords">
			<?php echo $form['tags']->renderLabel(__('Page Tags', array(), 'apostrophe')) ?>
			<?php echo $form['tags'] ?>
			<?php echo $form['tags']->renderError() ?>
			<?php a_js_call('aInlineTaggableWidget(?, ?)', '.tags-input', array('popular-tags' => $popularTags, 'existing-tags' => $existingTags, 'typeahead-url' => url_for('taggableComplete/complete'))) ?>
		</div>

		<div class="a-form-row meta-description">
			<?php echo $form['meta_description']->renderLabel(__('Meta Description', array(), 'apostrophe')) ?>
			<?php echo $form['meta_description'] ?>
			<?php echo $form['meta_description']->renderError() ?>
		</div>

		<div id="a_settings_engine_settings">
		  <?php if (isset($engineSettingsPartial)): ?>
		    <?php include_partial($engineSettingsPartial, array('form' => $engineForm)) ?>
	    <?php endif ?>
		</div>

	</div>

	<hr/>

	<div class="a-page-settings-section page-status">
		<h4 class="a-page-settings-section-head header"><?php echo __('Page Status', null, 'apostrophe') ?></h4>
		<div class="a-page-status content">
			<div class="a-form-row status">
			  <h5><?php echo __('Page Status', null, 'apostrophe') ?></h5>
			  	<div class="a-page-settings-status">
				    <?php echo $form['archived'] ?>
	          <?php if(isset($form['cascade_archived'])): ?>
	            <?php // If you want your <em> back here, do it in the translation file ?>
	            <?php echo $form['cascade_archived'] ?> <?php echo __('Cascade status changes to children', null, 'apostrophe') ?>
	          <?php endif ?> 
					</div>
			</div>			
			<div class="a-form-row privacy">
			  <h5><?php echo __('Page Privacy', null, 'apostrophe') ?></h5>
			  	<div class="a-page-settings-status">
						<?php echo $form['view_is_secure'] ?>
						<?php if(isset($form['cascade_view_is_secure'])): ?>
	              <?php echo $form['cascade_view_is_secure'] ?> <?php echo __('Cascade privacy changes to children', null, 'apostrophe') ?>
	          <?php endif ?> 
					</div>
			</div>
		</div>
	</div>
	
	<hr/>
	
	<div class="a-page-settings-section page-permissions">
    <?php include_partial('a/allPrivileges', array('form' => $form, 'inherited' => $inherited, 'admin' => $admin)) ?>
	</div>

	<hr/>

	<div class="a-page-settings-section page-submit">
			  <input type="submit" name="submit" value="<?php echo htmlspecialchars(__('Save Changes', null, 'apostrophe')) ?>" class="a-btn a-submit" id="a-page-settings-submit" />
				<?php echo jq_link_to_function(__('Cancel', null, 'apostrophe'), '',  array('class' => 'a-btn icon a-cancel', 'title' => __('Cancel', null, 'apostrophe'))) ?>
			<?php if ($page->userHasPrivilege('manage')): ?>
				<?php $childMessage = ''; ?>
				<?php if($page->hasChildren()): ?><?php $childMessage = __("This page has children that will also be deleted. ", null, 'apostrophe'); ?><?php endif; ?>
	      <?php echo link_to(__("Delete This Page", null, 'apostrophe'), "a/delete?id=" . $page->getId(), array("confirm" => $childMessage . __('Are you sure? This operation can not be undone. Consider unpublishing the page instead.', null, 'apostrophe'), 'class' => 'a-btn icon a-delete nobg mini alt')) ?>
			<?php endif ?>
		</ul>
	</div>
	
</form>
<?php a_js_call('apostrophe.updateEngineAndTemplate(?)', array('url' => url_for('a/engineSettings'), 'id' => $page->id)) ?>
<?php // All AJAX actions that use a_js_call must do this since they have no layout to do it for them ?>

<script src="/sfJqueryReloadedPlugin/js/plugins/jquery.autocomplete.js"></script>
<script src="/sfDoctrineActAsTaggablePlugin/js/pkTagahead.js"></script>
<?php a_include_js_calls() ?>
<script type="text/javascript" charset="utf-8">
	$(document).ready(function() {
		var aPageTypeSelect = $('#a_settings_settings_engine');
		var aPageTemplateSelect = $('.a-edit-page-template');

		if (aPageTypeSelect.attr('selectedIndex')) 
		{
			aPageTemplateSelect.hide();
		}
		else
		{
			aPageTemplateSelect.show();				
		};			

		aPageTypeSelect.change(function(){
			if (aPageTypeSelect.attr("selectedIndex")) 
			{
				aPageTemplateSelect.hide();
			}
			else
			{
				aPageTemplateSelect.show();				
			};			
		});				
	});
</script>
