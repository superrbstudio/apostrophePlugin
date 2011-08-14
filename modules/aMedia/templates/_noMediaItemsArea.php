<div class="a-no-items">
<?php // We need to retrieve the page if we are not at /admin/error404 ?>
<?php $page = aPageTable::retrieveBySlug('/admin/error-a-media') ?>	
<?php ($page) ? $slots = $page->getArea('body') : $slots = array() ?>

<?php // If there are no slots, show some default text ?>
<?php if (!count($slots)): ?>
	<h3>
	  <?php // I added the word "suitable" here and now it's equally reasonable ?>
	  <?php // for the case where the constraints, the filters, etc. are the reason ?>
	  <?php // you don't see anything, rather than the library being truly 100% empty. -Tom ?>
		<?php echo a_('Oops! You don\'t have anything suitable in your media library yet.') ?><br/>
		<?php echo a_('Do you want to <a href="#upload-images" class="a-add-media-toggle">add some media?</a>') ?>
	</h3>
<?php endif ?>

<?php // Only display this area if there is content in it OR if the user is logged-in & admin. ?>
<?php // Note: The sandbox pages.yml fixtures pre-populate an 'en' RichText slot with the media message. ?>
<?php if (count($slots) || $sf_user->hasCredential('admin')): ?>
	<?php a_slot('body', 'aRichText', array('tool' => 'Main', 'slug' => '/admin/error-a-media', 'editLabel' => 'Edit Message', )) ?>
<?php endif ?>
</div>

<?php // This works with the default text supplied above and if the end-user adds the class of 'a-add-media-toggle' to anything in richtext ?>
<?php a_js_call('$(".a-add-media-toggle").click(function(event){ event.preventDefault(); $("#a-media-add").show(); });') ?>
