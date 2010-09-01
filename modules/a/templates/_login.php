<ul class="a-login">
<?php if ($sf_user->isAuthenticated()): ?>
	<li class="a-logged-in-user"><?php echo __('You are logged in as', null, 'apostrophe') ?> <span><?php echo $sf_user->getGuardUser()->getUsername() ?></span></li>									
	<?php include_partial('a/language') ?>
	
  <li><?php echo link_to(__('Log Out', null, 'apostrophe'), sfConfig::get('app_a_actions_logout', 'sfGuardAuth/signout'), array('class' => 'a-btn', )) ?></li>
<?php else: ?>
	<?php include_partial('a/language') ?>
  <?php // You can easily turn off the 'Log In' link via app.yml ?>
  <?php if (sfConfig::get('app_a_login_link', true)): ?>
    <li>
			<?php echo jq_link_to_function(__('Login', null, 'apostrophe'), "$('#a-login-form-container').fadeIn(); $('#signin_username').focus(); $('.a-page-overlay').fadeIn('fast');", array('class' => 'a-btn','id' => 'a-login-button')) ?>	
			<div id="a-login-form-container" class="dropshadow a-options">
				<?php include_component('a','signinForm') ?>
			</div>
		</li>
  <?php endif ?>
<?php endif ?>
</ul>