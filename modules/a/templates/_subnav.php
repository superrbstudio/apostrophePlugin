<?php use_helper('a') ?>
<?php $page = aTools::getCurrentPage() ?>
	
<div class="a-subnav-wrapper">
	<div class="a-subnav-inner">
		<?php // echo a_navcolumn(false) ?>
		<?php $drag = $page->userHasPrivilege('manage') ?>
		<?php include_component('aNavigation', 'tabs', array('root' => $page->slug, 'active' => $page->slug, 'name' => 'subnav', 'draggable' => $drag, 'dragIcon' => $drag)) # Top Level Navigation ?>
	</div>
</div>