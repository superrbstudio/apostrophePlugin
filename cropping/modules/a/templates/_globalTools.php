<?php
/*
Global Tools
This will be the top bar across the site when logged in.
It will contain global admin buttons like Users, Page Settings, and the Breadcrumb.
These are mostly links to independent modules. 
*/
?>

<?php $buttons = aTools::getGlobalButtons() ?>
<?php $page = aTools::getCurrentPage() ?>
<?php $pageEdit = ($page && $page->userHasPrivilege('edit')) || empty($page) ?>
<?php $cmsAdmin = $sf_user->hasCredential('cms_admin') ?>

<?php use_helper('I18N') ?>

<div id="a-global-toolbar">
  <?php // All logged in users, including guests with no admin abilities, need access to the ?>
  <?php // logout button. But if you have no legitimate admin roles, you shouldn't see the ?>
  <?php // apostrophe or the global buttons ?>

  <?php if ($cmsAdmin || count($buttons) || $pageEdit): ?>

  	<?php // The Apostrophe ?>
  	<div class="a-global-toolbar-apostrophe">
  		<?php echo link_to(__('Apostrophe Now', null, 'apostrophe'),'@homepage', array('id' => 'the-apostrophe')) ?>
  		<ul class="a-global-toolbar-buttons a-controls">
	
				<?php if ($page && !$page->admin): ?>
					<li><a href="#" class="a-btn icon a-page-small" onclick="return false;" id="a-this-page-toggle"><?php echo __('This Page', null, 'apostrophe') ?></a></li>
				<?php endif ?>
  			<?php foreach ($buttons as $button): ?>
  			  <?php if ($button->getTargetEnginePage()): ?>
  			    <?php aRouteTools::pushTargetEnginePage($button->getTargetEnginePage()) ?>
  			  <?php endif ?>
  			  <li><?php echo link_to(__($button->getLabel(), null, 'apostrophe'), $button->getLink(), array('class' => 'a-btn icon ' . $button->getCssClass())) ?></li>
  			<?php endforeach ?>
  		</ul>
  	</div>

  	<div class="a-global-toolbar-user-settings a-personal-settings-container">
			<div id="a-personal-settings"></div>
    </div>

	<?php endif ?>

	<?php // Login / Logout ?>
	<div class="a-global-toolbar-login a-login">
		<?php include_partial("a/login") ?>
	</div>
		
	<?php // Administrative Breadcrumb ?>
 	<?php if ($page && (!$page->admin) && $cmsAdmin && $pageEdit): ?>
	<div class="a-global-toolbar-this-page" id="a-global-toolbar-this-page">
 		<?php include_component('a', 'breadcrumb') # Breadcrumb Navigation ?>
 		<div id="a-page-settings"></div>
	</div>
 	<?php endif ?>
  	
</div>

<?php if (aTools::isPotentialEditor()): ?>

<?php include_partial('a/historyBrowser', array('page' => $page)) ?>

<div class="a-page-overlay"></div>

<script type="text/javascript">
	$(document).ready(function() {
		var thisPageStatus = 0;
		var thisPage = $('#a-this-page-toggle');
		$('#a-global-toolbar-this-page').hide().addClass('ok');
		thisPage.click(function(){
			thisPage.toggleClass('open');
			$('#a-breadcrumb').addClass('show');
			$('#a-global-toolbar-this-page').slideToggle();
			thisPageStatus = (thisPageStatus == 1 ? 0 : 1);
			if (!thisPageStatus)
			{
				$('.a-page-overlay').hide();				
			}
		});
		<?php if ($page && ($page->getSlug() == '/')): ?>
		$('#a-breadcrumb').addClass('home-page');
		<?php endif ?>
	});
</script>

<?php endif ?>
