[?php include_stylesheets_for_form($form) ?]
[?php include_javascripts_for_form($form) ?]
[?php use_helper('a') ?]

<div id="a-admin-filters-container" class="a-ui [?php echo $filtersActive ? 'a-active' : '' ?]">

	  <form action="[?php echo url_for('<?php echo $this->getUrlForAction('collection') ?>', array('action' => 'filter')) ?]" method="post" id="a-admin-filters-form">
		
			<h3>[?php echo __('Filters', null, 'apostrophe') ?]</h3>

		  [?php if ($form->hasGlobalErrors()): ?]
		    [?php echo $form->renderGlobalErrors() ?]
		  [?php endif; ?]
		
	    <div class="a-admin-filters-fields">

	        [?php foreach ($configuration->getFormFilterFields($form) as $name => $field): ?]
	        [?php if ((isset($form[$name]) && $form[$name]->isHidden()) || (!isset($form[$name]) && $field->isReal())) continue ?]
					<div class="a-form-row" id="a-admin-filters-[?php echo str_replace("_","-",$name) ?]">
	          [?php include_partial('<?php echo $this->getModuleName() ?>/filters_field', array(
	            'name'       => $name,
	            'attributes' => $field->getConfig('attributes', array()),
	            'label'      => $field->getConfig('label'),
	            'help'       => $field->getConfig('help'),
	            'form'       => $form,
	            'field'      => $field,
	            'class'      => 'a-form-row a-admin-'.strtolower($field->getType()).' a-admin-filter-field-'.$name,
	          )) ?]
					</div>
	        [?php endforeach; ?]

        [?php echo $form->renderHiddenFields() ?]
				<div class="a-form-row submit">
					<ul class="a-ui a-controls">
						<li>[?php echo a_anchor_submit_button(a_('Filter')) ?]</li>
						<li>[?php echo link_to('<span class="icon"></span>'.a_('Reset'), '<?php echo $this->getUrlForAction('collection') ?>', array('action' => 'filter'), array('query_string' => '_reset', 'method' => 'post', 'class' => 'a-btn icon a-cancel alt')) ?]</li>
					</ul>
				</div>
				
	    </div>
	  </form>

</div>
