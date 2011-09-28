<?php
  // Compatible with sf_escaping_strategy: true
  $admin = isset($admin) ? $sf_data->getRaw('admin') : null;
  $engineForm = isset($engineForm) ? $sf_data->getRaw('engineForm') : null;
  $engineSettingsPartial = isset($engineSettingsPartial) ? $sf_data->getRaw('engineSettingsPartial') : null;
  $form = isset($form) ? $sf_data->getRaw('form') : null;
  $inherited = isset($inherited) ? $sf_data->getRaw('inherited') : null;
  $page = isset($page) ? $sf_data->getRaw('page') : null;
  $parent = isset($parent) ? $sf_data->getRaw('parent') : null;
  $slugStem = isset($slugStem) ? $sf_data->getRaw('slugStem') : null;
?>
<?php use_helper('a') ?>
<?php $create = $page->isNew() ?>
<?php $admin = $sf_user->hasCredential('cms_admin') ?>

<?php // If you are making a new page you must have manage privileges. Otherwise check the existing ?>
<?php // page object for manage privileges ?>
<?php $manage = $create || $page->userHasPrivilege('manage') ?>
<?php $stem = isset($stem) ? $sf_data->getRaw('stem') : ($page->isNew() ? 'a-create-page' : 'a-page-settings') ?>

  <form method="post" action="#" name="<?php echo $stem ?>-form" id="<?php echo $stem ?>-form" class="a-ui a-options a-page-form <?php echo $stem ?>-form dropshadow">
	<div class="a-form-row a-hidden">
		<?php echo $form->renderHiddenFields() ?>
	</div>

	<?php echo $form->renderGlobalErrors() ?>

	<div class="a-options-section title-permalink open clearfix">

    <?php if (isset($form['realtitle'])): ?>
  		<h3><?php echo __('Title', array(), 'apostrophe') ?></h3>

  		<div class="a-form-row a-page-title">
  		  <?php // "Why realtitle?" To avoid excessively magic features of sfFormDoctrine. 
  		 				// There is another way but I think it might still try to initialize the field 
  						// in an unwanted fashion even if it allows them to be saved right ?>
  			<div class="a-form-field">
  				<?php echo $form['realtitle']->render(array('id' => 'a-edit-page-title', 'class' => 'a-page-title-field')) ?>
        	<?php if (isset($form['slug'])): ?>
    			  <div class="a-page-slug<?php echo ($create)? ' a-hidden':'' ?>">
    					<h4><label>http://<?php echo $_SERVER['HTTP_HOST'] ?><?php echo ($page->getSlug() == '/') ? '/':'' ?></label></h4>
    					<?php if (isset($form['slug'])): ?>
    						<div class="a-form-field">
    			    		<?php echo $form['slug']->render() ?>
    						</div>
    			    	<?php echo $form['slug']->renderError() ?>
    					<?php endif ?>
    			  </div>
  			  <?php endif ?>
  			</div>
  			<?php echo $form['realtitle']->renderError() ?>
  		</div>
  	<?php endif ?>

		<hr class="a-hr" />

    <?php if (isset($form['joinedtemplate'])): ?>
			<div class="a-form-row a-edit-page-template">
				<h4><label><?php echo a_('Page Type') ?></label></h4>
				<div class="a-form-field">
				  <?php echo $form['joinedtemplate']->render() ?>
				</div>
			</div>
		<?php endif ?>

	   <?php // This outer div is an AJAX target, it has to be here all the time ?>
	   <?php // in case the user selects an engine ?>
		<div class="a-engine-page-settings">
		  <?php if (isset($engineSettingsPartial)): ?>
		    <?php include_partial($engineSettingsPartial, array('form' => $engineForm)) ?>
		  <?php endif ?>
		</div>

    <?php if (sfConfig::get('app_a_simple_permissions')): ?>
      <div class="a-form-row status">
        <h4><label><?php echo a_('Who can see this?') ?></label></h4>
        <div class="a-form-field">
          <?php echo $form['simple_status']->render() ?>
        </div>
      </div>
    <?php else: ?>
			<div class="a-form-row status">
			  <h4><label><?php echo __('Published', null, 'apostrophe') ?></label></h4>
		  	<div class="<?php echo $stem ?>-status">
					<?php echo $form['archived'] ?>
					<?php if(isset($form['cascade_archived'])): ?>
						<div class="cascade-checkbox a-cascade-option">
							<?php echo $form['cascade_archived'] ?> <?php echo __('apply to subpages', null, 'apostrophe') ?>
						</div>
					<?php endif ?> 
				</div>
			</div>
    <?php endif ?>
	</div>
	
	
	<?php if ($create): ?>
	<a href="/#more-options" onclick="return false;" class="a-btn lite mini a-more-options-btn">More Options...</a>
	<div class="a-options-more a-hidden">
	<?php endif ?>

	<hr/>
	
	<div class="a-options-section tags-metadata a-accordion clearfix">
		<h3>Tags &amp; Metadata</h3>
		<div class="a-accordion-content">			
			<div class="a-form-row keywords">
				<div class="a-form-field">
					<?php echo $form['tags']->render() ?>
				</div>
				<?php echo $form['tags']->renderError() ?>
			</div>
			<div class="a-form-row meta-description">
				<h4 class="a-block"><?php echo $form['real_meta_description']->renderLabel(__('Meta Description', array(), 'apostrophe')) ?></h4>
				<div class="a-form-field">
					<?php echo $form['real_meta_description'] ?>
				</div>
				<?php echo $form['real_meta_description']->renderError() ?>
			</div>
		</div>
	</div>

  <?php if (!sfConfig::get('app_a_simple_permissions')): ?>
    <?php if ($manage): ?>
   		<hr/>
   		<?php $hasSubpages = $page->hasChildren(false) ?>
      <?php include_partial('a/allPrivileges', array('form' => $form, 'inherited' => $inherited, 'admin' => $admin, 'hasSubpages' => $hasSubpages)) ?>
    <?php endif ?>
  <?php endif ?>
  
	<?php if ($create): ?>
		</div>
	<?php endif ?>

	<hr/>

	<div class="a-options-section submit-settings clearfix">
		<ul class="a-ui a-controls">		
		  <li><?php echo a_anchor_submit_button(htmlspecialchars(__($page->isNew() ? 'Create Page' : 'Save Changes', null, 'apostrophe')), array('big','a-show-busy'), $stem.'-submit', $stem.'-submit') ?></li>
			<li><?php echo a_js_button(a_('Cancel'), array('icon', 'a-cancel', 'alt', 'a-options-button', 'big')) ?></li>
			<?php if ((!$page->isNew()) && $page->userHasPrivilege('delete')): ?>
				<?php $childMessage = ''; ?>
				<?php if($page->hasChildren()): ?><?php $childMessage = __("This page has children that will also be deleted. ", null, 'apostrophe'); ?><?php endif; ?>
	      <li class="a-align-right"><?php echo link_to('<span class="icon"></span>'.__("Delete This Page", null, 'apostrophe'), "a/delete?id=" . $page->getId(), array("confirm" => $childMessage . __('Are you sure? This operation can not be undone. Consider unpublishing the page instead.', null, 'apostrophe'), 'class' => 'a-btn icon a-delete alt lite', 'title' => __('Delete This Page', null, 'apostrophe'))) ?></li>
			<?php endif ?>
		</ul>
	</div>
		
</form>

<?php a_js_call('apostrophe.enablePageSettings(?)', array('id' => $stem, 'pageId' => $page->id, 'new' => $page->isNew(), 'slugStem' => $slugStem,  'url' => a_url('a', 'settings') . '?' . http_build_query($page->isNew() ? array('new' => 1, 'parent' => $parent->slug) : array('id' => $page->id)), 'slugifyUrl' => a_url('a', 'slugify'), 'engineUrl' => a_url('a', 'engineSettings'))) ?>
<?php a_js_call('apostrophe.accordion(?)', array('accordion_toggle' => '.a-options-section:not(".open") h3')) ?>
<?php a_js_call('apostrophe.radioToggleButton(?)', array('field' => '.'.$stem.'-status', 'opt1Label' => 'on', 'opt2Label' => 'off', 'debug' => false)) ?>
<?php // All AJAX actions that use a_js_call must do this since they have no layout to do it for them ?>
<?php include_partial('a/globalJavascripts') ?>
