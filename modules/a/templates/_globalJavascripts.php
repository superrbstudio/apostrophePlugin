<?php use_helper('a') ?>

<?php a_js_call('apostrophe.enableBrowseHistoryButtons(?)', array('history_buttons' => 'a.a-history-btn')) ?>
<?php a_js_call('apostrophe.enableCloseHistoryButtons(?)', array('close_history_buttons' => '#a-history-close-button, #a-history-heading-button')) ?>
