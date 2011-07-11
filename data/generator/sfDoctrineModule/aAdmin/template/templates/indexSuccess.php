[?php use_helper('a', 'Date') ?]
[?php include_partial('<?php echo $this->getModuleName() ?>/assets') ?]

[?php slot('a-page-header')?]
<div class="a-ui a-admin-header">
	<h3 class="a-admin-title">[?php echo __('<?php echo $this->configuration->getValue('list.title') ?>', array(), 'apostrophe') ?]</h3>
	<ul class="a-ui a-controls a-admin-controls">
    [?php include_partial('<?php echo $this->getModuleName() ?>/list_actions', array('helper' => $helper)) ?]   
  </ul>
	<?php if ($this->configuration->hasFilterForm()): ?>
		[?php echo a_js_button(a_('Filters'), array('icon','a-filters','lite', 'alt','a-align-right', 'big'), 'a-admin-filters-open-button') ?]
	<?php endif ?>
</div>
[?php end_slot() ?]

[?php slot('a-subnav') ?]
<div class="a-ui a-subnav-wrapper admin">
	<div class="a-subnav-inner">
		<ul class="a-ui a-controls">
				<li>[?php include_partial('<?php echo $this->getModuleName() ?>/list_header', array('pager' => $pager)) ?]</li>
		</ul>
	</div>
</div>
[?php end_slot() ?]

<div class="a-ui a-admin-container [?php echo $sf_params->get('module') ?]">

	<div class="a-admin-content main">
		
		<?php if ($this->configuration->hasFilterForm()): ?>
		  [?php include_partial('<?php echo $this->getModuleName() ?>/filters', array('form' => $filters, 'configuration' => $configuration, 'filtersActive' => $filtersActive)) ?]
		<?php endif; ?>

		[?php include_partial('<?php echo $this->getModuleName() ?>/flashes') ?]
		
		<?php if ($this->configuration->getValue('list.batch_actions')): ?>
			<form action="[?php echo url_for('<?php echo $this->getUrlForAction('collection') ?>', array('action' => 'batch')) ?]" method="post" id="a-admin-batch-form">
		<?php endif; ?>
		
		[?php include_partial('<?php echo $this->getModuleName() ?>/list', array('pager' => $pager, 'sort' => $sort, 'helper' => $helper)) ?]

		<ul class="a-ui a-admin-actions">
      [?php include_partial('<?php echo $this->getModuleName() ?>/list_batch_actions', array('helper' => $helper)) ?]
    </ul>

		<?php if ($this->configuration->getValue('list.batch_actions')): ?>
		  </form>
		<?php endif; ?>
		
	</div>

  <div class="a-admin-footer">
    [?php include_partial('<?php echo $this->getModuleName() ?>/list_footer', array('pager' => $pager)) ?]
  </div>

</div>

[?php a_js_call('apostrophe.aAdminEnableFilters()') ?]