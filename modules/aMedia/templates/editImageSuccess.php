<?php use_helper('I18N', 'jQuery') ?>

<?php slot('body_class') ?>a-media<?php end_slot() ?>

<div id="a-media-plugin">
	
	<?php include_component('aMedia', 'browser') ?>

	<div class="a-media-toolbar">
		<h3><?php echo __('You are editing: %title%', array('%title%' => $item->getTitle()), 'apostrophe') ?></h3>
	</div>

  <?php if ($postMaxSizeExceeded): ?>
  <h3><?php echo __('File too large. Limit is %POSTMAXSIZE%', array('%POSTMAXSIZE%' => ini_get('post_max_size')), 'apostrophe') ?></h3>
  <?php endif ?>

	<div class="a-media-library">			
	<?php include_partial('aMedia/editImage', array('item' => $item, 'firstPass' => false, 'form' => $form)) ?>		
	</div>

</div>