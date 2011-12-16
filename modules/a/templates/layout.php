<!doctype html>
<?php use_helper('a') ?>
<?php $page = aTools::getCurrentNonAdminPage() ?>
<?php $realPage = aTools::getCurrentPage() ?>
<?php $root = aPageTable::retrieveBySlug('/') ?>
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
<head>
	<meta charset="UTF-8">
	<?php include_http_metas() ?>
	<?php include_metas() ?>

	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="shortcut icon" href="/favicon.ico">
	<link rel="apple-touch-icon" href="/apple-touch-icon.png">

	<?php include_title() ?>
  <?php a_include_stylesheets() ?>
	<?php a_include_javascripts() ?>

	<?php if (has_slot('og-meta')): ?>
		<?php include_slot('og-meta') ?>
	<?php endif ?>

	<?php if ($fb_page_id = sfConfig::get('app_a_facebook_page_id')): ?>
		<meta property="fb:page_id" content="<?php echo $fb_page_id ?>" />
	<?php endif ?>

	<link rel="shortcut icon" href="/favicon.ico" />

	<!--[if lt IE 9]>
  	<link rel="stylesheet" type="text/css" href="/apostrophePlugin/css/a-ie.css" />	
  	<link rel="stylesheet" type="text/css" href="/css/ie6.css" />		
		<script type="text/javascript">
			$(document).ready(function() {
				apostrophe.IE6({'authenticated':<?php echo ($sf_user->isAuthenticated())? 'true':'false' ?>, 'message':<?php echo json_encode(__('You are using IE6! That is just awful! Apostrophe does not support editing using Internet Explorer 6. Why don\'t you try upgrading? <a href="http://www.getfirefox.com">Firefox</a> <a href="http://www.google.com/chrome">Chrome</a> 	<a href="http://www.apple.com/safari/download/">Safari</a> <a href="http://www.microsoft.com/windows/internet-explorer/worldwide-sites.aspx">IE8</a>', null, 'apostrophe')) ?>});
			});
		</script>
	<![endif]-->
	
</head>

<?php // a-body-class allows you to set a class for the body element from a template ?>
<?php // body_class is preserved here for backwards compatibility ?>

<?php $a_bodyclass = '' ?>
<?php $a_bodyclass .= (has_slot('a-body-class')) ? get_slot('a-body-class') : '' ?>
<?php $a_bodyclass .= (has_slot('body_class')) ? get_slot('body_class') : '' ?>
<?php $a_bodyclass .= ($page && $page->archived) ? ' a-page-unpublished' : '' ?> 
<?php $a_bodyclass .= ($page && $page->view_is_secure) ? ' a-page-secure' : '' ?> 
<?php $a_bodyclass .= ($page) ? ' a-page-id-'.$page->id.' a-page-depth-'.$page->level : '' ?>
<?php $a_bodyclass .= (sfConfig::get('app_a_js_debug', false)) ? ' js-debug':'' ?>
<?php $a_bodyclass .= ($realPage && !is_null($realPage['engine'])) ? ' a-engine':'' ?>
<?php $a_bodyclass .= ($sf_user->isAuthenticated()) ? ' logged-in':' logged-out' ?>

<body class="<?php echo $a_bodyclass ?>">

	<?php include_partial('a/doNotEdit') ?>
  <?php include_partial('a/globalTools') ?>

	<div class="a-wrapper clearfix">

    <?php // Note that just about everything can be suppressed or replaced by setting a ?>
    <?php // Symfony slot. Use them - don't write zillions of layouts or do layout stuff ?>
    <?php // in the template (except by setting a slot). To suppress one of these slots ?>
    <?php // completely in one line of code, just do: slot('a-whichever', '') ?>

    <?php if (has_slot('a-search')): ?>
      <?php include_slot('a-search') ?>
    <?php else: ?>
      <?php include_partial('a/search') ?>
    <?php endif ?>

    <div class="a-header clearfix">
	    <?php if (has_slot('a-header')): ?>
	      <?php include_slot('a-header') ?>
	    <?php else: ?>
	        <?php if (has_slot('a-logo')): ?>
	          <?php include_slot('a-logo') ?>
	        <?php else: ?>
	          <?php a_slot('logo', 'aButton', array(
							'edit' => (isset($page) && $sf_user->hasCredential('cms_admin')) ? true : false,
							'history' => false, 
							'defaultImage' => '/apostrophePlugin/images/asandbox-logo.png',
							'link' => url_for('@homepage'),
							'global' => true,
							'width' => 360,
							'flexHeight' => true,
							'resizeType' => 's',
						)) ?>
	        <?php endif ?>
	    <?php endif ?>
  	</div>

		<?php if (has_slot('a-tabs')): ?>
			<?php include_slot('a-tabs') ?>
		<?php else: ?>
			<?php include_component('aNavigation', 'tabs', array('root' => $root, 'active' => $page, 'name' => 'main', 'draggable' => true, 'dragIcon' => false)) # Top Level Navigation ?>
		<?php endif ?>

		<?php if (has_slot('a-breadcrumb')): ?>
			<?php include_slot('a-breadcrumb') ?>
		<?php elseif ($page): ?>
			<?php include_component('aNavigation', 'breadcrumb', array('root' => $root, 'active' => $page, 'name' => 'component', 'separator' => ' /')) # Top Level Navigation ?>
		<?php endif ?>

    <?php if (has_slot('a-page-header')): ?>
			<?php include_slot('a-page-header') ?>
 		<?php endif ?>

		<?php if (has_slot('a-subnav')): ?>
			<?php include_slot('a-subnav') ?>
		<?php elseif ($page): ?>
			<?php include_component('a', 'subnav', array('page' => $page)) # Subnavigation ?>
		<?php endif ?>

		<div class="a-content clearfix">
			<?php echo $sf_data->getRaw('sf_content') ?>
		</div>

	</div>

	<?php if (has_slot('a-footer')): ?>
	  <?php include_slot('a-footer') ?>
	<?php else: ?>
		<div class='a-footer-wrapper clearfix'>
			<div class='a-footer clearfix'>
	  	  <?php include_partial('a/footer') ?>
			</div>
		</div>
	<?php endif ?>	

	<?php include_partial('a/googleAnalytics') ?>

  <?php // Invokes apostrophe.smartCSS, your project level JS hook and a_include_js_calls ?>
	<?php include_partial('a/globalJavascripts') ?>

</body>
</html>