<?php slot('body_class') ?>a-media<?php end_slot() ?>

<?php use_helper('jQuery') ?>

<div id="a-media-plugin">

<?php include_component('aMedia', 'browser') ?>

<div class="a-media-toolbar">
  <h3>
		<?php if ($item): ?> 
			Editing Video: <?php echo $item->getTitle() ?>
    <?php else: ?> 
			Add Video 
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
  <h3>That is not a valid YouTube video URL.</h3>
  <?php endif ?>

  <form method="POST" id="a-media-edit-form" enctype="multipart/form-data" action="<?php echo url_for(aUrl::addParams("aMedia/editVideo", array("slug" => $slug)))?>">

    <div class="form-row title">
      <?php echo $form['title']->renderLabel() ?>
      <?php if (!$sf_params->get('first_pass')): ?>
        <?php echo $form['title']->renderError() ?>
      <?php endif ?>
      <?php echo $form['title']->render() ?>
    </div>

    <?php if (isset($form['service_url'])): ?>
      <div class="form-row service-url">
        <?php echo $form['service_url']->renderRow() ?>
      </div>
    <?php endif ?>

    <?php if (isset($form['embed'])): ?>
      <div class="form-row embed">
        <?php echo $form['embed']->renderRow() ?>
      </div>
      <div class="form-row thumbnail">
        <?php echo $form['thumbnail']->renderLabel() ?>
        <?php if (!$sf_params->get('first_pass')): ?>
          <?php echo $form['thumbnail']->renderError() ?>
        <?php endif ?>
        <?php echo $form['thumbnail']->render() ?>
      </div>
    <?php endif ?>

    <div class="form-row description">
      <?php echo $form['description']->renderLabel() ?>
      <?php echo $form['description']->renderError() ?>
      <?php echo $form['description']->render() ?>
    </div>

    <div class="form-row credit">
      <?php echo $form['credit']->renderRow() ?>
    </div>

    <div class="form-row permissions">
      <?php echo $form['view_is_secure']->renderRow() ?>
    </div>

    <div class="form-row categories"><?php echo $form['media_categories_list']->renderRow() ?></div>

    <div class="form-row about-tags">
    Tags should be separated by commas. Example: student life, chemistry, laboratory
    </div>

    <div class="form-row tags">
      <?php echo $form['tags']->renderRow(array("id" => "a-media-video-tags")) ?>
    </div>

    <ul class="a-controls a-media-edit-footer">
      <li><input type="submit" value="Save" class="a-submit" /></li>
      <?php if ($item): ?>
      <li><?php echo link_to("Delete", "aMedia/delete?" . http_build_query(
          array("slug" => $slug)),
          array("confirm" => "Are you sure you want to delete this item?",
            "target" => "_top", "class"=>"a-btn icon a-delete")) ?></li>
      <?php endif ?>
			<li><?php echo link_to("Cancel", "aMedia/resumeWithPage", array("class"=>"a-cancel a-btn icon event-default")) ?></li>
    </ul>
  </form>
</div>

<?php include_partial('aMedia/itemFormScripts') ?>

</div>