<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
  $item = isset($item) ? $sf_data->getRaw('item') : null;
?>
<?php use_helper('I18N', 'jQuery', 'a') ?>

<?php slot('body_class') ?>a-media<?php end_slot() ?>

<div class="a-media-library">
	
	<?php include_component('aMedia', 'browser') ?>

	<div class="a-media-toolbar">
		<h3><?php echo __('You are editing: %title%', array('%title%' => $item->getTitle()), 'apostrophe') ?></h3>
	</div>

	<div class="a-media-items">			
	<?php include_partial('aMedia/edit', array('item' => $item, 'form' => $form)) ?>		
	</div>
</div>