<?php use_helper('a') ?>
<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
?>

<?php use_helper('a') ?>

<ul class="a-slot-info a-feed-info">
	<li><?php echo a_('Paste an RSS feed URL, a Twitter @name (with the @), or the URL of a page that offers a feed. Most blogs do.') ?></li>
</ul>  
<?php // Just echo the form. You might want to render the form fields differently ?>
<?php echo $form ?>