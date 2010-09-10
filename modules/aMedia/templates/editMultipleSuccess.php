<?php
  // Compatible with sf_escaping_strategy: true
  $active = isset($active) ? $sf_data->getRaw('active') : null;
  $firstPass = isset($firstPass) ? $sf_data->getRaw('firstPass') : null;
  $form = isset($form) ? $sf_data->getRaw('form') : null;
?>
<?php use_helper('a') ?>
<?php slot('body_class') ?>a-media<?php end_slot() ?>

<div class="a-media-library">

	<?php include_component('aMedia', 'browser') ?>

	<div class="a-media-toolbar">
		<h3><?php echo __('Annotate ' . aMediaTools::getBestTypeLabel(), null, 'apostrophe') ?></h3>
	</div>

	<div class="a-media-items">				
		<form method="POST" action="<?php echo url_for("aMedia/editMultiple") ?>" enctype="multipart/form-data" id="a-media-edit-form">
		<?php echo $form->renderHiddenFields() ?>
  
		<?php $n = 0 ?>

		<ul>
			<?php for ($i = 0; ($i < aMediaTools::getOption('batch_max')); $i++): ?>
			  <?php if (isset($form["item-$i"])): ?>
			    <?php // What we're passing here is actually a widget schema ?>
			    <?php // (they get nested when embedded forms are present), but ?>
			    <?php // it supports the same methods as a form for rendering purposes ?>
			    
			    <?php // Please do not remove this div, I need it to implement the remove button ?>
			    <div id="a-media-editor-<?php echo $i ?>">
  			    <?php include_partial('aMedia/edit', 
  								array(
  									"item" => false, 
  			        		"firstPass" => $firstPass, 
  									"form" => $form["item-$i"], 
  									"n" => $n, 
  									'i' => $i,
  									'itemFormScripts' => 'false',
  									)) ?>
					</div>
					<?php $n++ ?>
			  <?php endif ?>
			<?php endfor ?>
		</ul>

		<?php include_partial('aMedia/itemFormScripts', array('i'=>$i)) ?>

		<?php //We should wrap this with logic to say 'photo' if only one object has been uploaded ?>
		<ul class="a-ui a-controls">
			<li><input type="submit" name="submit" value="<?php echo a_('Save ' . aMediaTools::getBestTypeLabel()) ?>" class="a-btn a-submit" /></li>
			<?php // I use a-cancel and a-media-edit-multiple to find and trigger this cancel button in JS ?>
			<li><?php echo link_to(a_("Cancel"), "aMedia/resume", array("class"=>"a-btn icon a-media-edit-multiple-cancel")) ?></li>
		</ul>
		</form>
	</div>
</div>
