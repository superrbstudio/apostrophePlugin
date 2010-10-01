<?php
  // Compatible with sf_escaping_strategy: true
  $active = isset($active) ? $sf_data->getRaw('active') : null;
  $firstPass = isset($firstPass) ? $sf_data->getRaw('firstPass') : null;
  $form = isset($form) ? $sf_data->getRaw('form') : null;
?>

<?php (count(aMediaTools::getOption('batch_max')) > 1)? $singleItem = false:$singleItem = true; ?>

<?php use_helper('a') ?>

<?php slot('body_class') ?>a-media a-media-multiple-upload<?php end_slot() ?>

<?php slot('a-page-header') ?>
	<?php include_partial('aMedia/mediaHeader', array('uploadAllowed' => $uploadAllowed, 'embedAllowed' => $embedAllowed)) ?>
<?php end_slot() ?>

<?php include_component('aMedia', 'browser') ?>

<div class="a-media-library">

	<div class="a-media-toolbar">
		<h3><?php echo __('Annotate ' . aMediaTools::getBestTypeLabel(), null, 'apostrophe') ?></h3>
	</div>

  <?php if ($postMaxSizeExceeded): ?>
  	<h3><?php echo __('File too large. Limit is %POSTMAXSIZE%', array('%POSTMAXSIZE%' => ini_get('post_max_size')), 'apostrophe') ?></h3>
  <?php endif ?>

	<div class="a-media-items">				
		<form method="POST" action="<?php echo url_for("aMedia/editMultiple") ?>" enctype="multipart/form-data" id="a-media-edit-form-0" class="a-ui a-media-edit-form">		

			<?php if ($singleItem == false): ?>
				<ul class="a-ui a-controls top a-align-right">
					<li><input type="submit" name="submit" value="<?php echo a_('Save ' . aMediaTools::getBestTypeLabel()) ?>" class="a-btn a-submit big" /></li>
					<?php // I use a-cancel and a-media-edit-multiple to find and trigger this cancel button in JS ?>
					<li><?php echo link_to(a_("Cancel"), "aMedia/resume", array("class"=>"a-btn icon a-cancel big")) ?></li>
				</ul>
			<?php endif ?>
					
			<div class="a-form-row a-hidden">
			<?php echo $form->renderHiddenFields() ?>
	  	</div>
		
		<?php $n = 0 ?>

		<?php for ($i = 0; ($i < aMediaTools::getOption('batch_max')); $i++): ?>
		  <?php if (isset($form["item-$i"])): ?>
		    <?php // What we're passing here is actually a widget schema ?>
		    <?php // (they get nested when embedded forms are present), but ?>
		    <?php // it supports the same methods as a form for rendering purposes ?>

				<?php if ($n%2 == 0): ?>
					<div class="a-media-editor-row">
				<?php endif ?>
			    <?php // Please do not remove this div, I need it to implement the remove button ?>
			    <div id="a-media-editor-<?php echo $i ?>" class="a-media-editor<?php echo ($n%2 == 0)? ' a-even':' a-odd' ?>">
						<ul>
	 			    <?php include_partial('aMedia/edit', 
	 								array(
	 									"item" => false, 
	 			        		"firstPass" => $firstPass, 
	 									"form" => $form["item-$i"], 
	 									"n" => $n, 
	 									'i' => $i,
	 									'itemFormScripts' => 'false',
	 									)) ?>
						</ul>
					</div>
				<?php if ($n%2 == 1 || ($n == aMediaTools::getOption('batch_max')-1)): ?>
					</div>
				<?php endif ?>
				<?php $n++ ?>
		  <?php endif ?>
		<?php endfor ?>

		<?php include_partial('aMedia/itemFormScripts', array('i'=>$i)) ?>

		<ul class="a-ui a-controls<?php echo ($singleItem == false)? ' a-right':' a-left' ?>">
			<li><input type="submit" name="submit" value="<?php echo a_('Save ' . aMediaTools::getBestTypeLabel()) ?>" class="a-btn a-submit big" /></li>
			<?php // I use a-cancel and a-media-edit-multiple to find and trigger this cancel button in JS ?>
			<li><?php echo link_to(a_("Cancel"), "aMedia/resume", array("class"=>"a-btn icon a-cancel big")) ?></li>
		</ul>

		</form>
	</div>
</div>
