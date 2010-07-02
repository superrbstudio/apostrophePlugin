<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php // iframe layout for media plugin, used for forms containing files ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<?php include_http_metas() ?>
	<?php include_metas() ?>
	<?php include_title() ?>
	<link rel="shortcut icon" href="/favicon.ico" />
	
	<?php echo get_stylesheets() ?>
	<?php echo get_javascripts() ?>
</head>

<body class="<?php include_slot('body_class')?> iframe">
		<?php echo $sf_data->getRaw('sf_content') ?>
</body>
</html>
