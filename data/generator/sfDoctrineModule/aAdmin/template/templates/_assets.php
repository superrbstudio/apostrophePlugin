[?php slot('body_class') ?]a-admin a-admin-generator [?php echo $sf_params->get('module'); ?] [?php echo $sf_params->get('action');?] [?php end_slot() ?]

<?php if (isset($this->params['css'])): ?> 

	[?php use_stylesheet('<?php echo $this->params['css'] ?>', 'first') ?] 

<?php else: ?> 

	[?php use_stylesheet('/apostrophePlugin/css/a.css', 'first') ?]

	[?php use_javascript('/apostrophePlugin/js/aControls.js') ?]
	[?php use_javascript('/apostrophePlugin/js/aUI.js') ?]

	[?php use_stylesheet('/apostrophePlugin/css/ui-apostrophe/jquery-ui-1.7.2.custom.css', 'first') # JQ Date Picker Styles (This is the custome Apostrophe styles for the JQ Date Picker) ?]
	[?php use_javascript('/apostrophePlugin/js/plugins/jquery-ui-1.7.2.custom.min.js', 'last') # JQ Date Picker JS (This can/should be consolidated with sfJqueryReloadedPlugin/js/jquery-ui-sortable...) ?]

	[?php use_stylesheet('/sfJqueryReloadedPlugin/css/JqueryAutocomplete.css', 'first') # Autocomplete Plugin for Time Picker CSS ?]
	[?php use_javascript('/sfJqueryReloadedPlugin/js/plugins/jquery.autocomplete.min.js', 'last') # Autocomplete Plugin for Time Picker JS ?]

<?php endif; ?>

[?php aTools::setAllowSlotEditing(false); ?]