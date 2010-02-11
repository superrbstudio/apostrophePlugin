<?php use_helper('a') ?>
<?php $page = aTools::getCurrentPage() ?>
	
<div id="a-subnav" class="subnav">
	<div class="a-subnav-wrapper">
		<?php // echo a_navcolumn(false) ?>
		<?php $drag = $page->userHasPrivilege('manage') ?>
		<?php include_component('aNavigation', 'tabs', array('root' => $page->slug, 'active' => $page->slug, 'name' => 'subnav', 'draggable' => $drag, 'dragIcon' => $drag)) # Top Level Navigation ?>
	</div>
</div>