<?php use_helper('a') ?>

<?php // Defining the <body> class ?>
<?php slot('a-body-class','a-default') ?>

<?php include_component('a', 'standardArea', array('name' => 'body', 'width' => 480, 'toolbar' => 'main')) ?>

<?php include_component('a', 'standardArea', array('name' => 'sidebar', 'width' => 200, 'toolbar' => 'sidebar')) ?>

<?php slot('a-footer') ?>
<div class='a-footer-wrapper clearfix'>
	<div class='a-footer clearfix'>
	  <?php include_partial('a/footer') ?>
		<?php include_partial('aFeedback/feedbackForm'); ?>	
	</div>
</div>
<?php end_slot() ?>