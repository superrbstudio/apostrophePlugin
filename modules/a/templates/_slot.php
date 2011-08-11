<?php
  // Compatible with sf_escaping_strategy: true
  $editModule = isset($editModule) ? $sf_data->getRaw('editModule') : null;
  $editable = isset($editable) ? $sf_data->getRaw('editable') : null;
  $id = isset($id) ? $sf_data->getRaw('id') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $normalModule = isset($normalModule) ? $sf_data->getRaw('normalModule') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
  $outlineEditable = isset($outlineEditable) ? $sf_data->getRaw('outlineEditable') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $realSlug = isset($realSlug) ? $sf_data->getRaw('realSlug') : null;
  $showEditor = isset($showEditor) ? $sf_data->getRaw('showEditor') : null;
  $slot = isset($slot) ? $sf_data->getRaw('slot') : null;
  $slug = isset($slug) ? $sf_data->getRaw('slug') : null;
  $type = isset($type) ? $sf_data->getRaw('type') : null;
  $updating = isset($updating) ? $sf_data->getRaw('updating') : null;
  $validationData = isset($validationData) ? $sf_data->getRaw('validationData') : null;
?>

<?php use_helper('a') ?>

<?php // We now render the edit view only when it is AJAXed into place on demand. This saves us the ?>
<?php // considerable overhead of loading many instances of FCK we won't use ?>

<?php if ($editable && ($updating || $showEditor)): ?>
  <form method="post" action="#" class="a-slot-form a-edit-view a-ui clearfix" name="a-slot-form-<?php echo $id ?>" id="a-slot-form-<?php echo $id ?>" style="display: <?php echo $showEditor ? "block" : "none" ?>">

  <?php include_component($editModule, 
    "editView", 
    array(
      "name" => $name,
      "type" => $type,
      "permid" => $permid,
      "options" => $options,
      "updating" => $updating,
      "validationData" => $validationData)) ?>

	<ul class="a-ui a-controls">  
	  <li>
	    <?php // We need an id, not a name. Fix Jake's "I can't edit anything" bug ?>
  		<?php echo a_anchor_submit_button(a_('Save'), array('a-save','a-show-busy'), null, 'a-slot-form-submit-'.$id) ?>
		</li>
	  <li>
  		<?php echo a_js_button(a_('Cancel'), array('icon', 'a-cancel', 'alt'), 'a-slot-form-cancel-'.$id) ?>		
		</li>
	</ul>

  </form>
  
  <?php a_js_call('apostrophe.slotEnableForm(?)', array('slot-form' => '#a-slot-form-' . $id, 'slot-content' => '#a-slot-content-' . $id, 'url' => a_url($type . 'Slot', 'edit', array('slot' => $name, 'permid' => $permid, 'slug' => $slug)))) ?>
<?php endif ?>

<?php if ($editable): ?>
  <div class="a-slot-content-container a-normal-view <?php echo $outlineEditable ? " a-editable" : "" ?> clearfix" id="a-slot-content-container-<?php echo $id ?>" style="display: <?php echo $showEditor ? "none" : "block"?>">
<?php endif ?>

<?php include_component($normalModule, 
  "normalView", 
  array(
    "name" => $name,
    "type" => $type,
    "permid" => $permid,
    "updating" => $updating,
    "options" => $options)) ?>

<?php if ($editable): ?>
  </div>
<?php endif ?>

<?php if ($editable): ?>
  <?php a_js_call('apostrophe.slotEnableFormButtons(?)', array('view' => '#a-slot-' . $id, 'cancel' => '#a-slot-form-cancel-' . $id, 'save' => '#a-slot-form-submit-' . $id, 'slot-full-id' => $id, 'edit' => '#a-slot-edit-' . $id, 'showEditor' => $showEditor)) ?>
<?php endif ?>

<?php if ($sf_request->isXmlHttpRequest()): ?>

  <?php // Changing the variant only refreshes the content, not the outer wrapper and controls. However, ?>
  <?php // we do assign a CSS class to the outer wrapper based on the variant ?>
  <?php $variants = aTools::getVariantsForSlotType($type, $options) ?>
  <?php $slotVariant = $slot->getEffectiveVariant($options) ?>
  <?php foreach ($variants as $variant => $data): ?>
    <?php if ($slotVariant !== $variant): ?>
      <?php a_js_call('apostrophe.slotRemoveVariantClass(?, ?)', '#a-slot-' . $id, $variant) ?>
    <?php else: ?>
      <?php a_js_call('apostrophe.slotApplyVariantClass(?, ?)', '#a-slot-' . $id, $variant) ?>
    <?php endif ?>
    <?php // It's OK to show the variants menu once we've saved something ?>
    <?php if (!$slot->isNew()): ?>
      <?php a_js_call('apostrophe.slotShowVariantsMenu(?)', '#a-slot-' . $id) ?>
    <?php endif ?>
  <?php endforeach ?>

<?php endif ?>