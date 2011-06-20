<?php use_helper('a') ?>

<?php $page = aTools::getCurrentNonAdminPage() ?>
	
<?php a_slot('footer', 'aRichText', array(
	'global' => true,
	'edit' => (isset($page) && $sf_user->hasCredential('cms_admin')) ? true : false,
)) ?>

<?php // Feel free to shut these off in app.yml or override the footer partial in your app ?>
<?php if (sfConfig::get('app_a_credit', true)): ?>
  <div class="a-attribution apostrophe">Built with <a href="http://www.apostrophenow.com/">Apostrophe</a></div>
<?php endif ?>

<?php if (sfConfig::get('app_a_servergroveCredit', false)): ?>
	<div class="a-attribution servergrove">
  	<?php echo link_to(image_tag('/images/sg80x20_g.png'), 'https://secure.servergrove.com/clients/aff.php?aff=037', array('title' => 'Hosted by ServerGrove')) ?>
		<p class="a-help">Hosted by ServerGrove</p>
	</div>
<?php endif ?>
