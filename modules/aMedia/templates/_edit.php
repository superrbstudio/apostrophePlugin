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
?>

<?php if (!isset($form['file'])): ?>
  <?php $withPreview = false ?>
<?php endif ?>

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
  <form method="POST" class="a-ui a-media-edit-form" id="a-media-edit-form-<?php echo $i ?>" enctype="multipart/form-data" action="<?php echo $formAction ?>">
<?php else: ?>
  <?php // This is one of several items in a larger form ?>
  <div class="a-ui a-media-item a-media-edit-form <?php echo ($n%2) ? "odd" : "even" ?>" id="a-media-item-<?php echo $i ?>">
<?php endif ?>

<?php if ($withPreview): ?>
  <?php // This is how we get the preview and/or file extension outside of the widget. Jamming it into the widget made templating weird ?>
  <div class="a-form-row preview">
    <?php $widget = $form['file']->getWidget() ?>
    <?php $previewUrl = $widget->getPreviewUrl($form['file']->getValue(), aMediaTools::getOption('gallery_constraints')) ?>
    <?php if ($previewUrl): ?>
      <?php echo image_tag($previewUrl) ?>
    <?php else: ?>
      <?php $format = $widget->getFormat($form['file']->getValue()) ?>
      <?php if ($format): ?>
        <span class="a-media-type <?php echo $format ?>" ><b><?php echo $format ?></b></span>
      <?php endif ?>
    <?php endif ?>
  </div>
<?php endif ?>

<?php // * If there is a file widget with an error put it on top ?>
<?php // * If there is an embedded media form with no thumbnail yet, put it on top ?>
<?php // * Then unset it so it doesn't get displayed later in the form. ?>

<?php if (isset($form['file']) && ($form['file']->hasError() || ($form instanceof BaseaMediaVideoForm && (!$form['file']->getValue())))): ?>
	<div class="a-form-row replace a-ui">
    <?php echo $form['file']->renderLabel() ?>
    <?php echo $form['file']->renderError() ?>
    <?php echo $form['file']->render() ?>
		<?php if ((!$single) && (!$item)): ?>
      <a class="a-btn icon a-delete lite" href="#"><span class="icon"></span>Delete File</a>
      <?php a_js_call('apostrophe.mediaEnableRemoveButton(?)', $i) ?>
    <?php endif ?>
	</div>
	<?php unset($form['file']) ?>
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
	<?php if (!$firstPass): ?>
		<?php echo $form['categories_list']->renderError() ?>
	<?php endif ?>
</div>

<div class="a-form-row tags">
  <?php echo $form['tags']->renderLabel() ?>
	<div class="a-form-field">
  	<?php echo $form['tags']->render(array('id' => 'a-media-item-tags-input-'.$i, )) ?>
  	<?php $options = array('popular-tags' => $popularTags, 'tags-label' => '', 'commit-selector' => $submitSelector, 'typeahead-url' => url_for('taggableComplete/complete')) ?>
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
  <div class="a-form-help a-hidden">
		<!-- John, we'll want to do jake's new question mark floating help here instead. -->
		<?php echo __('Permissions: Hidden Photos can be used in photo slots, but are not displayed in the Media section.', null, 'apostrophe') ?>
  </div>
</div>

<?php // Let them replace an existing file. ?>
<?php if (isset($form['file'])): ?>
	<div class="a-form-row replace a-ui">
		<div class="a-options-container">		
			<a href="#replace-image" onclick="return false;" id="a-media-replace-image-<?php echo $i ?>" class="a-btn icon a-replace alt lite"><span class="icon"></span><?php echo $form instanceof BaseaMediaVideoForm ? a_('Replace Thumbnail') : a_('Replace File') ?></a>
			<div class="a-options dropshadow">
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
<ul class="a-ui a-controls">
 	<li>
		<input type="submit" value="<?php echo __('Save', null, 'apostrophe') ?>" class="a-btn a-submit" id="<?php echo substr($submitSelector, 1) ?>" />
	</li>
 	<li>
		<?php echo link_to("<span class='icon'></span>".__('Cancel', null, 'apostrophe'), "aMedia/resumeWithPage", array("class" => "a-btn icon a-cancel")) ?>
	</li>
	<?php if ($item): ?>
  	<li>
  		<?php echo link_to("<span class='icon'></span>".__("Delete", null, 'apostrophe'), "aMedia/delete?" . http_build_query(
       array("slug" => $item->slug)),
       array("confirm" => __("Are you sure you want to delete this item?", null, 'apostrophe'), "class"=>"a-btn icon a-delete no-label", 'title' => __('Delete', null, 'apostrophe'), ),
       array("target" => "_top")) ?>
  	</li>
  <?php endif ?>
	</ul>
<div class="a-form-row a-hidden">
		<?php echo $form->renderHiddenFields() ?>
</div>
</form>
<?php endif ?>
			
<?php if (!$item): ?>
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

<?php a_js_call('apostrophe.radioToggleButton(?)', array('field' => '.a-form-row.permissions .a-form-field', 'opt1Label' => 'public', 'opt2Label' => 'hidden')) ?>

<?php // include pkTagahead for the taggable widget ?>
<?php use_javascript('/sfDoctrineActAsTaggablePlugin/js/pkTagahead.js') ?>
