<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
  $item = isset($item) ? $sf_data->getRaw('item') : null;
  $serviceError = isset($serviceError) ? $sf_data->getRaw('serviceError') : null;
	$i = 0;
	$submitSelector = $item ? ('#' . $item->getSlug() . '-submit') : '.a-media-multiple-submit-button';	
?>
<?php use_helper('a') ?>

<?php slot('body_class') ?>a-media a-media-upload video<?php end_slot() ?>

<div class="a-media-library">

  <?php include_component('aMedia', 'browser') ?>

  <div class="a-media-toolbar">
    <h3>
  		<?php if ($item): ?> 
  			<?php echo __('Editing Video: %title%', array('%title%' => $item->getTitle()), 'apostrophe') ?>
      <?php else: ?> 
  			<?php echo __('Add Video', null, 'apostrophe') ?> 
  		<?php endif ?>
     </h3>
  </div>

  <div class="a-media-items a-media-edit-video">				
	  <?php include_partial('aMedia/edit', array('item' => $item, 'form' => $form, 'popularTags' => $popularTags, 'allTags' => $allTags, 'formAction' => a_url('aMedia', 'editVideo'), 'editVideoSuccess' => true)) ?>		
  </div>

</div>

<?php a_js_call('apostrophe.mediaEnableUploadMultiple()') ?>
