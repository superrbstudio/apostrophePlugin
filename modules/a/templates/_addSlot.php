<?php
  // Compatible with sf_escaping_strategy: true
  $id = isset($id) ? $sf_data->getRaw('id') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
	$singleSlot = isset($singleSlot) ? $sf_data->getRaw('singleSlot') : null;
	$slotTypesInfo = isset($slotTypesInfo) ? $sf_data->getRaw('slotTypesInfo') : aTools::getSlotTypesInfo($options);
?>
<?php use_helper('a');?>

<?php foreach ($slotTypesInfo as $type => $info): ?>

	<?php if (!$singleSlot): ?>
		<li class="a-options-item">
	<?php endif ?>

		<?php $label = ($singleSlot) ? a_('Add').' '.$info['label'] : $info['label'] ?>
		<?php $buttonClass = ($singleSlot) ? array('a-add-slot', 'icon', 'big', $info['class']) : array('alt', 'icon', 'no-bg', 'slot', $info['class'])  ?>
		<?php $buttonId = 'a-area-'.$id.'-'.$name.'-add-'.$info['class'].'-slot-button' ?>
		<?php echo a_js_button($label, $buttonClass, $buttonId) ?>
		<?php echo a_js_call('apostrophe.areaEnableAddSlotChoice(?)', array('url' => url_for("a/addSlot") . '?' . http_build_query(array('name' => $name, 'id' => $id, 'type' => $type, 'actual_url' => $sf_request->getUri())), 'pageId' => $id, 'name' => $name, 'buttonId' => $buttonId, 'debug' => false)) ?>

	<?php if (!$singleSlot): ?>
		</li>
	<?php endif ?>

<?php endforeach ?>