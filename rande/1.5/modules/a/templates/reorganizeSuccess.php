<?php
  // Compatible with sf_escaping_strategy: true
  $treeData = isset($treeData) ? $sf_data->getRaw('treeData') : null;
?>
<?php use_helper('a') ?>

<?php slot('body_class','a-admin a-reorganize') ?>
<?php slot('tabs','') ?>

<?php sfContext::getInstance()->getResponse()->addJavascript('/apostrophePlugin/js/jsTree/_lib/css.js') ?>
<?php sfContext::getInstance()->getResponse()->addJavascript('/apostrophePlugin/js/jsTree/source/tree_component.js') ?>
<?php sfContext::getInstance()->getResponse()->addStylesheet('/apostrophePlugin/js/jsTree/source/tree_component.css') ?>

<div id="a-page-tree-container" class="a-page-tree-container">
	<h2 class="a-page-tree-title"><?php echo __('Drag and drop pages to reorganize the site.', null, 'apostrophe') ?></h2>
	<div id="a-page-tree" class="a-page-tree"></div>
	<div class="a-page-tree-in-progress"></div>
</div>

<?php a_js_call('apostrophe.jsTree(?)', array('treeData' => $treeData, 'moveURL' => url_for("a/treeMove"))) ?>