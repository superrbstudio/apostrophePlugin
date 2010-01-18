<?php use_helper('jQuery') ?>

<?php slot('body_class') ?>a-media<?php end_slot() ?>

<div id="a-media-plugin">
	
	<?php include_component('aMedia', 'browser') ?>

	<div class="a-media-toolbar">
		<h3>You are editing: <?php echo $item->getTitle() ?></h3>
	</div>

	<div class="a-media-library">			
	<?php include_partial('aMedia/editImage', array('item' => $item, 'firstPass' => false, 'form' => $form)) ?>		
	</div>

</div>