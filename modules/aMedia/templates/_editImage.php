<?php if (!isset($n)): ?> <?php $n = 0 ?> <?php endif ?>

<?php if (!$item): ?>	
<li class="a-media-item <?php echo ($n%2) ? "odd" : "even" ?>">
	<div class="a-media-item-edit-form">
<?php endif ?>

<?php if ($item): ?>
<form method="POST" id="a-media-edit-form" enctype="multipart/form-data" 
  action="<?php echo url_for(aUrl::addParams("aMedia/editImage",
    array("slug" => $item->getSlug())))?>">
<?php endif ?>

		<?php $previewAvailable = aValidatorFilePersistent::previewAvailable($form['file']->getValue()) ?>
		<?php if ($previewAvailable || $item): ?>

		<div class="a-form-row image">
		<?php if (0): ?>
		  <?php // Maybe Rick doesn't want this... ?>
		  <?php echo $form['file']->renderLabel() ?>
		<?php endif ?>
		<?php // But we must have this ?>
		<?php echo $form['file']->renderError() ?>
		<?php echo $form['file']->render() ?>
		<?php else: ?>
		<div class="a-form-row newfile">
		<?php echo $form['file']->renderRow() ?>
		</div>
		<?php endif ?>
		</div>

		<div class="a-form-row title">
		<?php echo $form['title']->renderLabel() ?>
		<?php if (!$firstPass): ?>
		  <?php echo $form['title']->renderError() ?>
		<?php endif ?>
		<?php echo $form['title']->render() ?>
		</div>

		<?php echo $form['id']->render() ?>
		<div class="a-form-row description">
			<?php echo $form['description']->renderLabel() ?>
			<?php echo $form['description']->renderError() ?>
			<?php echo $form['description']->render() ?>
		</div>
		
		<div class="a-form-row credit"><?php echo $form['credit']->renderRow() ?></div>

    <div class="a-form-row categories"><?php echo $form['media_categories_list']->renderRow() ?></div>
    <div class="a-form-row tags help">
    Tags should be separated by commas. Example: student life, chemistry, laboratory
    </div>

		<div class="a-form-row tags"><?php echo $form['tags']->renderRow() ?></div>

    <div class="a-form-row permissions help">
			Hidden Photos can be used in photo slots, but are not displayed in the Media section.
    </div>

		<div class="a-form-row permissions">

			<?php echo $form['view_is_secure']->renderLabel() ?>
			<?php echo $form['view_is_secure']->renderError() ?>
			<?php echo $form['view_is_secure']->render() ?>

			<?php if (isset($i)): ?>
			<script type="text/javascript" charset="utf-8">
			 	aRadioSelect('#a_media_items_item-<?php echo $i ?>_view_is_secure', { }); //This is for multiple editing			  
			</script>
			<?php endif ?>

		</div>

   <?php if ($item): ?>
    <ul class="a-controls a-media-form-footer">

     	<li class="a-controls-item submit"><input type="submit" value="Save" class="pk-btn a-submit" /></li>

       <?php $id = $item->getId() ?>

     	<li class="a-controls-item cancel"><?php echo link_to("cancel", "aMedia/resumeWithPage", array("class" => "a-btn icon a-cancel event-default")) ?></li>

      <li class="a-controls-item delete">
			<?php echo link_to("Delete", "aMedia/delete?" . http_build_query(
         array("slug" => $item->slug)),
         array("confirm" => "Are you sure you want to delete this item?", "class"=>"a-btn icon a-delete icon-only", 'title' => 'Delete', ),
         array("target" => "_top")) ?>
			</li>

   	</ul>
	</form>
<?php endif ?>
				
<?php if (!$item): ?>
	</div>
</li>
<?php endif ?>

<?php if (!isset($itemFormScripts)): ?>
	<?php include_partial('aMedia/itemFormScripts') ?>
<?php endif ?>
