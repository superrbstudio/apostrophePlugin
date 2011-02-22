<?php // Invoked at the end of every page load or AJAX refresh. Keep it light. ?>

<?php use_helper('a') ?>

<?php // Project level hook. Our default version is empty ?>
<?php a_js_call('apostrophe.ready()') ?>

<?php // There must be a better time and place for this ?>
<?php if ($sf_user->isAuthenticated()): ?>
	<?php a_js_call('apostrophe.enableCloseHistoryButtons(?)', array('close_history_buttons' => '#a-history-close-button, #a-history-heading-button')) ?>
<?php endif ?>
	
<?php // Disqus comments ?>
<?php if (has_slot('disqus_needed')): ?>
	<?php include_partial('aBlog/disqus_countcode') ?>
<?php endif ?>

<?php // A handful of fundamental improvements like a-autosubmit for anchor buttons ?>
<?php a_js_call('apostrophe.setMessages(?)', array('updating' => a_('Updating...'), 'updated' => a_('Updated'))) ?>
<?php a_js_call('apostrophe.smartCSS()') ?>
<?php // End of body - time to emit all of the queued JS as one script block ?>
<?php a_include_js_calls() ?>