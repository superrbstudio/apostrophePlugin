<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php // Non-CMS pages, such as search results and the media plugin, ?>
<?php // can safely include the same navigational elements and can ?>
<?php // also include global slots. ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <?php if (in_array('a', sfContext::getInstance()->getConfiguration()->getPlugins())): ?>
    <?php // Inside here it's safe to call a stuff even if some projects won't have it ?>
  	<?php use_helper('a') ?>
  	<?php $page = aTools::getCurrentPage() ?>
  <?php endif ?>
<head>
	<?php include_http_metas() ?>
	<?php include_metas() ?>
	<?php include_title() ?>
	<link rel="shortcut icon" href="/favicon.ico" />
		
</head>

<body class="<?php if (has_slot('body_class')): ?><?php include_slot('body_class') ?><?php endif ?>">

	<div id="a-media-wrapper">
		<?php // Demo requires an obvious way to test login ?>


		<h1>aMedia Plugin</h1>


		<!-- your top level navigation would be placed here -->

		<?php echo $sf_data->getRaw('sf_content') ?>

		<!-- your footer would be placed here -->
	</div>

</body>
</html>
