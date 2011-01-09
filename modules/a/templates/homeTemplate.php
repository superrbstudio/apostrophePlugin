<?php use_helper('a') ?>
<?php slot('body_class') ?>a-home<?php end_slot() ?>

<?php // Breadcrumb is removed for the home page template because it is redundant ?>
<?php slot('a-breadcrumb', '') ?>

<?php // Subnav is removed for the home page template because it is redundant ?>
<?php slot('a-subnav', '') ?>

<?php a_slot('home-banner', 
	'aSlideshow', array(
		'width' => 960, 
		'height' => 300,
		'resizeType' => 'c',  
		'flexHeight' => false, 
		'constraints' => array('minimum-width' => 960, 'minimum-height' => 300),
		'arrows' => true,
		'interval' => 8,			
		'random' => true, 
		'title' => false,
		'description' => false,
		'credit' => false,
		'position' => true,
		'transition' => 'crossfade', 
		'itemTemplate' => 'homeBannerItem',
	)) ?>

<?php a_area('body', array(
	'allowed_types' => array(
		'aRichText', 
		'aVideo',		
		'aSlideshow', 
		'aSmartSlideshow', 	
		'aFile',
		'aAudio',		
		'aFeed', 		
		'aButton', 
		'aText',
		'aRawHTML', 		
	),
  'type_options' => array(
		'aRichText' => array(
		  'tool' => 'Main',
			// 'allowed-tags' => array(),
			// 'allowed-attributes' => array('a' => array('href', 'name', 'target'),'img' => array('src')),
			// 'allowed-styles' => array('color','font-weight','font-style'), 
		), 	
		'aVideo' => array(
			'width' => 720, 
			'height' => false, 
			'resizeType' => 's',
			'flexHeight' => true, 
			'title' => false,
			'description' => false,			
		),		
		'aSlideshow' => array(
			'width' => 720, 
			'height' => false,
			'resizeType' => 's',  
			'flexHeight' => true, 
			'constraints' => array('minimum-width' => 720),
			'arrows' => true,
			'interval' => false,			
			'random' => false, 
			'title' => false,
			'description' => false,
			'credit' => false,
			'position' => false,
			'itemTemplate' => 'slideshowItem',       			
		),
		'aSmartSlideshow' => array(
			'width' => 720, 
			'height' => false,
			'resizeType' => 's',  
			'flexHeight' => true, 
			'constraints' => array('minimum-width' => 720),
			'arrows' => true,
			'interval' => false,			
			'random' => false, 
			'title' => false,
			'description' => false,
			'credit' => false,
			'position' => false,
			'itemTemplate' => 'slideshowItem',       			
		),
		'aFile' => array(
		), 
		'aAudio' => array(
			'width' => 720,
			'title' => true,
			'description' => true,
			'download' => true,
			'playerTemplate' => 'default',
		),
		'aFeed' => array(
			'posts' => 5,
			'links' => true,
			'dateFormat' => false,
			'itemTemplate' => 'aFeedItem',
			// 'markup' => '<strong><em><p><br><ul><li><a>',
			// 'attributes' => false,
			// 'styles' => false,
		),
		'aButton' => array(
			'width' => 720, 
			'flexHeight' => true, 
			'resizeType' => 's', 
			'constraints' => array('minimum-width' => 720),  
			'rollover' => true, 
			'title' => true, 
			'description' => false
		),		
    'aText' => array(
			'multiline' => true
		),
		'aRawHTML' => array(
		), 
	))) ?>
	
<?php a_area('sidebar', array(
	'allowed_types' => array(
		'aRichText', 
		'aVideo',		
		'aSlideshow', 
		'aSmartSlideshow', 	
		'aFile',
		'aAudio',		
		'aFeed', 		
		'aButton', 
		'aText',
		'aRawHTML',	
	),
  'type_options' => array(
		'aRichText' => array(
			'tool' => 'Sidebar'
		),
		'aSlideshow' => array(
			'width' => 200, 
			'height' => false,
			'resizeType' => 's',  
			'flexHeight' => true, 
			'constraints' => array('minimum-width' => 200),
			'arrows' => true,
			'interval' => false,			
			'random' => false, 
			'title' => false,
			'description' => false,
			'credit' => false,
			'position' => false,
			'itemTemplate' => 'slideshowItem',
		),		
		'aSmartSlideshow' => array(
			'width' => 200, 
			'height' => false,
			'resizeType' => 's',  
			'flexHeight' => true, 
			'constraints' => array('minimum-width' => 200),
			'arrows' => true,
			'interval' => false,
			'random' => false, 						
			'title' => false,
			'description' => false,
			'credit' => false,
			'position' => false,
			'itemTemplate' => 'slideshowItem',
		),		
		'aVideo' => array(
			'width' => 200, 
			'flexHeight' => true, 
			'resizeType' => 's'
		),
		'aFeed' => array(
			'posts' => 5,
			'links' => true,
			'dateFormat' => false,
			'itemTemplate' => 'aFeedItem',
			// 'markup' => '<strong><em><p><br><ul><li><a>',
			// 'attributes' => false,
			// 'styles' => false,
		),
		'aAudio' => array(
			'width' => 200,
			'title' => true,
			'description' => true,
			'download' => true,
			'playerTemplate' => 'default',
		),		
		'aButton' => array(
			'width' => 200, 
			'flexHeight' => true, 
			'resizeType' => 's', 
			'constraints' => array('minimum-width' => 200),
			'rollover' => true, 
			'title' => true, 
			'description' => false,
		),		
    'aText' => array(
			'multiline' => true
		),
		'aRawHTML' => array(
		), 
	))) ?>