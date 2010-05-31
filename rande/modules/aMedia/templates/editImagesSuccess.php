<?php use_helper('I18N', 'jQuery') ?>
<?php slot('body_class') ?>a-media<?php end_slot() ?>

<div id="a-media-plugin">

	<?php include_component('aMedia', 'browser') ?>

	<div class="a-media-toolbar">
		<h3><?php echo __('Annotate Images', null, 'apostrophe') ?></h3>
	</div>

	<div class="a-media-library">				
		<form method="POST" action="<?php echo url_for("aMedia/editImages") ?>" enctype="multipart/form-data" id="a-media-edit-form">
		<?php echo $form->renderHiddenFields() ?>
  
		<input type="hidden" name="active" value="<?php echo implode(",", $active) ?>" />

		<?php $n = 0 ?>

		<ul>
			<?php for ($i = 0; ($i < aMediaTools::getOption('batch_max')); $i++): ?>
			  <?php if (isset($form["item-$i"])): ?>
			    <?php // What we're passing here is actually a widget schema ?>
			    <?php // (they get nested when embedded forms are present), but ?>
			    <?php // it supports the same methods as a form for rendering purposes ?>
			    <?php include_partial('aMedia/editImage', 
								array(
									"item" => false, 
			        		"firstPass" => $firstPass, 
									"form" => $form["item-$i"], 
									"n" => $n, 
									'i' => $i,
									'itemFormScripts' => 'false',
									)) ?>
					<?php $n++ ?>
			  <?php endif ?>
			<?php endfor ?>
		</ul>

		<?php include_partial('aMedia/itemFormScripts', array('i'=>$i)) ?>

		<?php //We should wrap this with logic to say 'photo' if only one object has been uploaded ?>
		<ul class="a-controls a-media-edit-footer">
			<li><input type="submit" name="submit" value="<?php echo __('Save Images', null, 'apostrophe') ?>" class="a-submit" /></li>
			<li><?php echo link_to(__("Cancel", null, 'apostrophe'), "aMedia/resume", array("class"=>"a-cancel a-btn icon event-default")) ?></li>
		</ul>
		</form>
	</div>
</div>