<?php
use_helper('I18N');

$slotTypesInfo = aTools::getSlotTypesInfo($options);

foreach ($slotTypesInfo as $type => $info) {
  $label = $info['label'];
  $class = $info['class'];
	$link = jq_link_to_remote(__($label, null, 'apostrophe'), array(
		"url" => "a/addSlot?" . http_build_query(array('name' => $name, 'id' => $id, 'type' => $type, )),
		"update" => "a-slots-$id-$name",
		'script' => true,
		'complete' => 'aUI("#a-area-'.$id.'-'.$name.'","add-slot");', 
		), 
		array(
			'class' => 'a-btn icon ' . $class .' slot', 
	));

	echo "<li>".$link."</li>";
}
?>