<?php // Calling include_stylesheets and include_javascripts here was never a good idea and ?>
<?php // never worked for Apostrophe's CSS and JS, which didn't matter because you had them ?>
<?php // preloaded in the page anyway. If you really want to load more JS and CSS on the fly, ?>
<?php // you will have to do it explicitly, but we recommend doing it when you load the page. ?>
<?php use_helper('a') ?>
<?php a_slot_body($name, $type, $permid, $options, $validationData, $editorOpen, true) ?>

<?php if (isset($variant)): ?>
	<script type="text/javascript" charset="utf-8">
		$(document).ready(function() {
			$('<?php echo "#a-$pageid-$name-$permid-variant ul.a-variant-options" ?>').removeClass('loading').fadeOut('slow').parent().removeClass('open');
		});
  </script>
<?php endif ?>