<?php use_helper('a') ?>

<div id="a-subnav" class="subnav">
	<?php // echo a_navcolumn(false) ?>
	<?php include_component('aNavigation', 'tabs', array('root' => $page->slug, 'active' => $page->slug, 'name' => 'subnav')) # Top Level Navigation ?>
</div>