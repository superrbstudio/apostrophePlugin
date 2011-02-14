<?php use_helper('a') ?>

<?php $page = aTools::getCurrentNonAdminPage() ?>
	
<?php if (has_slot('a-footer')): ?>
  <?php include_slot('a-footer') ?>
<?php else: ?>
  <?php a_slot('footer', 'aRichText', array(
		'global' => true,
		'edit' => (isset($page) && $sf_user->hasCredential('cms_admin')) ? true : false,
	)) ?>
<?php endif ?>

<?php // Feel free to shut these off in app.yml or override the footer partial in your app ?>
<?php if (sfConfig::get('app_a_credit', true)): ?>
  <div class="a-attribution">Built with <a href="http://www.apostrophenow.com/">Apostrophe</a></div>
<?php endif ?>

<?php if (sfConfig::get('app_a_servergroveCredit', false)): ?>
	<div class="a-servergrove-attribution">
  	<?php echo link_to(image_tag('/images/sg160x40_g.png'), 'http://www.servergrove.com/', array('title' => 'We recommend ServerGrove Symfony hosting')) ?>
	</div>
<?php endif ?>
