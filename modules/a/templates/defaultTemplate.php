<?php use_helper('a') ?>
<?php $page = aTools::getCurrentPage() ?>
<?php slot('body_class') ?>a-default<?php end_slot() ?>

<?php if (0): ?>
	<?php if (!$page->hasChildren()): ?>
		<?php slot('a-subnav','') ?>
		<?php slot('body_class') ?>a-default no-sidebar<?php end_slot() ?>	
	<?php endif ?>	
<?php endif ?>

<?php a_area('body', array(
	'allowed_types' => array('aRichText', 'aFeed', 'aImage', 'aButton', 'aSlideshow', 'aVideo', 'aPDF', 'aRawHTML', 'aText'),
  'type_options' => array(
		'aRichText' => array('tool' => 'Main'), 	
		'aFeed' => array(),
		'aImage' => array('width' => 480, 'flexHeight' => true, 'resizeType' => 's'),
		'aButton' => array('width' => 480, 'flexHeight' => true, 'resizeType' => 's'),
		'aVideo' => array('width' => 480, 'flexHeight' => true, 'resizeType' => 's'),		
		'aSlideshow' => array("width" => 480, "flexHeight" => true),
		'aPDF' => array('width' => 480, 'flexHeight' => true, 'resizeType' => 's'),		
	))) ?>
	
<?php a_area('sidebar', array(
	'allowed_types' => array('aRichText', 'aFeed', 'aImage', 'aButton', 'aSlideshow', 'aVideo', 'aPDF', 'aRawHTML', 'aText'),
  'type_options' => array(
		'aRichText' => array('tool' => 'Sidebar'),
		'aFeed' => array(),
		'aImage' => array('width' => 200, 'flexHeight' => true, 'resizeType' => 's'),
		'aButton' => array('width' => 200, 'flexHeight' => true, 'resizeType' => 's'),
		'aVideo' => array('width' => 200, 'flexHeight' => true, 'resizeType' => 's'),				
		'aSlideshow' => array('width' => 200, 'flexHeight' => true, 'resizeType' => 's'),
		'aPDF' => array('width' => 200, 'flexHeight' => true, 'resizeType' => 's'),		
	))) ?>
