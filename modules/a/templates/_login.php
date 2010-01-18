<ul class="a-controls">
<?php if ($sf_user->isAuthenticated()): ?>
  
	<?php if (sfConfig::get('app_a_personal_settings_enabled', false)): ?>
	<li id="a-logged-in-as">You are logged in as <span>								
	<?php // Sets up open and close buttons, ajax loading of form ?>
	<?php echo a_remote_dialog_toggle(
	  array("id" => "a-personal-settings", 
	    "label" => $sf_user->getGuardUser()->getUsername(),
	    "loading" => "/apostrophePlugin/images/a-icon-personal-settings-ani.gif",
	    "action" => "a/personalSettings",
	    "chadFrom" => "#a-logged-in-as span",
			'hideToggle' => true,)) ?></span>
	</li>
	<?php else: ?>
		<li id="a-logged-in-as">You are logged in as <span><?php echo $sf_user->getGuardUser()->getUsername() ?></span></li>									
	<?php endif ?>
	
  <li><?php echo link_to("Log Out", sfConfig::get('app_a_actions_logout', 'sfGuardAuth/signout'), array('class' => 'a-btn', )) ?></li>
<?php else: ?>
  <?php // You can easily turn off the 'Log In' link via app.yml ?>
  <?php if (sfConfig::get('app_a_login_link', true)): ?>
    <li><?php echo link_to("Log In", sfConfig::get('app_a_actions_login', 'sfGuardAuth/signin'), array('class' => 'a-btn', )) ?></li>
  <?php endif ?>
<?php endif ?>
</ul>
