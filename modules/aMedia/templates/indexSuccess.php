<?php
  // Compatible with sf_escaping_strategy: true
  $label = isset($label) ? $sf_data->getRaw('label') : null;
  $limitSizes = isset($limitSizes) ? $sf_data->getRaw('limitSizes') : null;
  $pager = isset($pager) ? $sf_data->getRaw('pager') : null;
  $pagerUrl = isset($pagerUrl) ? $sf_data->getRaw('pagerUrl') : null;
  $results = isset($results) ? $sf_data->getRaw('results') : null;
?>

<?php use_helper('a') ?>

<?php $type = aMediaTools::getAttribute('type') ?>
<?php $selecting = aMediaTools::isSelecting() ?>
<?php $multipleStyle = (($type === 'image') || (aMediaTools::isMultiple())) ?>

<?php $body_class = 'a-media a-media-index'?>
<?php $body_class .= ($selecting) ? ' a-media-selecting':'' ?>
<?php $body_class .= ' '.$layout['name'] ?>

<?php slot('body_class', $body_class) ?>

<?php slot('a-page-header') ?>
	<?php include_partial('aMedia/mediaHeader', array('uploadAllowed' => $uploadAllowed, 'embedAllowed' => $embedAllowed)) ?>
<?php end_slot() ?>

<?php slot('a-media-library-controls') ?>
  <?php include_partial('aMedia/pager', array('pager' => $pager, 'pagerUrl' => $pagerUrl, 'max_per_page' => $max_per_page, 'enabled_layouts' => $enabled_layouts, 'layout' => $layout)) ?>
<?php end_slot() ?>

<div class="a-media-library clearfix">

  <?php include_partial('aMedia/addForm', array('uploadAllowed' => $uploadAllowed, 'embedAllowed' => $embedAllowed)) ?>
  
	<?php if (aMediaTools::isSelecting() || aMediaTools::userHasUploadPrivilege()): ?>
			<?php if (aMediaTools::isSelecting()): ?>
				<div class="a-media-selection">
			    <?php if ($multipleStyle): ?>
			      <?php include_component('aMedia', 'selectMultiple', array('limitSizes' => $limitSizes, 'label' => (isset($label)?$label:null))) ?>
			    <?php else: ?>
			      <?php include_component('aMedia', 'selectSingle', array('limitSizes' => $limitSizes, 'label' => (isset($label)?$label:null))) ?>
			    <?php endif ?>
				</div>		
			<?php endif ?>
	<?php endif ?>

	<?php if ($pager->count()): ?>
	<div class="a-media-library-controls a-ui top">
		<?php include_slot('a-media-library-controls') ?>
	</div>
	<?php endif ?>
	
	<?php // This should never have been disabled for cases where there are zero images matching. ?>
	<?php // That is exactly when you need it most to understand why you don't see nuttin'! ?>
	<?php // Overrides to the contrary must be at project level only. -Tom ?>
	<?php if ($limitSizes): ?>
		<div class="a-media-selection-contraints clearfix">
			<?php include_partial('aMedia/describeConstraints', array('limitSizes' => $limitSizes)) ?>		
		</div>
	<?php endif ?>

	<div class="a-media-items clearfix <?php echo $layout['name'] ?>">
	 <?php for ($n = 0; ($n < count($results)); $n += $layout['columns']): ?>
	   <div class="a-media-row clearfix">
	   	<?php for ($i = $n; ($i < min(count($results), $n + $layout['columns'])); $i++): ?>
        <?php include_partial('aMedia/mediaItem', array('mediaItem' => $results[$i], 'layout' => $layout, 'i' => $i, 'selecting' => $selecting, 'autoplay' => true)) ?>
	   	<?php endfor ?>
	   </div>
	 <?php endfor ?>
	</div>

	<?php if ((!$pager->count()) && (aMediaTools::userHasUploadPrivilege())): ?>
		<?php include_partial('aMedia/noMediaItemsArea') ?>
	<?php endif ?>

	<div class="a-media-footer clearfix">
		<div class="a-media-library-controls a-ui bottom">
			<?php include_slot('a-media-library-controls') ?>
		</div>
	</div>
	
</div>

<?php // Media Sidebar is wrapped slot('a-subnav') ?>
<?php include_component('aMedia', 'browser') ?>

<?php a_js_call('apostrophe.selectOnFocus(?)', '.a-select-on-focus') ?>

<?php if ($layout['name'] != "four-up"): ?>
	<?php a_js_call('apostrophe.mediaEmbeddableToggle(?)', array('selector' => '.a-media-item.a-embedded-item')) ?>
<?php endif ?>

<?php if ($layout['name'] == "four-up" && !$selecting): ?>
<?php a_js_call('apostrophe.mediaFourUpLayoutEnhancements(?)', array('selector' => '.four-up .a-media-item.a-type-image')) ?>
<?php endif ?>