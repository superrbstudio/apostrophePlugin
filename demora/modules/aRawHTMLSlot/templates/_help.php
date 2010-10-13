<div class="a-form-help a-slot-info a-raw-html-info">
	<p>
		<?php if (isset($options['directions'])): ?>
	  	<?php echo $options['directions'] ?>
		<?php else: ?>
	  	<?php echo __('Use this slot to add raw HTML markup, such as embed codes.', null, 'apostrophe') ?>
		<?php endif ?>
	</p>
	<p>
		<?php echo __('Use this slot with caution. If bad markup causes the page to become uneditable, add ?safemode=1 to the URL and edit the slot to correct the markup.', null, 'apostrophe') ?>
	</p>
</div>
