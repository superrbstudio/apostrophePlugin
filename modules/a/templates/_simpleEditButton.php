<?php
  // Compatible with sf_escaping_strategy: true
  $class = isset($class) ? $sf_data->getRaw('class') : null;
  $controlsSlot = isset($controlsSlot) ? $sf_data->getRaw('controlsSlot') : null;
  $label = isset($label) ? $sf_data->getRaw('label') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $pageid = isset($pageid) ? $sf_data->getRaw('pageid') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $title = isset($title) ? $sf_data->getRaw('title') : null;
  $slot = isset($slot) ? $sf_data->getRaw('slot') : null;
?>
<?php use_helper('a') ?>
<?php if (is_null($slot)): ?>
  Apostrophe 1.5: this slot's normalView partial must be upgraded to pass slot => $slot as one of its parameters to the simpleEditButton partial.
<?php else: ?>
  <?php if (!isset($controlsSlot)): ?>
    <?php $controlsSlot = true ?>
  <?php endif ?>

  <?php if ($controlsSlot): ?>
  	<?php slot("a-slot-controls-$pageid-$name-$permid") ?>
  <?php endif ?>

  <li>
  <?php // We want to eliminate jQuery helpers, but writing this link as raw HTML is tricky because ?>
  <?php // of the need to quote the title option right. And link_to doesn't like '#' as a URL. So we use ?>
  <?php // content_tag, Symfony's lower-level helper for outputting any tag and its content programmatically ?>
  <?php echo content_tag('a', '<span class="icon"></span>'.(isset($label) ? a_($label) : a_("Edit")), 
  			array(
  			  'href' => '#edit-slot-'.$pageid.'-'.$name.'-'.$permid,
  				'id' => "a-slot-edit-$pageid-$name-$permid",
  				'class' => isset($class) ? $class : 'a-btn icon a-edit', 
  				'title' => isset($title) ? $title : a_('Edit'), 
  )) ?>

  <?php a_js_call('apostrophe.slotEnableEditButton(?, ?, ?, ?, ?)', $pageid, $name, $permid, a_url($slot->type . 'Slot', 'ajaxEditView'), aTools::getRealUrl()) ?>
  </li>
	
  <?php if ($controlsSlot): ?>
  	<?php end_slot() ?>
  <?php endif ?>
<?php endif ?>

