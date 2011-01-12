<div class="a-no-items">
<?php // We need to retrieve the page if we are not at /admin/error404 ?>
<?php $page = aPageTable::retrieveBySlug('aErrors:aMedia') ?>	
<?php ($page) ? $slots = $page->getArea('body') : $slots = array() ?>

<?php // Display some help information to admins so they know they can customize the Error404 page ?>
<?php if ($sf_user->hasCredential('admin')): ?>
	<div class="a-help a-no-items">
		<?php echo a_('You can customize the message displayed to users when there are no media items by adding content below.') ?>
	</div>
<?php endif ?>	

<?php // If there are no slots, show some default text ?>
<?php if (!count($slots)): ?>
	<h3>
		<?php echo a_('Oops! You don\'t have anything in your media library.') ?><br/>
		<?php echo a_('Do you want to <a href="#upload-images" id="a-upload-some-images">add some media?</a>') ?>
	</h3>
<?php endif ?>

<?php // Only display this area if there is content in it OR if the user is logged-in & admin. ?>
<?php // Note: The sandbox pages.yml fixtures pre-populate an 'en' RichText slot with the media message. ?>
<?php if (count($slots) || $sf_user->hasCredential('admin')): ?>
	<?php a_slot('body', 'aRichText', array('tool' => 'Main', 'slug' => 'aErrors:aMedia')) ?>
<?php endif ?>
</a>

<?php a_js_call('$("#a-upload-some-images").click(function(event){ event.preventDefault(); $("#a-media-add").show(); });') ?>
