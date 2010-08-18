<?php
  // Compatible with sf_escaping_strategy: true
  $label = isset($label) ? $sf_data->getRaw('label') : null;
  $limitSizes = isset($limitSizes) ? $sf_data->getRaw('limitSizes') : null;
  $pager = isset($pager) ? $sf_data->getRaw('pager') : null;
  $pagerUrl = isset($pagerUrl) ? $sf_data->getRaw('pagerUrl') : null;
  $results = isset($results) ? $sf_data->getRaw('results') : null;
?>
<?php use_helper('I18N','jQuery','a') ?>
<?php slot('body_class') ?>a-media<?php end_slot() ?>
<?php $type = aMediaTools::getAttribute('type') ?>
<?php $selecting = aMediaTools::isSelecting() ?>

<div id="a-media-plugin">

<?php include_component('aMedia', 'browser') ?>

<?php if (aMediaTools::isSelecting() || aMediaTools::userHasUploadPrivilege()): ?>
<div class="a-media-toolbar">
	<?php if (aMediaTools::isSelecting()): ?>

    <?php if (($type === 'image') || (aMediaTools::isMultiple())): ?>
      <?php include_component('aMedia', 'selectMultiple', array('limitSizes' => $limitSizes, 'label' => (isset($label)?$label:null))) ?>
    <?php else: ?>
      <?php include_component('aMedia', 'selectSingle', array('limitSizes' => $limitSizes, 'label' => (isset($label)?$label:null))) ?>
    <?php endif ?>
	<?php endif ?>

</div>

<?php endif ?>

<div class="a-media-library">
	
	<h3>Media Library</h3>
	
	<?php include_partial('aMedia/describeConstraints', array('limitSizes' => $limitSizes)) ?>
	
	<?php if (aMediaTools::userHasUploadPrivilege()): ?>
   <ul class="a-ui a-controls">

     <?php if (!($selecting && $type && ($type !== 'image'))): ?>
     <li><a href="<?php echo url_for("aMedia/uploadImages") ?>" class="a-btn icon big a-add"><?php echo __('Add Images', null, 'apostrophe') ?></a></li>
     <?php endif ?>

     <?php if (!($selecting && $type && ($type !== 'video'))): ?>
     <li><a href="<?php echo url_for("aMedia/newVideo") ?>" class="a-btn icon big a-add"><?php echo __('Add Video', null, 'apostrophe') ?></a></li>
     <?php endif ?>

     <?php if (!($selecting && $type && ($type !== 'pdf'))): ?>
     <li><a href="<?php echo url_for("aMedia/editPdf") ?>" class="a-btn icon big a-add"><?php echo __('Add PDF', null, 'apostrophe') ?></a></li>
     <?php endif ?>
   </ul>
  <?php endif ?>
	
 <?php for ($n = 0; ($n < count($results)); $n += 2): ?>
   <div class="a-media-row">
   	<?php for ($i = $n; ($i < min(count($results), $n + 2)); $i++): ?>
     <?php $mediaItem = $results[$i] ?>
      <ul id="a-media-item-<?php echo $mediaItem->getId() ?>" class="a-media-item <?php echo ($i % 2) ? "odd" : "even" ?>">
        <?php include_partial('aMedia/mediaItem', array('mediaItem' => $mediaItem)) ?>
      </ul>
   	<?php endfor ?>
   </div>
 <?php endfor ?>
</div>

<div class="a-media-footer">
 <?php include_partial('aPager/pager', array('pager' => $pager, 'pagerUrl' => $pagerUrl)) ?>
</div>
</div>