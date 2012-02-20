<?php // P'unks: please do not add any complicated conditionals and calls here, this partial ?>
<?php // should receive clean and simple boolean parameters from the globalTools partial. ?>
<?php // That keeps this simple to override. -Tom ?>

<?php // The markup of page-settings is specialized because it's loaded via AJAX, but ?>
<?php // feel free to override and move this to the end, etc. ?>
<?php if ($pageSettings): ?> 	
	<li>
		<a href="/#page-settings" onclick="return false;" class="a-btn icon alt no-bg a-page-settings" id="a-page-settings-button"><span class="icon"></span><?php echo a_('Page Settings') ?></a>
		<div id="a-page-settings" class="a-page-settings-menu dropshadow"></div>
	</li>				
<?php endif ?>

<?php // The usual buttons: blog, events, media, categories, tags, users, reorganize... ?>

<?php // The normal way: output all global buttons the user has access to, ?>
<?php // in the order specified by app_a_global_button_order if any. Note that if ?>
<?php // you do specify an order you must specify all of the buttons ?>
	
<?php $buttons = aTools::getGlobalButtons() ?>
<?php foreach ($buttons as $button): ?>
	<li class="a-global-toolbar-<?php echo aTools::slugify($button->getLabel()) ?>">
		<?php echo link_to('<span class="icon"></span>'.__($button->getLabel(), null, 'apostrophe'), $button->getLink(), array('class' => 'a-btn icon alt no-bg ' . $button->getCssClass())) ?>
	</li>
<?php endforeach ?>
<?php include_partial('a/globalProjectButtons', array()) ?>

<?php // An alternative: call aTools::getGlobalButtonsByName(). Then you can emit them in any order you want with ?>
<?php // any styling you want, and app_a_global_button_order is ignored. However you MUST test them with isset('media'), ?>
<?php // etc. before assuming this particular user is entitled to see them. This is an exaggerated example with just two ?>
<?php // buttons to show that you have control over the markup ?>

<?php /* Example Code ?> 
  <?php $buttons = aTools::getGlobalButtonsByName() ?>
  <?php if (isset($buttons['media'])): ?>
    <?php $button = $buttons['media'] ?>
  	<li>
  		<?php echo link_to('<span class="icon"></span>'.__('Picturebook', null, 'apostrophe'), $button->getLink(), array('class' => 'a-btn icon alt no-bg ' . $button->getCssClass())) ?>
  	</li>
  <?php endif ?>
  <li>Separator Thing</li>
  <?php if (isset($buttons['blog'])): ?>
    <?php $button = $buttons['blog'] ?>
  	<li>
  		<?php echo link_to('<span class="icon"></span>'.__('Journal', null, 'apostrophe'), $button->getLink(), array('class' => 'a-btn icon alt no-bg ' . $button->getCssClass())) ?>
  	</li>
  <?php endif ?>
<?php //*/ ?>

<?php // The markup of add-page is also specialized and DHTML-based ?>

<?php if ($addPage): ?>
	<li>
	  <?php // Triggers the same form as page settings now ?>
	  <a href="/#add-page" class="a-btn icon a-add a-create-page" id="a-create-page-button" onclick="return false;"><span class="icon"></span><?php echo __("Add Page", null, 'apostrophe') ?></a>
	  <div id="a-create-page" class="a-page-settings-menu dropshadow"></div>
	</li>
<?php endif ?>

<?php // A toggle between near-WYSIWYG editing and (almost) completely WYSIWYG previewing ?>
<?php if (sfConfig::get('app_a_preview_toggle')): ?>
  <li>
  <a href="#" class="a-btn icon a-search a-preview" id="a-preview"><span class="icon"></span><span class="label"></span></a>
    <?php a_js_call('apostrophe.setupPreviewToggle(?)', array('labels' => array('preview' => a_('Preview Page'), 'edit' => a_('Edit Page')))) ?>
  </li>
<?php endif ?>
