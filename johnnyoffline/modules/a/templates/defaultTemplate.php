<?php use_helper('a') ?>

<?php slot('body_class') ?>a-default<?php end_slot() ?>

<?php a_area('body', array(
	'allowed_types' => array('aRichText', 'aImage', 'aButton', 'aSlideshow', 'aVideo', 'aPDF', 'aRawHTML'),
  'type_options' => array(
		'aRichText' => array('tool' => 'Main'), 	
		'aImage' => array('width' => 598, 'flexHeight' => true, 'resizeType' => 's'),
		'aButton' => array('width' => 598, 'flexHeight' => true, 'resizeType' => 's'),
		'aVideo' => array('width' => 598, 'flexHeight' => true, 'resizeType' => 's'),		
		'aSlideshow' => array("width" => 598, "flexHeight" => true),
		'aPDF' => array('width' => 598, 'flexHeight' => true, 'resizeType' => 's'),		
	))) ?>
	
<?php a_area('sidebar', array(
	'allowed_types' => array('aRichText', 'aImage', 'aButton', 'aSlideshow', 'aVideo', 'aPDF', 'aRawHTML'),
  'type_options' => array(
		'aRichText' => array('tool' => 'Sidebar'),
		'aImage' => array('width' => 200, 'flexHeight' => true, 'resizeType' => 's'),
		'aButton' => array('width' => 200, 'flexHeight' => true, 'resizeType' => 's'),
		'aVideo' => array('width' => 200, 'flexHeight' => true, 'resizeType' => 's'),				
		'aSlideshow' => array('width' => 200, 'flexHeight' => true, 'resizeType' => 's'),
		'aPDF' => array('width' => 200, 'flexHeight' => true, 'resizeType' => 's'),		
	))) ?>
