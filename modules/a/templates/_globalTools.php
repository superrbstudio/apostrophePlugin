<?php
/*
Global Tools
This will be the top bar across the site when logged in.

It will contain global admin buttons like Users, Page Settings, and the Breadcrumb.

These are mostly links to independent modules. 
*/
?>

<ul id="a-global-toolbar">
  <?php // All logged in users, including guests with no admin abilities, need access to the ?>
  <?php // logout button. But if you have no legitimate admin roles, you shouldn't see the ?>
  <?php // apostrophe or the global buttons ?>

  <?php $buttons = aTools::getGlobalButtons() ?>
  <?php $page = aTools::getCurrentPage() ?>
  <?php $pageEdit = $page && $page->userHasPrivilege('edit') ?>
  <?php $cmsAdmin = $sf_user->hasCredential('cms_admin') ?>

  <?php if ($cmsAdmin || count($buttons) || $pageEdit): ?>

  	<?php // The Apostrophe ?>
  	<li class="a-global-toolbar-apostrophe">
  		<?php echo jq_link_to_function('Apostrophe Now','',array('id' => 'the-apostrophe', )) ?>
  		<ul class="a-global-toolbar-buttons a-controls">
  			<?php $buttons = aTools::getGlobalButtons() ?>
  			<?php foreach ($buttons as $button): ?>
  			  <?php if ($button->getTargetEnginePage()): ?>
  			    <?php aRouteTools::pushTargetEnginePage($button->getTargetEnginePage()) ?>
  			  <?php endif ?>
  			  <li><?php echo link_to($button->getLabel(), $button->getLink(), array('class' => 'a-btn icon ' . $button->getCssClass())) ?></li>
  			<?php endforeach ?>
  			<li><?php echo jq_link_to_function('Cancel','',array('class' => 'a-btn icon a-cancel', )) ?></li>					
  		</ul>
  	</li>

  	<?php // Administrative breadcrumb ?>
  	<?php if ($page && (!$page->admin)): ?>
	  	<li class="a-global-toolbar-breadcrumb">
	  		<?php include_component('a', 'breadcrumb') # Breadcrumb Navigation ?>
	  	</li>
  	<?php endif ?>

  	<li class="a-global-toolbar-page-settings a-page-settings-container">
  		<div id="a-page-settings"></div>
  	</li>

  	<li class="a-global-toolbar-user-settings a-personal-settings-container">
			<div id="a-personal-settings"></div>
    </li>

	<?php endif ?>

		<?php // Login / Logout ?>
		<li class="a-global-toolbar-login a-login">
			<?php include_partial("a/login") ?>
		</li>
</ul>

<script type="text/javascript">

	$(document).ready(function(){

		var aposToggle = 0;

	  $('#the-apostrophe').click(function(){
		
			if (!aposToggle)
			{
				$(this).addClass('open');
				$('.a-global-toolbar-breadcrumb').hide();
				$('.a-global-toolbar-buttons').fadeIn();
				$('.a-global-toolbar-buttons .a-cancel').fadeIn();			
				$('.a-global-toolbar-buttons .a-cancel').parent().show();
				aposToggle = 1;
			}
			else
			{
				closeApostrophe();				
				aposToggle = 0;
			}

		});
  
		$('.a-global-toolbar-apostrophe .a-cancel').click(function(){
			closeApostrophe();
			aposToggle = 0;
	  });      

		function closeApostrophe()
		{
			$('#the-apostrophe').removeClass('open');
			$('.a-global-toolbar-buttons').hide();			
			$('.a-global-toolbar-breadcrumb').fadeIn();
		}

	});
</script>

<?php if (aTools::getCurrentPage()): ?>
	<?php include_partial('a/historyBrowser', array('page' => $page)) ?>
<?php endif ?>

<div class="a-page-overlay"></div>

