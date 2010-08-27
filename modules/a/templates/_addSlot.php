<?php
  // Compatible with sf_escaping_strategy: true
  $id = isset($id) ? $sf_data->getRaw('id') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
?>
<?php use_helper('I18N');?>

<?php $slotTypesInfo = aTools::getSlotTypesInfo($options); ?>

<?php foreach ($slotTypesInfo as $type => $info): ?>

<?php 
  $label = $info['label'];
  $class = $info['class'];
	$link = jq_link_to_remote(__($label, null, 'apostrophe'), array(
		"url" => url_for("a/addSlot") . '?' . http_build_query(array('name' => $name, 'id' => $id, 'type' => $type, 'actual_url' => $sf_request->getUri() )),
		"update" => "a-slots-$id-$name",
		'script' => true,
		'complete' => 'aUI("#a-area-'.$id.'-'.$name.'"); $("#a-area-'.$id.'-'.$name.'").removeClass("a-options-open");', 
		), 
		array(
			'class' => 'a-btn alt icon no-bg ' . $class .' slot', 
	));
?>	

<li class="a-options-item"><?php echo $link ?></li>

<?php endforeach ?>

