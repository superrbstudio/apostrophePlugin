[?php use_helper('I18N', 'Date', 'jQuery') ?]
[?php include_partial('<?php echo $this->getModuleName() ?>/assets') ?]

<div class="a-admin-container [?php echo $sf_params->get('module') ?]">

  [?php include_partial('<?php echo $this->getModuleName() ?>/list_bar', array('filters' => $filters)) ?]

	[?php slot('a-subnav') ?]
	<div class="a-subnav-wrapper admin">
		<div class="a-subnav-inner">
			<ul class="a-controls a-admin-action-controls">
				<?php if ($this->configuration->hasFilterForm()): ?>
	  			<li class="filters">[?php echo jq_link_to_function("Filters", "$('#a-admin-filters-container').slideToggle()" ,array('class' => 'a-btn icon a-settings', 'title'=>'Filter Data')) ?]</li>
				<?php endif; ?>
					<li>[?php include_partial('<?php echo $this->getModuleName() ?>/list_header', array('pager' => $pager)) ?]</li>
			</ul>
		</div>
  </div>
	[?php end_slot() ?]

	<div class="a-admin-content main">
		<ul id="a-admin-list-actions" class="a-controls a-admin-action-controls">
  		[?php include_partial('<?php echo $this->getModuleName() ?>/list_actions', array('helper' => $helper)) ?]		
		</ul>
		<?php if ($this->configuration->hasFilterForm()): ?>
		  [?php include_partial('<?php echo $this->getModuleName() ?>/filters', array('form' => $filters, 'configuration' => $configuration)) ?]
		<?php endif; ?>

		[?php include_partial('<?php echo $this->getModuleName() ?>/flashes') ?]
		<?php if ($this->configuration->getValue('list.batch_actions')): ?>
			<form action="[?php echo url_for('<?php echo $this->getUrlForAction('collection') ?>', array('action' => 'batch')) ?]" method="post" id="a-admin-batch-form">
		<?php endif; ?>
		[?php include_partial('<?php echo $this->getModuleName() ?>/list', array('pager' => $pager, 'sort' => $sort, 'helper' => $helper)) ?]
				<ul class="a-admin-actions">
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
