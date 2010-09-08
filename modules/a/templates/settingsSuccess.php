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
				'class' => 'a-ui a-options a-page-form a-page-settings-form dropshadow')) ?>

	<div class="a-form-row a-hidden">
		<?php echo $form->renderHiddenFields() ?>
	</div>

	<?php echo $form->renderGlobalErrors() ?>

	<div class="a-options-section">
		<h3>Title &amp; Permalink</h3>
		<div class="a-form-row a-page-title">
		  <?php // "Why realtitle?" To avoid excessively magic features of sfFormDoctrine. 
		 				// There is another way but I think it might still try to initialize the field 
						// in an unwanted fashion even if it allows them to be saved right ?>
			<h4><?php echo $form['realtitle']->renderLabel(__('Page Title', array(), 'apostrophe')) ?></h4>
			<div class="a-form-field">
				<?php echo $form['realtitle']->render(array('id' => 'a-edit-page-title', 'class' => 'a-page-title-field')) ?>
			</div>
			<?php echo $form['realtitle']->renderError() ?>
		</div>

		<?php if (isset($form['slug'])): ?>
		  <div class="a-form-row a-page-slug">
				<h4><?php echo $form['slug']->renderLabel(__('Permalink', array(), 'apostrophe')) ?></h4>
				<div class="a-form-field">
		    	<?php echo $form['slug'] ?>
				</div>
		    <?php echo $form['slug']->renderError() ?>
		  </div>
		<?php elseif (0): ?>
			<?php // If It's the ho ?>
		<?php endif ?>

	</div>
	
	<hr/>
	
	<div class="a-options-section">
		
		<h3>Options</h3>
		
		<div class="a-form-row engine a-page-type">
			<h4><?php echo $form['engine']->renderLabel(__('Page Type', array(), 'apostrophe')) ?></h4>
			<div class="a-form-field">
		  	<?php echo $form['engine']->render(array('onChange' => 'aUpdateEngineAndTemplate()')) ?>
			</div>
		  <?php echo $form['engine']->renderError() ?>
		</div>

		<div class="a-form-row a-edit-page-template">
			<h4><?php echo $form['template']->renderLabel(__('Page Template', array(), 'apostrophe')) ?></h4>
			<div class="a-form-field">
		  	<?php echo $form['template'] ?>
			</div>
		  <?php echo $form['template']->renderError() ?>
		</div>

	  <?php if (isset($engineSettingsPartial)): ?>
		<div id="a_settings_engine_settings">
		    <?php include_partial($engineSettingsPartial, array('form' => $engineForm)) ?>
		</div>
  	<?php endif ?>

		<div class="a-form-row status">
		  <h4><label><?php echo __('Page Status', null, 'apostrophe') ?></label></h4>
		  	<div class="a-page-settings-status">
			    <?php echo $form['archived'] ?>
          <?php if(isset($form['cascade_archived'])): ?>
            <?php // If you want your <em> back here, do it in the translation file ?>
            <?php echo $form['cascade_archived'] ?> <?php echo __('Cascade status changes to children', null, 'apostrophe') ?>
          <?php endif ?> 
				</div>
		</div>			

		<div class="a-form-row privacy">
		  <h4><label><?php echo __('Page Privacy', null, 'apostrophe') ?></label></h4>
		  	<div class="a-page-settings-status">
					<?php echo $form['view_is_secure'] ?>
					<?php if(isset($form['cascade_view_is_secure'])): ?>
              <?php echo $form['cascade_view_is_secure'] ?> <?php echo __('Cascade privacy changes to children', null, 'apostrophe') ?>
          <?php endif ?> 
				</div>
		</div>

	</div>

	<hr/>

	<div class="a-options-section">

		<h3>Tags &amp; Metadata</h3>

		<div class="a-form-row keywords">
			<div class="a-form-field">
				<?php echo $form['tags'] ?>
			</div>
			<?php echo $form['tags']->renderError() ?>
			<?php a_js_call('aInlineTaggableWidget(?, ?)', '.tags-input', array('popular-tags' => $popularTags, 'existing-tags' => $existingTags, 'typeahead-url' => url_for('taggableComplete/complete'), 'tagsLabel' => 'Page Tags')) ?>
		</div>

		<div class="a-form-row meta-description">
			<h4 class="a-block"><?php echo $form['meta_description']->renderLabel(__('Meta Description', array(), 'apostrophe')) ?></h4>
			<div class="a-form-field">
				<?php echo $form['meta_description'] ?>
			</div>
			<?php echo $form['meta_description']->renderError() ?>
		</div>
		
	</div>

	<hr/>
	
	<div class="a-options-section">
    <?php include_partial('a/allPrivileges', array('form' => $form, 'inherited' => $inherited, 'admin' => $admin)) ?>
	</div>

	<hr/>

	<div class="a-options-section">
		<ul class="a-ui a-controls">		
		  <input type="submit" name="submit" value="<?php echo htmlspecialchars(__('Save Changes', null, 'apostrophe')) ?>" class="a-btn a-submit" id="a-page-settings-submit" />
			<?php echo jq_link_to_function(__('Cancel', null, 'apostrophe'), '',  array('class' => 'a-btn icon a-cancel', 'title' => __('Cancel', null, 'apostrophe'))) ?>
			<?php if ($page->userHasPrivilege('manage')): ?>
				<?php $childMessage = ''; ?>
				<?php if($page->hasChildren()): ?><?php $childMessage = __("This page has children that will also be deleted. ", null, 'apostrophe'); ?><?php endif; ?>
	      <?php echo link_to(__("Delete This Page", null, 'apostrophe'), "a/delete?id=" . $page->getId(), array("confirm" => $childMessage . __('Are you sure? This operation can not be undone. Consider unpublishing the page instead.', null, 'apostrophe'), 'class' => 'a-btn icon a-delete no-label', 'title' => __('Delete This Page', null, 'apostrophe'))) ?>
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
