<?php
$slotTypesInfo = aTools::getSlotTypesInfo($options);

foreach ($slotTypesInfo as $type => $info) {
  $label = $info['label'];
  $class = $info['class'];
	$link = jq_link_to_remote($label, array(
		"url" => "a/addSlot?" . http_build_query(array('name' => $name, 'id' => $id, 'type' => $type, )),
		"update" => "a-slots-$id-$name",
		'script' => true,
		'complete' => 'aUI("#a-area-'.$id.'-'.$name.'","add-slot"); $("#a-area-'.$id.'-'.$name.'").removeClass("addslot-now");', 
		), 
		array(
			'class' => 'a-btn icon ' . $class .' slot', 
	));

	echo "<li>".$link."</li>";
}
?>