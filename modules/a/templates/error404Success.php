<?php slot('body_class') ?>a-error404<?php end_slot() ?>
<?php use_helper('a') ?>

<?php $page = aPageTable::retrieveBySlug('/admin/error404') ?>	
<?php ($page) ? $slots = $page->getArea('body') : $slots = array() ?>

<?php // If there are no slots, show some default text ?>
<?php if (!count($slots)): ?>
	<h2><?php echo a_('Error 404 &mdash; The page you are looking for could not be found.') ?></h2>
	<?php $search = trim(aTools::slugify(str_replace($sf_request->getUriPrefix(), '', $sf_request->getUri()), false, false, ' ')) ?>
	<h3><?php echo link_to(a_('Try searching for %SEARCH%.', array('%SEARCH%' => $search)), 'a/search?' . http_build_query(array('q' => $search))) ?></h3>
	<h3><a href="/"><?php echo a_('Go Home.') ?></a></h3>
<?php endif ?>

<?php // Display some help information to admins so they know they can customize the Error404 page ?>
<?php if ($sf_user->hasCredential('admin')): ?>
	<div class="a-help">
		<?php echo a_('You can customize the error404 page by adding your own content below.') ?>
	</div>
<?php endif ?>	

<?php // Only display this area if there is content in it OR if the user is logged-in & admin. ?>
<?php // Note: The sandbox pages.yml fixtures pre-populate an 'en' RichText slot with a 404 message. ?>
<?php if (count($slots) || $sf_user->hasCredential('admin')): ?>
	<?php a_area('body', array(
		'slug' => '/admin/error404', 
		'allowed_types' => array(
			'aRichText', 
			'aVideo',		
			'aSlideshow', 
			// 'aSmartSlideshow', 	
			// 'aFile',
			// 'aAudio',		
			// 'aFeed', 		
			// 'aButton', 
			// 'aBlog',
			// 'aEvent',
			// 'aEventSingle',
			// 'aText',
			// 'aRawHTML', 		
		),
	  'type_options' => array(
			'aRichText' => array(
			  'tool' => 'Main',
				// 'allowed-tags' => array(),
				// 'allowed-attributes' => array('a' => array('href', 'name', 'target'),'img' => array('src')),
				// 'allowed-styles' => array('color','font-weight','font-style'), 
			), 	
			'aVideo' => array(
				'width' => 480, 
				'height' => false, 
				'resizeType' => 's',
				'flexHeight' => true, 
				'title' => false,
				'description' => false,			
			),		
			'aSlideshow' => array(
				'width' => 480, 
				'height' => false,
				'resizeType' => 's',  
				'flexHeight' => true, 
				'constraints' => array('minimum-width' => 480),
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
				'width' => 480, 
				'height' => false,
				'resizeType' => 's',  
				'flexHeight' => true, 
				'constraints' => array('minimum-width' => 480),
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
				'width' => 480,
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
				'width' => 480, 
				'flexHeight' => true, 
				'resizeType' => 's', 
				'constraints' => array('minimum-width' => 480),  
				'rollover' => true, 
				'title' => true, 
				'description' => false
			),		
			'aBlog' => array(
				'slideshowOptions' => array(
					'width' => 480, 
					'height' => 320
				),
			),
			'aEvent' => array(
				'slideshowOptions' => array(
					'width' => 340, 
					'height' => 220
				),
			),
			'aEventSingle' => array(
				'slideshowOptions' => array(
					'width' => 340, 
					'height' => 220
				),
			),
	    'aText' => array(
				'multiline' => true
			),
			'aRawHTML' => array(
			), 
		))) ?>
<?php endif ?>