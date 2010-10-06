<?php
  // Compatible with sf_escaping_strategy: true
  $label = isset($label) ? $sf_data->getRaw('label') : null;
  $limitSizes = isset($limitSizes) ? $sf_data->getRaw('limitSizes') : null;
  $pager = isset($pager) ? $sf_data->getRaw('pager') : null;
  $pagerUrl = isset($pagerUrl) ? $sf_data->getRaw('pagerUrl') : null;
  $results = isset($results) ? $sf_data->getRaw('results') : null;
?>

<?php use_helper('a') ?>
<?php slot('body_class','a-media a-media-index') ?>

<?php $type = aMediaTools::getAttribute('type') ?>
<?php $selecting = aMediaTools::isSelecting() ?>
<?php $multipleStyle = (($type === 'image') || (aMediaTools::isMultiple())) ?>

<?php slot('a-page-header') ?>
	<?php include_partial('aMedia/mediaHeader', array('uploadAllowed' => $uploadAllowed, 'embedAllowed' => $embedAllowed)) ?>
<?php end_slot() ?>

<?php slot('a-media-library-controls') ?>
<?php $views = array(20, 50, 100) ?>
<?php include_partial('aPager/pager', array('pager' => $pager, 'pagerUrl' => $pagerUrl)) ?>
<ul class="a-ui a-controls a-media-footer-controls">
	<li class="a-media-footer-item-count"><?php echo $pager->count() ?> items</li>
	<li class="a-media-footer-separator a">|</li>
	<li class="a-media-footer-view-label">view</li>
	<?php foreach($views as $n): ?>
		<li class="a-media-footer-view-option"><?php echo link_to($n, aUrl::addParams($pagerUrl, array('max_per_page' => $n)), array('class' => 'a-btn lite alt', )) ?></li>
	<?php endforeach ?>
	<li class="a-media-footer-separator b">|</li>
	<?php foreach($enabled_layouts as $enabled_layout): ?>
		<li class="a-media-footer-layout-option"><?php echo link_to('<span class="icon" style="background-image:url('.$enabled_layout['image'].');"></span>'.$enabled_layout['name'],  aUrl::addParams($pagerUrl, array('layout' => $enabled_layout['name'])), array('alt' => $enabled_layout['name'], 'class' => 'a-btn icon lite no-label')) ?></li>
	<?php endforeach; ?>
</ul>
<?php end_slot() ?>

<div class="a-media-library">

	<div class="a-media-library-controls top">
		<?php include_slot('a-media-library-controls') ?>
	</div>

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

	<?php if ($limitSizes): ?>
		<div class="a-media-selection-contraints">
			<?php include_partial('aMedia/describeConstraints', array('limitSizes' => $limitSizes)) ?>		
		</div>
	<?php endif ?>

	<div class="a-media-items <?php echo $layout['name'] ?>">
	 <?php for ($n = 0; ($n < count($results)); $n += $layout['columns']): ?>
	   <div class="a-media-row">
	   	<?php for ($i = $n; ($i < min(count($results), $n + $layout['columns'])); $i++): ?>
        <?php include_partial('aMedia/mediaItem', array('mediaItem' => $results[$i], 'layout' => $layout, 'i' => $i )) ?>
	   	<?php endfor ?>
	   </div>
	 <?php endfor ?>
	</div>


	<div class="a-media-footer">
		<div class="a-media-library-controls bottom">
			<?php include_slot('a-media-library-controls') ?>
		</div>
	</div>
	
</div>


<?php // Media Sidebar is wrapped slot('a-subnav') ?>
<?php include_component('aMedia', 'browser') ?>

<?php a_js_call('apostrophe.selectOnFocus(?)', '.a-select-on-focus') ?>
<?php a_js_call('apostrophe.mediaEmbeddableToggle(?)', array('mediaItems' => '.a-media-item.a-embedded-item')) ?>
<?php if ($layout['name'] == "four-up" && !$selecting): ?>
	<?php a_js_call('apostrophe.mediaFourUpLayoutEnhancements(?)', array('selector' => '.four-up .a-media-item')) ?>
<?php endif ?>