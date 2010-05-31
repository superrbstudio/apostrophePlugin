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

  <?php $buttons = aTools::getGlobalButtons() ?>
  <?php $page = aTools::getCurrentPage() ?>
  <?php $pageEdit = $page && $page->userHasPrivilege('edit') ?>
  <?php $cmsAdmin = $sf_user->hasCredential('cms_admin') ?>

  <?php if ($cmsAdmin || count($buttons) || $pageEdit): ?>

  	<?php // The Apostrophe ?>
  	<div class="a-global-toolbar-apostrophe">
  		<?php echo link_to(__('Apostrophe Now', null, 'apostrophe'),'@homepage', array('id' => 'the-apostrophe')) ?>
  		<ul class="a-global-toolbar-buttons a-controls">

				<?php if (0): ?>
					<?php if ($page && !$page->admin): ?>
						<li><a href="#" class="a-btn icon a-page-small" onclick="return false;" id="a-this-page-toggle"><?php echo __('This Page', null, 'apostrophe') ?></a></li>
					<?php endif ?>
				<?php endif ?>	

  			<?php foreach ($buttons as $button): ?>
  			  <?php if ($button->getTargetEnginePage()): ?>
  			    <?php aRouteTools::pushTargetEnginePage($button->getTargetEnginePage()) ?>
  			  <?php endif ?>
  			  <li><?php echo link_to(__($button->getLabel(), null, 'apostrophe'), $button->getLink(), array('class' => 'a-btn icon ' . $button->getCssClass())) ?></li>
  			<?php endforeach ?>

		  	<?php if ($page && (!$page->admin) && $cmsAdmin && $pageEdit): ?>			
					<li>
						<a href="#" onclick="return false;" class="a-btn icon a-page-settings" id="a-page-settings-button">Page Settings</a>			
				 		<div id="a-page-settings" class="dropshadow"></div>
					</li>				
					<li><?php include_component('a', 'createPage', array('page' => $page, 'edit' => $page->userHasPrivilege('edit'))); ?></li>
				<?php endif ?>
  		</ul>
  	</div>

		<?php if (0): ?>
  	<?php // user profile container for a feature we haven't built / and aren't using yet ?>
			<div class="a-global-toolbar-user-settings a-personal-settings-container">
			<div id="a-personal-settings"></div>
    </div>
		<?php endif ?>

	<?php endif ?>

		<?php // Login / Logout ?>
		<div class="a-global-toolbar-login a-login">
			<?php include_partial("a/login") ?>
		</div>
		
  	
</div>

<?php if (aTools::isPotentialEditor()): ?>

<div class="a-page-overlay"></div>

<script type="text/javascript">
	$(document).ready(function() {
				
		aMenuToggle('#a-page-settings-button', '#a-page-settings', 'open', true);
		
		var aPageSettingsButton = $('#a-page-settings-button');
		
		aPageSettingsButton.click(function() {
		 $.ajax({
				type:'POST',
				dataType:'html',
				success:function(data, textStatus){
					$('#a-page-settings').html(data);
				},
				complete:function(XMLHttpRequest, textStatus){
					aUI('#a-page-settings');
				},
				url:'/admin/a/settings/id/<?php echo $page->id; ?>'
			});
			
		  var pos = aPageSettingsButton.offset();  
		  var width = aPageSettingsButton.width();
			  //show the menu directly over the placeholder
		  // $("#a-page-settings").css( { "left": pos.left-5 + "px", "top":pos.top-5 + "px" } );			
			
		});
	});
</script>

<?php endif ?>