<div class="a-help simple">
	<p>
		<?php if (isset($options['directions'])): ?>
	  	<?php echo $options['directions'] ?>
		<?php else: ?>
	  	<?php echo a_('Use this slot to add raw HTML markup, such as embed codes.') ?>
		<?php endif ?>
	</p>
	<p><?php echo a_('Use this slot with caution. If bad markup causes the page to become uneditable, add ?safemode=1 to the URL and edit the slot to correct the markup.') ?></p>
</div>
