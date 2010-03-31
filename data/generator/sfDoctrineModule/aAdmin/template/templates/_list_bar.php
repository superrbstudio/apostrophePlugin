<div class="a-admin-bar" [?php if (count($sf_user->getAttribute('<?php echo $this->getModuleName() ?>.filters', null, 'admin_module'))): ?]class="has-filters"[?php endif ?]>
	<h2 class="a-admin-title you-are-here">[?php echo <?php echo $this->getI18NString('list.title') ?> ?]</h2>
</div>