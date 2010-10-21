<?php use_helper('a') ?>

<?php // Project level hook. Our default version is empty ?>
<?php a_js_call('apostrophe.ready()') ?>

<?php if ($sf_user->isAuthenticated()): ?>
	<?php a_js_call('apostrophe.enableCloseHistoryButtons(?)', array('close_history_buttons' => '#a-history-close-button, #a-history-heading-button')) ?>
	<?php a_js_call('apostrophe.smartCSS()') ?>
<?php endif ?>