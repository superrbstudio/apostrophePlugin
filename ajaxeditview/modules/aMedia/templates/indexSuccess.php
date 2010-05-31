<?php use_helper('I18N') ?>
<?php slot('body_class') ?>a-media<?php end_slot() ?>

<?php use_helper('jQuery') ?>

<div id="a-media-plugin">

<?php include_component('aMedia', 'browser') ?>

<?php if (aMediaTools::isSelecting() || aMediaTools::userHasUploadPrivilege()): ?>
<div class="a-media-toolbar">
	<?php if (aMediaTools::isSelecting()): ?>

    <?php if (isset($label)): ?>
      <h3><?php echo htmlspecialchars($label) ?> or <?php echo link_to(__("Cancel", null, 'apostrophe'), "aMedia/selectCancel", array("class"=>"a-btn a-cancel text-only")) ?></h3>
    <?php endif ?>

    <?php include_partial('aMedia/describeConstraints') ?>

	  <?php if (aMediaTools::isMultiple()): ?>
	    <?php include_partial('aMedia/selectMultiple', array('limitSizes' => $limitSizes)) ?>
	  <?php else: ?>
	    <?php include_partial('aMedia/selectSingle', array('limitSizes' => $limitSizes)) ?>
	  <?php endif ?>

	<?php endif ?>

  <?php if (aMediaTools::userHasUploadPrivilege()): ?>

   <ul class="a-controls a-media-controls">
     <?php $selecting = aMediaTools::isSelecting() ?>
     <?php $type = aMediaTools::getAttribute('type') ?>

     <?php if (!($selecting && $type && ($type !== 'image'))): ?>
     <li><a href="<?php echo url_for("aMedia/uploadImages") ?>" class="a-btn icon a-add"><?php echo __('Add Images', null, 'apostrophe') ?></a></li>
     <?php endif ?>

     <?php if (!($selecting && $type && ($type !== 'video'))): ?>
     <li><a href="<?php echo url_for("aMedia/newVideo") ?>" class="a-btn icon a-add"><?php echo __('Add Video', null, 'apostrophe') ?></a></li>
     <?php endif ?>

     <?php if (!($selecting && $type && ($type !== 'pdf'))): ?>
     <li><a href="<?php echo url_for("aMedia/editPdf") ?>" class="a-btn icon a-add"><?php echo __('Add PDF', null, 'apostrophe') ?></a></li>
     <?php endif ?>

   </ul>

  <?php endif ?>
</div>

<?php endif ?>

<div class="a-media-library">
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