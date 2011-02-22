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
		'complete' => 'aUI("#a-area-'.$id.'-'.$name.'"); $("#a-area-'.$id.'-'.$name.'").removeClass("add-slot-now");', 
		), 
		array(
			'class' => 'a-btn alt icon nobg ' . $class .' slot', 
	));
?>	

<li class="a-options-item"><?php echo $link ?></li>

<?php endforeach ?>

