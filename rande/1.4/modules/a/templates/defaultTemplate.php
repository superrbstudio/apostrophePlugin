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
	'allowed_types' => array(
		'aRichText', 
		'aSlideshow', 
		'aVideo', 
		'aImage', 
		'aFeed', 
		'aPDF',		
		'aButton', 		
		'aText',
		'aRawHTML',
	),
  'type_options' => array(
    'aText' => array('multiline' => true),
		'aRichText' => array('tool' => 'Main'), 	
		'aSlideshow' => array("width" => 480, "flexHeight" => true),
		'aVideo' => array('width' => 480, 'flexHeight' => true, 'resizeType' => 's'),		
		'aImage' => array('width' => 480, 'flexHeight' => true, 'resizeType' => 's'),
		'aFeed' => array(),
		'aButton' => array('width' => 480, 'flexHeight' => true, 'resizeType' => 's'),
		'aPDF' => array('width' => 480, 'flexHeight' => true, 'resizeType' => 's'),		
	))) ?>
	
<?php a_area('sidebar', array(
	'allowed_types' => array(
		'aRichText', 
		'aSlideshow', 
		'aVideo',
		'aBlog', 		 
		'aImage', 
		'aFeed', 
		'aPDF', 
		'aButton', 
		'aText',
		'aRawHTML', 		
	),
  'type_options' => array(
		'aRichText' => array('tool' => 'Sidebar'),
		'aSlideshow' => array('width' => 200, 'flexHeight' => true, 'resizeType' => 's'),		
		'aVideo' => array('width' => 200, 'flexHeight' => true, 'resizeType' => 's'),				
		'aImage' => array('width' => 200, 'flexHeight' => true, 'resizeType' => 's'),
		'aFeed' => array(),		
		'aButton' => array('width' => 200, 'flexHeight' => true, 'resizeType' => 's'),
		'aPDF' => array('width' => 200, 'flexHeight' => true, 'resizeType' => 's'),		
	))) ?>
