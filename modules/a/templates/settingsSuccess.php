<?php
  // Compatible with sf_escaping_strategy: true
  $admin = isset($admin) ? $sf_data->getRaw('admin') : null;
  $engineForm = isset($engineForm) ? $sf_data->getRaw('engineForm') : null;
  $engineSettingsPartial = isset($engineSettingsPartial) ? $sf_data->getRaw('engineSettingsPartial') : null;
  $form = isset($form) ? $sf_data->getRaw('form') : null;
  $inherited = isset($inherited) ? $sf_data->getRaw('inherited') : null;
  $page = isset($page) ? $sf_data->getRaw('page') : null;
  $parent = isset($parent) ? $sf_data->getRaw('parent') : null;
  $popularTags = isset($popularTags) ? $sf_data->getRaw('popularTags') : null;
  $existingTags = isset($existingTags) ? $sf_data->getRaw('existingTags') : null;
  $allTags = isset($existingTags) ? $sf_data->getRaw('allTags') : null;
  $slugStem = isset($slugStem) ? $sf_data->getRaw('slugStem') : null;
?>
<?php use_helper('a') ?>

<?php $stem = $page->isNew() ? 'a-create-page' : 'a-page-settings' ?>

  <form method="POST" action="#" name="<?php echo $stem ?>-form" id="<?php echo $stem ?>-form" class="a-ui a-options a-page-form <?php echo $stem ?>-form dropshadow">

	<div class="a-form-row a-hidden">
		<?php echo $form->renderHiddenFields() ?>
	</div>

	<?php echo $form->renderGlobalErrors() ?>

	<div class="a-options-section open">

		<h3>
			<?php if ($page->getSlug() == "/"): ?>
				<?php echo __('Homepage Title', array(), 'apostrophe') ?>
			<?php else: ?>
				<?php echo __('Title &amp; Permalink', array(), 'apostrophe') ?>
			<?php endif ?>
		</h3>

		<div class="a-form-row a-page-title">
		  <?php // "Why realtitle?" To avoid excessively magic features of sfFormDoctrine. 
		 				// There is another way but I think it might still try to initialize the field 
						// in an unwanted fashion even if it allows them to be saved right ?>
			<div class="a-form-field">
				<?php echo $form['realtitle']->render(array('id' => 'a-edit-page-title', 'class' => 'a-page-title-field')) ?>
			</div>
			<?php echo $form['realtitle']->renderError() ?>
		</div>

		<?php if (isset($form['slug'])): ?>
		  <div class="a-form-row a-page-slug">
				<h4><?php echo $form['slug']->renderLabel('http://'.$_SERVER['HTTP_HOST']) ?></h4>
				<div class="a-form-field">
		    	<?php echo $form['slug'] ?>
				</div>
		    <?php echo $form['slug']->renderError() ?>
		  </div>
		<?php endif ?>

	</div>
	
	<hr/>
	
	<div class="a-options-section a-accordion">
		
		<h3 class="a-accordion-toggle">Options</h3>
		
		<div class="a-accordion-content">
			
			<div class="a-form-row engine a-page-type">
				<h4><?php echo $form['engine']->renderLabel(__('Page Type', array(), 'apostrophe')) ?></h4>
				<div class="a-form-field">
			  	<?php echo $form['engine']->render() ?>
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

      <?php // This outer div is an AJAX target, it has to be here all the time ?>
      <?php // in case the user selects an engine ?>
			<div class="a-form-row a-engine-page-settings">
  		  <?php if (isset($engineSettingsPartial)): ?>
			    <?php include_partial($engineSettingsPartial, array('form' => $engineForm)) ?>
			  <?php endif ?>
			</div>

			<div class="a-form-row status">
			  <h4><label><?php echo __('Page Status', null, 'apostrophe') ?></label></h4>
		  	<div class="<?php echo $stem ?>-status">
					<?php echo $form['archived'] ?>
					<?php if(isset($form['cascade_archived'])): ?>
						<?php echo $form['cascade_archived'] ?> <?php echo __('Cascade status changes to children', null, 'apostrophe') ?>
					<?php endif ?> 
				</div>
			</div>			

		</div>
	</div>

	<hr/>

	<div class="a-options-section a-accordion">

		<h3>Tags &amp; Metadata</h3>

		<div class="a-accordion-content">			
			
			<div class="a-form-row keywords">
				<div class="a-form-field">
					<?php echo $form['tags'] ?>
				</div>
				<?php echo $form['tags']->renderError() ?>
				<?php a_js_call('aInlineTaggableWidget(?, ?)', '.tags-input', array('popular-tags' => $popularTags, 'existing-tags' => $existingTags, 'all-tags' => $allTags, 'typeahead-url' => url_for('taggableComplete/complete'), 'tagsLabel' => 'Page Tags')) ?>
			</div>

			<div class="a-form-row meta-description">
				<h4 class="a-block"><?php echo $form['meta_description']->renderLabel(__('Meta Description', array(), 'apostrophe')) ?></h4>
				<div class="a-form-field">
					<?php echo $form['meta_description'] ?>
				</div>
				<?php echo $form['meta_description']->renderError() ?>
			</div>
			
		</div>
	</div>

  <?php if ($sf_user->hasCredential('cms_admin')): ?>
 		<hr/>
  	<div class="a-options-section a-accordion">
      <?php include_partial('a/allPrivileges', array('form' => $form, 'inherited' => $inherited, 'admin' => $admin)) ?>
  	</div>
  <?php endif ?>
  
	<hr/>
	<div class="a-options-section">
		<ul class="a-ui a-controls">		
		  <li><input type="submit" name="submit" value="<?php echo htmlspecialchars(__($page->isNew() ? 'Create Page' : 'Save Changes', null, 'apostrophe')) ?>" class="a-btn a-submit" id="<?php echo $stem ?>-submit" /></li>
			<li><a href="#cancel" onclick="return false;" class="a-btn icon a-cancel a-options-cancel" title="<?php echo __('Cancel', null, 'apostrophe') ?>"><span class="icon"></span><?php echo __('Cancel', null, 'apostrophe') ?></a></li>
			<?php if ((!$page->isNew()) && $page->userHasPrivilege('manage')): ?>
				<?php $childMessage = ''; ?>
				<?php if($page->hasChildren()): ?><?php $childMessage = __("This page has children that will also be deleted. ", null, 'apostrophe'); ?><?php endif; ?>
	      <li class="a-align-right"><?php echo link_to('<span class="icon"></span>'.__("Delete This Page", null, 'apostrophe'), "a/delete?id=" . $page->getId(), array("confirm" => $childMessage . __('Are you sure? This operation can not be undone. Consider unpublishing the page instead.', null, 'apostrophe'), 'class' => 'a-btn icon a-delete lite', 'title' => __('Delete This Page', null, 'apostrophe'))) ?></li>
			<?php endif ?>
		</ul>
	</div>
	
</form>

<?php a_js_call('apostrophe.enablePageSettings(?)', array('id' => $stem, 'pageId' => $page->id, 'new' => $page->isNew(), 'slugStem' => $slugStem,  'url' => url_for('a/settings') . '?' . http_build_query($page->isNew() ? array('new' => 1, 'parent' => $parent->slug) : array('id' => $page->id)), 'slugifyUrl' => url_for('a/slugify'), 'engineUrl' => url_for('a/engineSettings'))) ?>
<?php a_js_call('apostrophe.accordion(?)', array('accordion_toggle' => '.a-options-section:not(".open") h3')) ?>

<?php // All AJAX actions that use a_js_call must do this since they have no layout to do it for them ?>
<script src="/sfJqueryReloadedPlugin/js/plugins/jquery.autocomplete.js"></script>
<script src="/sfDoctrineActAsTaggablePlugin/js/pkTagahead.js"></script>

<?php a_include_js_calls() ?>