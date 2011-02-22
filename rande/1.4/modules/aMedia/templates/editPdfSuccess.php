<?php use_helper('I18N', 'jQuery') ?>
<?php slot('body_class') ?>a-media<?php end_slot() ?>

<div id="a-media-plugin">

<?php include_component('aMedia', 'browser') ?>

<div class="a-media-toolbar">
  <h3>
		<?php if ($item): ?> 
			<?php echo __('Editing PDF: %title%', array('%title%' => $item->getTitle()), 'apostrophe') ?>
    <?php else: ?> 
			<?php echo __('Add PDF', null, 'apostrophe') ?> 
		<?php endif ?>
   </h3>
</div>

<div class="a-media-library">				

  <?php if ($item): ?>
  	<?php $slug = $item->getSlug() ?>
  <?php else: ?>
  	<?php $slug = false ?>
  <?php endif ?>

  <?php // Post-form-validation error when we tried to get the thumbnail ?>
  <?php if (isset($serviceError)): ?>
  <h3><?php echo __('That is not a valid PDF.', null, 'apostrophe') ?></h3>
  <?php endif ?>

  <form method="POST" id="a-media-edit-form" enctype="multipart/form-data" action="<?php echo url_for(aUrl::addParams("aMedia/editPdf", array("slug" => $slug)))?>">
  	<?php echo $form->renderHiddenFields() ?>

    <div class="a-form-row file">
      <?php echo $form['file']->renderLabel() ?>
      <?php echo $form['file']->renderError() ?>
      <?php echo $form['file']->render() ?>
    </div>

    <div class="a-form-row title">
      <?php echo $form['title']->renderLabel() ?>
      <?php echo $form['title']->renderError() ?>
      <?php echo $form['title']->render() ?>
    </div>

    <div class="a-form-row description">
      <?php echo $form['description']->renderLabel() ?>
      <?php echo $form['description']->renderError() ?>
      <?php echo $form['description']->render() ?>
    </div>

    <div class="a-form-row credit">
      <?php echo $form['credit']->renderRow() ?>
    </div>

    <div class="a-form-row permissions">
      <?php echo $form['view_is_secure']->renderRow() ?>
    </div>

    <div class="a-form-row categories"><?php echo $form['media_categories_list']->renderRow() ?></div>

    <div class="a-form-row about-tags">
    <?php echo __('Tags should be separated by commas. Example: student life, chemistry, laboratory', null, 'apostrophe') ?>
    </div>

    <div class="a-form-row tags">
      <?php echo $form['tags']->renderRow(array("id" => "a-media-pdf-tags")) ?>
    </div>

    <ul class="a-controls a-media-edit-footer">
      <li><input type="submit" value="<?php echo __('Save', null, 'apostrophe') ?>" class="a-submit" /></li>
      <?php if ($item): ?>
      <li><?php echo link_to(__("Delete", null, 'apostrophe'), "aMedia/delete?" . http_build_query(
          array("slug" => $slug)),
          array("confirm" => __("Are you sure you want to delete this item?", null, 'apostrophe'),
            "target" => "_top", "class"=>"a-btn icon a-delete")) ?></li>
      <?php endif ?>
			<li><?php echo link_to(__("Cancel", null, 'apostrophe'), "aMedia/resumeWithPage", array("class"=>"a-btn a-cancel")) ?></li>
    </ul>
  </form>
</div>

<?php include_partial('aMedia/itemFormScripts') ?>

</div>