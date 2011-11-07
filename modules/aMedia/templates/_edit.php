<?php
  // Compatible with sf_escaping_strategy: true
  $firstPass = isset($firstPass) ? $sf_data->getRaw('firstPass') : null;
  $form = isset($form) ? $sf_data->getRaw('form') : null;
  $i = isset($i) ? $sf_data->getRaw('i') : null;
  $item = isset($item) ? $sf_data->getRaw('item') : null;
  $itemFormScripts = isset($itemFormScripts) ? $sf_data->getRaw('itemFormScripts') : null;
  $n = isset($n) ? $sf_data->getRaw('n') : null;
  $withPreview = isset($withPreview) ? $sf_data->getRaw('withPreview') : true;
	$popularTags = isset($popularTags) ? $sf_data->getRaw('popularTags') : array();
	$allTags = isset($allTags) ? $sf_data->getRaw('allTags') : array();
	// There is just one edit form for new uploads, even in bulk
	$submitSelector = $item ? ('#' . $item->getSlug() . '-submit') : '.a-media-multiple-submit-button';
	$single = isset($single) ? $sf_data->getRaw('single') : false;
	$formAction = isset($formAction) ? $sf_data->getRaw('formAction') : null;
	$editVideoSuccess = isset($editVideoSuccess) ? $sf_data->getRaw('editVideoSuccess') : null;
?>

<?php // Oops: with embedded forms you are not allowed to unset widgets. Didn't bite us in the ?>
<?php // one-file case. Lovely. Work around it by using flags instead ?>
<?php $fileShown = false ?>
<?php $embedShown = false ?>

<?php if (!isset($form['file'])): ?>
  <?php $withPreview = false ?>
<?php endif ?>

<?php $embeddable = ($form instanceof BaseaMediaVideoForm) || ($item && $item->getEmbeddable()) ?>

<?php use_helper('a') ?>

<?php // Sometimes this is one item in a list of several in embedded forms, in ?>
<?php // which case $n is our ordinal in the list and $i is an acceptably unique suffix ?>
<?php // to distinguish this particular item (its ID). When we're editing just one item we use ?>
<?php // 0 and 'single' instead. ?>

<?php if (!isset($n)): ?> 
  <?php $n = 0 ?> 
  <?php $single = true ?>
<?php else: ?>
  <?php $single = false ?>
<?php endif ?>
<?php if (!isset($i)): ?> 
  <?php $i = 'single' ?> 
<?php endif ?>

<?php // From here on in we can use $single to check when we need to know whether ?>
<?php // we're dealing with a single item (like editing an existing item or annotating ?>
<?php // an embedded media upload) or we're just one of many. Test $item to determine ?>
<?php // if the single item has already been saved in the past and we're just editing ?>

<?php if ($single): ?>	
  <?php // Editing one item, existing or otherwise ?>
  <form method="post" class="a-ui a-media-edit-form" id="a-media-edit-form-<?php echo $i ?>" enctype="multipart/form-data" action="<?php echo $formAction ?>">
		<?php if ($editVideoSuccess): ?>
			<div class="a-media-editor a-even last">
				<div class="a-ui a-media-item a-media-edit-form even">
		<?php endif ?>
<?php else: ?>
  <?php // This is one of several items in a larger form ?>
  <div class="a-ui a-media-item a-media-edit-form <?php echo ($n%2) ? "odd" : "even" ?>" id="a-media-item-<?php echo $i ?>">
<?php endif ?>

<?php // Prepare to render an embed code even though this object hasn't been saved yet ?>
<?php $embedValues = array() ?>

<?php // Preview of image and/or embed code. We do not need this when we are editing an existing item via AJAX ?>
<?php // because a preview is on the screen ?>
<?php if (!$item): ?>
  <?php if (isset($form['embed']) && (!$form['embed']->hasError()) && strlen($form['embed']->getValue())): ?>
    <?php $embedValues['embed'] = $form['embed']->getValue() ?>
  <?php elseif (isset($form['service_url']) && strlen($form['service_url']->getValue())): ?>
    <?php $embedValues['service_url'] = $form['service_url']->getValue() ?>
  <?php endif ?>
  <?php if (count($embedValues)): ?>
    <?php $form->updateObject($embedValues) ?>
    <?php $constraints = aMediaTools::getOption('gallery_constraints') ?>
    <?php $width = $constraints['width'] ?>
    <?php $height = $constraints['height'] ?>
    <?php $embedCode = $form->getObject()->getEmbedCode($width, $height, 'c') ?>
  <?php endif ?>

  <?php if ($withPreview || isset($embedCode)): ?>
    <?php // This is how we get the preview and/or file extension outside of the widget. Jamming it into the widget made templating weird ?>
    <div class="a-form-row preview">
	    <?php if (isset($embedCode)): ?>
				<label class="full">Embed Preview</label>
				<div class="a-form-field">
  				<?php echo $embedCode ?>
				</div>
				<label class="full">Embed Thumbnail</label>
  		<?php endif ?>
      <?php $widget = $form['file']->getWidget() ?>
      <?php $previewUrl = $widget->getPreviewUrl($form['file']->getValue(), aMediaTools::getOption('gallery_constraints')) ?>
			<div class="a-form-field">
	      <?php if ($previewUrl): ?>
	        <?php echo image_tag($previewUrl) ?>
	      <?php else: ?>
	        <?php $format = $widget->getFormat($form['file']->getValue()) ?>
	        <?php if ($format): ?>
	          <span class="a-media-type <?php echo $format ?>" ><b><?php echo $format ?></b></span>
	        <?php endif ?>
	      <?php endif ?>
			</div>
    </div>
  <?php endif ?>
<?php endif ?>

<?php // Special handling for a new submission ?>

<?php if (!$item): ?>

  <?php // * If there is an embed widget with an error put it on top ?>
  <?php // * If there is no value for the embed widget yet, put it on top ?>
  <?php // * Then mark it shown so it doesn't get displayed later in the form. ?>

  <?php if ((!$embedShown) && isset($form['embed']) && ($form['embed']->hasError() || (!$form['file']->getValue()))): ?>
  	<div class="a-form-row embed a-ui">
      <?php echo $form['embed']->renderLabel() ?>
      <?php echo $form['embed']->renderError() ?>
      <?php echo $form['embed']->render() ?>
  	</div>
  	<?php $embedShown = true ?>
  <?php endif ?>

  <?php // * If there is a file widget with an error put it on top ?>
  <?php // * If there is an embedded media form with no thumbnail yet, put it on top ?>
  <?php // * Then mark it shown so it doesn't get displayed later in the form. ?>

  <?php if ((!$fileShown) && isset($form['file']) && ($form['file']->hasError() || ($form instanceof BaseaMediaVideoForm && (!$form['file']->getValue())))): ?>
  	<div class="a-form-row replace a-ui">
      <?php echo $form['file']->renderLabel() ?>
      <?php echo $form['file']->renderError() ?>
      <?php echo $form['file']->render() ?>
  		<?php if ((!$single) && (!$item)): ?>
        <a class="a-btn icon a-delete alt lite" href="#"><span class="icon"></span>Delete File</a>
        <?php a_js_call('apostrophe.mediaEnableRemoveButton(?)', $i) ?>
      <?php endif ?>
  	</div>
  	<?php $fileShown = true ?>
  <?php endif ?>
<?php endif ?>

<div class="a-form-row title">
	<?php echo $form['title']->renderLabel() ?>
	<div class="a-form-field">
		<?php echo $form['title']->render() ?>
	</div>			
	<?php if (!$firstPass): ?>
  	<?php echo $form['title']->renderError() ?>
	<?php endif ?>
</div>

<div class="a-form-row description">
	<?php // echo $form['description']->renderLabel() ?>
	<div class="a-form-field">
		<?php echo $form['description']->render() ?>
	</div>
	<?php echo $form['description']->renderError() ?>
</div>

<div class="a-form-row credit">
  <?php echo $form['credit']->renderLabel() ?>
	<div class="a-form-field">
  	<?php echo $form['credit']->render() ?>
	</div>
  <?php echo $form['credit']->renderError() ?>
</div>

<div class="a-form-row categories">
	<?php echo $form['categories_list']->renderLabel() ?>
	<div class="a-form-field">
		<?php echo $form['categories_list']->render() ?>
	</div>
	<?php if ($item): ?>
  	<?php $adminCategories = $item->getAdminCategories() ?>
  	<?php if (count($adminCategories)): ?>
      <div class="a-form-field">
  	    <?php echo 'Set by admin: ' . implode(',', $adminCategories) ?>
  	  </div>
  	<?php endif ?>
  <?php endif ?>
	<?php if (!$firstPass): ?>
		<?php echo $form['categories_list']->renderError() ?>
	<?php endif ?>
</div>

<div class="a-form-row tags">
  <?php echo $form['tags']->renderLabel() ?>
	<div class="a-form-field">
  	<?php echo $form['tags']->render(array('id' => 'a-media-item-tags-input-'.$i, )) ?>
  	<?php $options = array('popular-tags' => $popularTags, 'tags-label' => ' ', 'commit-selector' => $submitSelector, 'typeahead-url' => a_url('taggableComplete', 'complete')) ?>
  	<?php if (sfConfig::get('app_a_all_tags', true)): ?>
  	  <?php $options['all-tags'] = $allTags ?>
  	<?php endif ?>
		<?php a_js_call('pkInlineTaggableWidget(?, ?)', '#a-media-item-tags-input-'.$i, $options) ?>
	</div>
  <?php echo $form['tags']->renderError() ?>
</div>

<div class="a-form-row permissions">
	<?php echo $form['view_is_secure']->renderLabel() ?>
	<div class="a-form-field">
		<?php echo $form['view_is_secure']->render() ?>
	</div>
	<?php echo $form['view_is_secure']->renderError() ?>
  <div class="a-help">
		<!-- John, we'll want to do jake's new question mark floating help here instead. -->
		<?php echo __('Permissions: Hidden Photos can be used in photo slots, but are not displayed in the Media section.', null, 'apostrophe') ?>
  </div>
</div>

<?php // Let them replace an existing embed code. ?>
<?php // TODO: have john wrap the "replace file" button or similar around this so it's not always in your face ?>
<?php if ((!$embedShown) && isset($form['embed'])): ?>
  <div class="a-form-row embed a-ui">
    <?php echo $form['embed']->renderLabel() ?>
    <?php echo $form['embed']->renderError() ?>
    <?php echo $form['embed']->render() ?>
	</div>
<?php endif ?>

<?php // Let them replace an existing file. ?>
<?php if ((!$fileShown) && isset($form['file'])): ?>
	<div class="a-form-row replace a-ui">
		<div class="a-options-container">
			<a href="#replace-image" onclick="return false;" id="a-media-replace-image-<?php echo $i ?>" class="a-btn icon a-replace alt lite"><span class="icon"></span><?php echo $embeddable ? a_('Replace Thumbnail') : a_('Replace File') ?></a>
			<div class="a-ui a-options dropshadow">
      	<?php echo $form['file']->renderLabel() ?>
				<div class="a-form-field">
      		<?php echo $form['file']->render() ?>
				</div>
      	<?php echo $form['file']->renderError() ?>
    	</div>
		</div>
		<?php // In a multiple-upload context this is how we decide not to use one of the items after all ?>
		<?php if ((!$single) && (!$item)): ?>
	  	<a class="a-btn icon a-delete lite" href="#"><span class="icon"></span><?php echo a_('Delete File') ?></a>
	    <?php a_js_call('apostrophe.mediaEnableRemoveButton(?)', $i) ?>
	  <?php endif ?>
    <?php if ($item && $item->getDownloadable()): ?>
			<?php echo link_to(__("%buttonspan%Download Original", array('%buttonspan%' => "<span class='icon'></span>"), 'apostrophe'),	"aMediaBackend/original?" .http_build_query(array("slug" => $item->getSlug(), "format" => $item->getFormat())), array("class"=>"a-btn icon a-download lite alt")) ?>
		<?php endif ?>
	</div>
<?php endif ?>
<?php if ($single): ?>

	<?php if ($editVideoSuccess): ?>
			</div>
		</div>
	<?php endif ?>

  <ul class="a-ui a-controls a-align-left bottom">
		<?php if ($editVideoSuccess): ?>
	   	<li><?php echo a_button(a_('Cancel'), a_url('aMedia', 'resume'), array('icon','a-cancel', 'big','alt')) ?></li>
	   	<li><?php echo a_anchor_submit_button(a_('Save Media'), array('big','a-show-busy'), substr($submitSelector, 1)) ?></li>
		<?php else: ?>
	   	<li><?php echo a_anchor_submit_button(a_('Save'), array('a-show-busy'), substr($submitSelector, 1)) ?></li>
	   	<li><?php echo a_button(a_('Cancel'), a_url('aMedia', 'resume'), array('icon','a-cancel','alt')) ?></li>
		<?php endif ?>
  	<?php if ($item): ?>
    	<li>
    		<?php echo link_to("<span class='icon'></span>".__("Delete", null, 'apostrophe'), "aMedia/delete?" . http_build_query(
         array("slug" => $item->slug)),
         array("confirm" => __("Are you sure you want to delete this item?", null, 'apostrophe'), "class"=>"a-btn icon a-delete alt no-label", 'title' => __('Delete', null, 'apostrophe'), ),
         array("target" => "_top")) ?>
    	</li>
    <?php endif ?>
	</ul>
  <div class="a-form-row a-hidden">
  		<?php echo $form->renderHiddenFields() ?>
  </div>
</form>
<?php else: ?>
</div>
<?php endif ?>
			

<?php if (!isset($itemFormScripts)): ?>
<?php // TODO: When Categories and Tags are updated to use our inline JS widgets, these scripts can get removed ?>
	<?php include_partial('aMedia/itemFormScripts') ?>
<?php endif ?>

<?php a_js_call('apostrophe.menuToggle(?)', array('button' => '#a-media-replace-image-'.$i, 'classname' => '', 'overlay' => false)) ?>
<?php a_js_call('apostrophe.mediaReplaceFileListener(?)', array('menu' => '#a-media-replace-image-'.$i, 'input' => '.a-form-row.replace input[type="file"]', 'message' => a_('This file will be replaced after you click save.'), 'fileLabel' => a_('New file: '))) ?>

<?php if($sf_request->isXmlHttpRequest()): ?>
	<?php a_js_call('apostrophe.mediaAjaxSubmitListener(?)', array('form' => '#a-media-edit-form-'.$i, 'descId' => $form['description']->renderId(), 'url' => $formAction, 'update' => '#a-media-item-'.$item->getId().' .a-media-item-information')) ?>
<?php endif ?>

<?php a_js_call('apostrophe.radioToggleButton(?)', array('field' => '#a-media-editor-'.$i.' .a-form-row.permissions > .a-form-field', 'opt1Label' => 'public', 'opt2Label' => 'hidden')) ?>

<?php // include pkTagahead for the taggable widget ?>
<?php use_javascript('/sfDoctrineActAsTaggablePlugin/js/pkTagahead.js') ?>
