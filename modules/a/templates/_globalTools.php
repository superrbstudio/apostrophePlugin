<?php use_helper('a') ?>

<?php $page = aTools::getCurrentPage() ?>
<?php $pageEdit = ($page && $page->userHasPrivilege('edit')) || empty($page) ?>
<?php $cmsAdmin = $sf_user->hasCredential('cms_admin') ?>
<?php $pageSettings = $page && (!$page->admin) && ($cmsAdmin || $pageEdit) ?> 
<?php $maxPageLevels = (sfConfig::get('app_a_max_page_levels'))? sfConfig::get('app_a_max_page_levels') : 0; ?><?php // Your Site Tree can only get so deep ?>
<?php $maxChildPages = (sfConfig::get('app_a_max_children_per_page'))? sfConfig::get('app_a_max_children_per_page') : 0; ?><?php // Your Site Tree can only get so wide ?>

<?php // Remove the Add Page Button if we have reached our max depth, max peers, or if it is an engine page, ?>
<?php // or we don't have the privs in the first place ?>

<?php $addPage = $page && (!(($maxPageLevels && ($page->getLevel() == $maxPageLevels)) || ($maxChildPages && (count($page->getChildren()) == $maxChildPages)) || strlen($page->getEngine()))) && $page->userHasPrivilege('manage') ?>

<?php if ($sf_user->isAuthenticated() || sfConfig::get('app_a_persistent_global_toolbar', true)): ?> 
	
  <div class="a-ui a-global-toolbar">
  	<ul class="a-ui a-controls">

      <?php include_partial('a/apostrophe') ?>

      <?php $buttons = aTools::getGlobalButtonsByName() ?>

  	  <?php if ($cmsAdmin || count($buttons) || $pageEdit): ?>
        
        <?php include_partial('a/globalButtons', array('pageSettings' => $pageSettings, 'addPage' => $addPage)) ?>
			
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