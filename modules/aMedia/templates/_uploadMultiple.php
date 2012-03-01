<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
?>
<?php use_helper('a') ?>
<div class="a-media-add-subheading a-media-add-upload">
  <?php // This is important because sometimes you are selecting specific media types ?>
  <?php $typeLabel = aMediaTools::getBestTypeLabel() ?>
  <?php include_partial('aMedia/uploadMultipleHeader')?>

  <?php // This is present if we have a second pass due to upload failure ?>
  <?php if ($sf_user->getFlash('aMedia.postMaxSizeExceeded')): ?>
  <h4><?php echo __('File too large. Limit is %POSTMAXSIZE%', array('%POSTMAXSIZE%' => ini_get('post_max_size')), 'apostrophe') ?></h4>
  <?php endif ?>

  <?php // Error message if they don't select anything at all ?>
  <?php if ($sf_user->getFlash('aMedia.mustUploadSomething')): ?>
  <h4>You must select a file to upload with the Browse Files button.</h4>
  <?php endif ?>

  <?php echo $form->renderGlobalErrors() ?>

  <form method="post" action="<?php echo a_url('aMedia', 'upload') ?>" enctype="multipart/form-data" id="a-media-upload-form">
	  <div class="a-form-row a-hidden">
	  	<?php echo $form->renderHiddenFields() ?>
	  </div>

	  <?php // I use this in js code, don't kill it please, style it if you want ?>
	  <div id="a-media-upload-form-subforms">
	    <?php for ($i = 0; ($i < aMediaTools::getOption('batch_max')); $i++): ?>
	      <?php // What we're passing here is actually a widget schema ?>
	      <?php // (they get nested when embedded forms are present), but ?>
	      <?php // it supports the same methods as a form for rendering purposes ?>
	      <?php include_partial('aMedia/upload', array("form" => $form["item-$i"], "first" => ($i == 0))) ?>
	    <?php endfor ?>
	  </div>

	  <ul class="a-ui a-controls a-upload-multiple-controls">
	  	<li><a href="#" id="a-media-add-photo" class="a-btn icon a-add lite alt"><span class="icon"></span><?php echo a_('Add Multiple Files') ?></a></li>
	  </ul>

	  <ul class="a-ui a-controls">
	  	<li><?php echo a_anchor_submit_button(a_('Upload ' . aMediaTools::getBestTypeLabel()), array('big','a-show-busy')) ?></li>
	  	<li><?php echo a_js_button(a_('Cancel'), array('icon', 'a-cancel', 'big', 'alt')) ?></li>
	  </ul>
  </form>

  <?php // Elements get moved here by jQuery when they are not in use. ?>
  <?php // This form is never submitted so file upload elements that are ?>
  <?php // in it are never uploaded. ?>
  <form style="display:none;" enctype="multipart/form-data" id="a-media-upload-form-inactive" action="#" ></form>
  <?php include_partial('aMedia/afterUploadMultiple') ?>
</div>

<?php a_js_call('apostrophe.mediaEnableUploadMultiple()') ?>
