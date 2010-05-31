[?php slot('body_class') ?]a-admin [?php echo $sf_params->get('module'); ?] [?php echo $sf_params->get('action');?] [?php end_slot() ?]

<?php if (isset($this->params['css'])): ?> 
[?php use_stylesheet('<?php echo $this->params['css'] ?>', 'first') ?] 
<?php else: ?> 
[?php slot('body_class') ?]a-admin [?php echo $sf_params->get('action'); ?] [?php end_slot() ?]

[?php // use_stylesheet('/apostrophePlugin/css/aToolkit.css', 'first') // Merged into a.css 2/3/2010 ?]
[?php // use_stylesheet('/apostrophePlugin/css/aAdmin.css', 'first') // Merged into a.css 2/3/2010 ?]
[?php use_stylesheet('/apostrophePlugin/css/a.css', 'first') ?]

[?php use_javascript('/apostrophePlugin/js/aControls.js') ?]
[?php use_javascript('/apostrophePlugin/js/aUI.js') ?]

[?php use_stylesheet('/sfJqueryReloadedPlugin/css/ui-lightness/jquery-ui-1.7.2.custom.css', 'first') # JQ Date Picker Styles (This doesn't have to be the ui.all.css, we could make a custom css later ) ?]
[?php use_javascript('/sfJqueryReloadedPlugin/js/plugins/jquery-ui-1.7.2.custom.min.js', 'last') # JQ Date Picker JS (This can/should be consolidated with sfJqueryReloadedPlugin/js/jquery-ui-sortable...) ?]
<?php endif; ?>

[?php aTools::setAllowSlotEditing(false); ?]