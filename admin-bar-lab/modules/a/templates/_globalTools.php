<?php use_helper('I18N') ?>

<?php $buttons = aTools::getGlobalButtons() ?>
<?php $page = aTools::getCurrentPage() ?>
<?php $pageEdit = ($page && $page->userHasPrivilege('edit')) || empty($page) ?>
<?php $cmsAdmin = $sf_user->hasCredential('cms_admin') ?>

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

  			<?php foreach ($buttons as $button): ?>
  			  <?php if ($button->getTargetEnginePage()): ?>
  			    <?php aRouteTools::pushTargetEnginePage($button->getTargetEnginePage()) ?>
  			  <?php endif ?>
  			  <li><?php echo link_to(__($button->getLabel(), null, 'apostrophe'), $button->getLink(), array('class' => 'a-btn icon ' . $button->getCssClass())) ?></li>
  			<?php endforeach ?>

		  	<?php if ($page && (!$page->admin) && $cmsAdmin && $pageEdit): ?>			
					<li>
						<a href="#" onclick="return false;" class="a-btn icon a-page-settings" id="a-page-settings-button">Page Settings</a>			
				 		<div id="a-page-settings" class="a-page-settings-menu dropshadow"></div>
					</li>				

					<?php // Remove the Add Page Button if we have reached our max depth, max peers, or if it is an engine page ?>
					<?php $maxPageLevels = (sfConfig::get('app_a_max_page_levels'))? sfConfig::get('app_a_max_page_levels') : 0; ?><?php // Your Site Tree can only get so deep ?>
					<?php $maxChildPages = (sfConfig::get('app_a_max_children_per_page'))? sfConfig::get('app_a_max_children_per_page') : 0; ?><?php // Your Site Tree can only get so wide ?>
					<?php if (!(($maxPageLevels && ($page->getLevel() == $maxPageLevels)) || ($maxChildPages && (count($page->getChildren()) == $maxChildPages)) || strlen($page->getEngine()))): ?>
						<li><?php include_component('a', 'createPage', array('page' => $page, 'edit' => $page->userHasPrivilege('edit'))); ?></li>
					<?php endif ?>

				<?php endif ?>
  		</ul>
  	</div>

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
				
		aMenuToggle('#a-page-settings-button', $('#a-page-settings-button').parent(), '', true);
		
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
		});
		
	});
</script>
<?php endif ?>