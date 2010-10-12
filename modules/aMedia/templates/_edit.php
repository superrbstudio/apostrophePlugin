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
?>

<?php use_helper('a') ?>
  
<?php if (!isset($n)): ?> <?php $n = 0 ?> <?php endif ?>
<?php if (!isset($i)): ?> <?php $i = $item['id'] ?> <?php endif ?>
	

<?php // if there is an $item then we're editing one existing media item ?>
<?php // else: we're part of an annotation of potentially several new items in a bigger form ?>

<?php if (!$item): ?>	
<div class="a-media-item a-media-edit-form <?php echo ($n%2) ? "odd" : "even" ?>" id="a-media-item-<?php echo $i ?>">
<?php endif ?>

<?php if ($item): ?>
<form method="POST" class="a-media-edit-form" id="a-media-edit-form-<?php echo $i ?>" enctype="multipart/form-data" action="<?php echo url_for(aUrl::addParams("aMedia/edit", array("slug" => $item->getSlug())))?>">
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
		
    <?php // If the file is bad, this should be the first thing on the form and should already be open with ?>
    <?php // the error displayed ?>
    <?php if ($form['file']->hasError()): ?>
  		<div class="a-form-row replace a-ui">		
  	    <?php // The label says 'Replace File' now, see BaseaMediaEditForm ?>
	      <?php echo $form['file']->renderLabel() ?>
	      <?php echo $form['file']->renderError() ?>
	      <?php echo $form['file']->render() ?>
  			<?php if (!$item): ?>
  	      <a class="a-btn icon a-delete lite" href="#"><span class="icon"></span>Delete File</a>
  	      <?php a_js_call('apostrophe.mediaEnableRemoveButton(?)', $i) ?>
  	    <?php endif ?>
  		</div>
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
				<?php a_js_call('aInlineTaggableWidget(?, ?)', '#a-media-item-tags-input-'.$i, array('existing-tags' => method_exists($form, 'getObject') ? $form->getObject()->getTags() : array(), 'popular-tags' => $popularTags, 'all-tags' => $allTags, 'typeahead-url' => url_for('taggableComplete/complete'), 'tagsLabel' => 'Item Tags')) ?>
			</div>
      <?php echo $form['tags']->renderError() ?>
			<?php // The inline taggable widget requires Popular Tags and Existing Tags -- These objects need to be created from somewhere before this can work. ?>
			<?php // a_js_call('aInlineTaggableWidget(?, ?)', '#a-media-item-tags-input-'.$i, array('popular-tags' => $popularTags, 'existing-tags' => $existingTags, 'typeahead-url' => url_for('taggableComplete/complete'), 'tagsLabel' => 'Tags')) ?>
	    <div class="a-form-help-text">
	    	<?php echo __('Tags should be separated by commas. Example: teachers, kittens, buildings', null, 'apostrophe') ?>
	    </div>
    </div>

		<div class="a-form-row permissions">
			<?php echo $form['view_is_secure']->renderLabel() ?>
			<div class="a-form-field">
				<?php echo $form['view_is_secure']->render() ?>
			</div>
			<?php echo $form['view_is_secure']->renderError() ?>
	    <div class="a-form-help-text">
				<!-- John, we'll want to do jake's new question mark floating help here instead. -->
				<?php echo __('Permissions: Hidden Photos can be used in photo slots, but are not displayed in the Media section.', null, 'apostrophe') ?>
	    </div>
		</div>
		
		<?php // If the file is good, it's unlikely that they want to replace it, so put that in a toggle at the end ?>
		<?php if (!$form['file']->hasError()): ?>
 			<div class="a-form-row replace a-ui">		
				<?php if ((!$item) || ($item && $item->getDownloadable())): ?>
	  	    	<?php // The label says 'Replace File' now, see BaseaMediaEditForm ?>
	  				<div class="a-options-container">		
	  					<a href="#replace-image" onclick="return false;" id="a-media-replace-image-<?php echo $i ?>" class="a-btn icon a-replace alt lite"><span class="icon"></span>Replace File</a>
	  					<div class="a-options dropshadow">
	  		      	<?php echo $form['file']->renderLabel() ?>
								<div class="a-form-field">
	  		      		<?php echo $form['file']->render() ?>
								</div>
	  		      	<?php echo $form['file']->renderError() ?>
	  		    	</div>
	  				</div>
				<?php endif ?>
	  		<?php if (!$item): ?>
	  	  	<a class="a-btn icon a-delete lite" href="#"><span class="icon"></span>Delete File</a>
	  	    <?php a_js_call('apostrophe.mediaEnableRemoveButton(?)', $i) ?>
	  	  <?php endif ?>
	      <?php if ($item && $item->getDownloadable()): ?>
					<?php echo link_to(__("%buttonspan%Download Original", array('%buttonspan%' => "<span class='icon'></span>"), 'apostrophe'),	"aMediaBackend/original?" .http_build_query(array("slug" => $item->getSlug(), "format" => $item->getFormat())), array("class"=>"a-btn icon a-download lite alt")) ?>
				<?php endif ?>
  		</div>
    <?php endif ?>

   <?php if ($item): ?>
    <ul class="a-ui a-controls">
     	<li>
				<input type="submit" value="<?php echo __('Save', null, 'apostrophe') ?>" class="a-btn a-submit" />
			</li>
     	<li>
				<?php echo link_to("<span class='icon'></span>".__('Cancel', null, 'apostrophe'), "aMedia/resumeWithPage", array("class" => "a-btn icon a-cancel")) ?>
			</li>
			<li>
				<?php echo link_to("<span class='icon'></span>".__("Delete", null, 'apostrophe'), "aMedia/delete?" . http_build_query(
         array("slug" => $item->slug)),
         array("confirm" => __("Are you sure you want to delete this item?", null, 'apostrophe'), "class"=>"a-btn icon a-delete no-label", 'title' => __('Delete', null, 'apostrophe'), ),
         array("target" => "_top")) ?>
			</li>
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
	<?php a_js_call('apostrophe.mediaAjaxSubmitListener(?)', array('form' => '#a-media-edit-form-'.$i, 'descId' => $form['description']->renderId(), 'url' => url_for(aUrl::addParams("aMedia/edit", array("slug" => $item->getSlug()))), 'update' => '#a-media-item-'.$item->getId().' .a-media-item-information')) ?>
<?php endif ?>

<?php // include pkTagahead for the taggable widget ?>
<script src="/sfJqueryReloadedPlugin/js/plugins/jquery.autocomplete.js"></script>
<script src="/sfDoctrineActAsTaggablePlugin/js/pkTagahead.js"></script>
