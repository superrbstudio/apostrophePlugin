[?php use_helper('a', 'Date') ?]
[?php include_partial('<?php echo $this->getModuleName() ?>/assets') ?]

[?php slot('a-subnav') ?]
<div class="a-ui a-subnav-wrapper admin">
	<div class="a-subnav-inner">
		[?php include_partial('<?php echo $this->getModuleName() ?>/form_header', array('<?php echo $this->getSingularName() ?>' => $<?php echo $this->getSingularName() ?>, 'form' => $form, 'configuration' => $configuration)) ?]
	</div>	
</div>
[?php end_slot() ?]

<div class="a-ui a-admin-container [?php echo $sf_params->get('module') ?]">
  <?php // We want to allow overrides of the singular and plural labels at runtime, ?>
  <?php // but we don't want to break the ability to have placeholders for the fields of the ?>
  <?php // object, so we have to reproduce a little bit of getI18NString here ?>
  [?php $itemRaw = $sf_data->getRaw('<?php echo $this->getSingularName() ?>') ?]
  [?php include_partial('<?php echo $this->getModuleName() ?>/form_bar', 
  	array('title' => a_($configuration->getValue('edit.title'), aArray::wrapKeys($itemRaw->toArray(), '%%', '%%')))) ?]

  <div class="a-admin-content main">
	  [?php include_partial('<?php echo $this->getModuleName() ?>/flashes') ?]
 		[?php include_partial('<?php echo $this->getModuleName() ?>/form', array('<?php echo $this->getSingularName() ?>' => $<?php echo $this->getSingularName() ?>, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?]
  </div>

  <div class="a-admin-footer">
 		[?php include_partial('<?php echo $this->getModuleName() ?>/form_footer', array('<?php echo $this->getSingularName() ?>' => $<?php echo $this->getSingularName() ?>, 'form' => $form, 'configuration' => $configuration)) ?]
  </div>

</div>
