<?php
  // Compatible with sf_escaping_strategy: true
  $id = isset($id) ? $sf_data->getRaw('id') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
?>
<?php use_helper('a');?>

<?php $slotTypesInfo = aTools::getSlotTypesInfo($options); ?>

<?php foreach ($slotTypesInfo as $type => $info): ?>

<?php 
  $label = $info['label'];
  $class = $info['class'];
?>

<?php $buttonId = "a-area-$id-$name-add-$class-slot-button" ?>
<li class="a-options-item"><?php echo a_js_button($label, array('alt', 'icon', 'no-bg', $class, 'slot'), $buttonId) ?></li>
<?php echo a_js_call('apostrophe.areaEnableAddSlotChoice(?)', array('url' => url_for("a/addSlot") . '?' . http_build_query(array('name' => $name, 'id' => $id, 'type' => $type, 'actual_url' => $sf_request->getUri())), 'pageId' => $id, 'name' => $name, 'buttonId' => $buttonId)) ?>

<?php endforeach ?>

