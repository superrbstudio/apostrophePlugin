<?php use_helper('a') ?>

<?php $buttons = aTools::getGlobalButtons() ?>
<?php $page = aTools::getCurrentPage() ?>
<?php $pageEdit = ($page && $page->userHasPrivilege('edit')) || empty($page) ?>
<?php $cmsAdmin = $sf_user->hasCredential('cms_admin') ?>
<?php $thisPage = $page && (!$page->admin) && ($cmsAdmin || $pageEdit) ?> 
<?php $maxPageLevels = (sfConfig::get('app_a_max_page_levels'))? sfConfig::get('app_a_max_page_levels') : 0; ?><?php // Your Site Tree can only get so deep ?>
<?php $maxChildPages = (sfConfig::get('app_a_max_children_per_page'))? sfConfig::get('app_a_max_children_per_page') : 0; ?><?php // Your Site Tree can only get so wide ?>

<?php if ($cmsAdmin || count($buttons) || $pageEdit || $sf_user->isAuthenticated() || sfConfig::get('app_a_persistent_global_toolbar', true)): ?> 
	
<div class="a-ui a-global-toolbar">
	<ul class="a-ui a-controls">

	  <?php if ($cmsAdmin || count($buttons) || $pageEdit): ?>

			<li>
				<?php echo link_to(__('Apostrophe Now', null, 'apostrophe'),'@homepage', array('class' => 'the-apostrophe')) ?>
			</li>

			<?php if ($thisPage): ?> 	
			<li>
				<a href="#" onclick="return false;" class="a-btn icon a-page-settings" id="a-page-settings-button"><span class="icon"></span>Page Settings</a>
				<div id="a-page-settings" class="a-page-settings-menu dropshadow"></div>
			</li>				
			<?php endif ?>

			<?php foreach ($buttons as $button): ?>
				<li>
					<?php if ($button->getTargetEnginePage()): ?><?php aRouteTools::pushTargetEnginePage($button->getTargetEnginePage(), $button->getTargetEngine()) ?><?php endif ?>
					<?php echo link_to('<span class="icon"></span>'.__($button->getLabel(), null, 'apostrophe'), $button->getLink(), array('class' => 'a-btn icon ' . $button->getCssClass())) ?>
				</li>
			<?php endforeach ?>

      <?php // You need manage privileges to create a subpage ?>
			<?php if ($thisPage && $page->userHasPrivilege('manage')): ?> 
				<?php // Remove the Add Page Button if we have reached our max depth, max peers, or if it is an engine page ?>
				<?php if (!(($maxPageLevels && ($page->getLevel() == $maxPageLevels)) || ($maxChildPages && (count($page->getChildren()) == $maxChildPages)) || strlen($page->getEngine()))): ?>
					<li>
					  <?php // Triggers the same form as page settings now ?>
					  <a href="#add-page" class="a-btn icon a-add a-create-page" id="a-create-page-button"><span class="icon"></span><?php echo __("Add Page", null, 'apostrophe') ?></a>
					  <div id="a-create-page" class="a-page-settings-menu dropshadow"></div>
					</li>
				<?php endif ?>
			<?php endif ?>
			
		<?php endif ?>

		<li class="a-login">
			<?php include_partial("a/login") ?>
		</li>		

	</ul>
</div>
<?php endif ?>

<?php if (aTools::isPotentialEditor()): ?>
	<?php include_partial('a/historyBrowser') ?>
	<div class="a-page-overlay"></div>
	<?php if ($page): ?>
		<?php a_js_call('apostrophe.enablePageSettingsButtons(?)', array('aPageSettingsURL' => url_for('a/settings') . '?' . http_build_query(array('id' => $page->id)), 'aPageSettingsCreateURL' => url_for('a/settings') . '?' . http_build_query(array('new' => 1, 'parent' => $page->slug)))) ?>
	<?php endif ?>	
<?php endif ?>