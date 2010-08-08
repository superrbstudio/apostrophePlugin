<?php
  // Compatible with sf_escaping_strategy: true
  $editorOpen = isset($editorOpen) ? $sf_data->getRaw('editorOpen') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
  $pageid = isset($pageid) ? $sf_data->getRaw('pageid') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $type = isset($type) ? $sf_data->getRaw('type') : null;
  $validationData = isset($validationData) ? $sf_data->getRaw('validationData') : null;
  $variant = isset($variant) ? $sf_data->getRaw('variant') : null;
?>
<?php // 1.3 and up don't do this automatically (no common filter) ?>
<?php // We're using renderPartial so there is no layout to call this for us ?>
<?php include_javascripts() ?>
<?php include_stylesheets() ?>
<?php use_helper('a') ?>
<?php a_slot_body($name, $type, $permid, $options, $validationData, $editorOpen, true) ?>

<?php if (isset($variant)): ?>
	<script type="text/javascript" charset="utf-8">
		$(document).ready(function() {
			$('<?php echo "#a-$pageid-$name-$permid-variant ul.a-variant-options" ?>').removeClass('loading').fadeOut('slow').parent().removeClass('open');
		});
  </script>
<?php endif ?>