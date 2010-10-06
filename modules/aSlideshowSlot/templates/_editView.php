<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
  $constraints = isset($constraints) ? $sf_data->getRaw('constraints') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
  $itemIds = isset($itemIds) ? $sf_data->getRaw('itemIds') : null;
?>

<?php echo $form->renderHiddenFields() ?>

<div class="a-form-row type">
	<div class="a-form-field">
		<?php echo $form['type']->render() ?>
		<div class="a-form-help"><?php echo $form['type']->renderHelp() ?></div>
	</div>
	<div class="a-form-error"><?php echo $form['type']->renderError() ?></div>
</div>

<?php // The two wrapper divs are js targets ?>
<div class="a-selected-images">
  <div class="a-form-row browse">
    <?php echo link_to(a_('Browse images'),
      'aMedia/select',
      array(
        'query_string' => 
          http_build_query(
            array_merge(
              $options['constraints'],
              array("multiple" => true,
              "aMediaIds" => implode(",", $itemIds),
              "type" => "image",
              "label" => __("You are creating a slideshow of images.", null, 'apostrophe'),
              "after" => url_for("aSlideshowSlot/edit") . "?" . 
                http_build_query(
                  array(
                    "slot" => $name, 
                    "slug" => $slug, 
                    "permid" => $permid,
                    'actual_url' => aTools::getRealUrl(),
                    "noajax" => 1))))),
        'class' => 'a-btn icon a-media')) ?>
  </div>
</div>
<div class="a-tagged-images">
  <div class="a-form-row count">
  	<?php echo $form['count']->renderLabel(a_('Images')) ?>
  	<div class="a-form-field">
  		<?php echo $form['count']->render() ?>
  		<div class="a-form-help"><?php echo $form['count']->renderHelp() ?></div>
  	</div>
  	<div class="a-form-error"><?php echo $form['count']->renderError() ?></div>
  </div>
  <div class="a-form-row categories">
  	<?php echo $form['categories_list']->renderLabel(__('Category', array(), 'apostrophe')) ?>
  	<div class="a-form-field">
  		<?php echo $form['categories_list']->render() ?>
  		<div class="a-form-help"><?php echo $form['categories_list']->renderHelp() ?></div>
  	</div>
  	<div class="a-form-error"><?php echo $form['categories_list']->renderError() ?></div>
  </div>

  <div class="a-form-row tags">
  	<?php echo $form['tags_list']->renderLabel(__('Tags', array(), 'apostrophe')) ?>
  	<div class="a-form-field">
  		<?php echo $form['tags_list']->render() ?>
  		<div class="a-form-help"><?php echo $form['tags_list']->renderHelp() ?></div>
  	</div>
  	<div class="a-form-error"><?php echo $form['tags_list']->renderError() ?></div>
  </div>
</div>

<?php a_js_call('apostrophe.enableSlideshowEditView(?)', $id) ?>
<script type="text/javascript" charset="utf-8" src="/sfDoctrineActAsTaggablePlugin/js/pkTagahead.js"></script>
<script type="text/javascript" charset="utf-8">
$(document).ready(function() {
    pkTagahead(<?php echo json_encode(url_for("taggableComplete/complete")) ?>);
    aMultipleSelect('#a-<?php echo $form->getName() ?>', { 'choose-one': 'Add Categories' });
		$('#a-<?php echo $form->getName() ?>').addClass('a-options dropshadow');			
  });
</script>

