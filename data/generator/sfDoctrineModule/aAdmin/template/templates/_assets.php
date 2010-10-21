[?php use_helper('a')?]

[?php slot('body_class') ?]a-admin a-admin-generator [?php echo $sf_params->get('module'); ?] [?php echo $sf_params->get('action');?] [?php end_slot() ?]

<?php if (isset($this->params['css'])): ?> 

	[?php use_stylesheet('<?php echo $this->params['css'] ?>', 'first') ?] 

<?php endif; ?>

[?php aTools::setAllowSlotEditing(false); ?]