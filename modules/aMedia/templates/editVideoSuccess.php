<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
  $item = isset($item) ? $sf_data->getRaw('item') : null;
  $serviceError = isset($serviceError) ? $sf_data->getRaw('serviceError') : null;
	$i = 0;
	$submitSelector = $item ? ('#' . $item->getSlug() . '-submit') : '.a-media-multiple-submit-button';	
?>
<?php use_helper('a') ?>

<?php slot('body_class') ?>a-media<?php end_slot() ?>

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

<div class="a-media-items">				

  <?php if ($item): ?>
  	<?php $slug = $item->getSlug() ?>
  <?php else: ?>
  	<?php $slug = false ?>
  <?php endif ?>

  <?php // Post-form-validation error when we tried to get the thumbnail ?>
  <?php if (isset($serviceError)): ?>
  <h3><?php echo __('That is not a valid YouTube video URL.', null, 'apostrophe') ?></h3>
  <?php endif ?>

  <form method="post" class="a-media-edit-form a-ui" id="a-media-edit-form-<?php echo $i ?>" enctype="multipart/form-data" action="<?php echo url_for(aUrl::addParams("aMedia/editVideo", array("slug" => $slug)))?>">

	
		<div class='a-form-row a-hidden'>
  		<?php echo $form->renderHiddenFields() ?>
		</div>

    <div class="a-form-row title">
      <?php echo $form['title']->renderLabel() ?>
			<div class="a-form-field">
      	<?php echo $form['title']->render() ?>
			</div>
      <?php if (!$sf_params->get('first_pass')): ?>
        <?php echo $form['title']->renderError() ?>
      <?php endif ?>			
    </div>

    <?php if (isset($form['service_url'])): ?>
      <div class="a-form-row service-url">
        <?php echo $form['service_url']->renderLabel('Video URL') ?>
				<div class="a-form-field">
        	<?php echo $form['service_url']->render() ?>
				</div>
        <?php echo $form['service_url']->renderError() ?>
      </div>
    <?php endif ?>

    <?php if (isset($form['embed'])): ?>
      <div class="a-form-row embed">	
        <?php echo $form['embed']->renderLabel() ?>
				<div class="a-form-field">
        	<?php echo $form['embed']->render() ?>
				</div>
        <?php echo $form['embed']->renderError() ?>
      </div>
    <?php endif ?>

    <div class="a-form-row description">
      <?php echo $form['description']->renderLabel() ?>
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
      <?php if (!$sf_params->get('first_pass')): ?>
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

    <ul class="a-ui a-controls">
      <li>
				<input type="submit" value="<?php echo __('Save', null, 'apostrophe') ?>" class="a-btn a-submit" id="<?php echo substr($submitSelector, 1) ?>" />
			</li>
      <?php if ($item): ?>
      <li><?php echo link_to('<span class="icon"></span>'.__("Delete", null, 'apostrophe'), "aMedia/delete?" . http_build_query(
          array("slug" => $slug)),
          array("confirm" => __("Are you sure you want to delete this item?", null, 'apostrophe'),
            "target" => "_top", "class"=>"a-btn icon a-delete")) ?></li>
      <?php endif ?>
			<li><?php echo link_to('<span class="icon"></span>'.__("Cancel", null, 'apostrophe'), "aMedia/resumeWithPage", array("class"=>"a-btn icon a-cancel")) ?></li>
    </ul>
  </form>
</div>

<?php if (!isset($itemFormScripts)): ?>
<?php // TODO: When Categories and Tags are updated to use our inline JS widgets, these scripts can get removed ?>
	<?php include_partial('aMedia/itemFormScripts') ?>
<?php endif ?>

<?php a_js_call('apostrophe.radioToggleButton(?)', array('field' => '.a-form-row.permissions .a-form-field', 'opt1Label' => 'public', 'opt2Label' => 'hidden')) ?>

</div>