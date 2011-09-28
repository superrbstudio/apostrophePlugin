[?php use_helper('a', 'Date') ?]
[?php include_partial('<?php echo $this->getModuleName() ?>/assets') ?]

[?php slot('a-page-header')?]
<div class="a-ui a-admin-header">
	<h3 class="a-admin-title">[?php echo __('<?php echo $this->configuration->getValue('edit.title') ?>', array(), 'apostrophe') ?]</h3>
</div>
[?php end_slot() ?]


[?php slot('a-subnav') ?]
<div class="a-ui a-subnav-wrapper admin">
	<div class="a-subnav-inner">
		[?php include_partial('<?php echo $this->getModuleName() ?>/form_header', array('<?php echo $this->getSingularName() ?>' => $<?php echo $this->getSingularName() ?>, 'form' => $form, 'configuration' => $configuration)) ?]
	</div>	
</div>
[?php end_slot() ?]

<div class="a-ui a-admin-container [?php echo $sf_params->get('module') ?]">
  [?php include_partial('<?php echo $this->getModuleName() ?>/form_bar', array('title' => <?php echo $this->getI18NString('edit.title') ?>)) ?]

  <div class="a-admin-content main">
	  [?php include_partial('<?php echo $this->getModuleName() ?>/flashes') ?]
 		[?php include_partial('<?php echo $this->getModuleName() ?>/form', array('<?php echo $this->getSingularName() ?>' => $<?php echo $this->getSingularName() ?>, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?]
  </div>

  <div class="a-admin-footer">
 		[?php include_partial('<?php echo $this->getModuleName() ?>/form_footer', array('<?php echo $this->getSingularName() ?>' => $<?php echo $this->getSingularName() ?>, 'form' => $form, 'configuration' => $configuration)) ?]
  </div>

</div>
