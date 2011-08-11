<?php
  // Compatible with sf_escaping_strategy: true
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $pageid = isset($pageid) ? $sf_data->getRaw('pageid') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $slot = isset($slot) ? $sf_data->getRaw('slot') : null;
  $sf_user = isset($sf_user) ? $sf_data->getRaw('sf_user') : null;
?>

<?php use_helper('a') ?>

<?php $options = $sf_user->getAttribute("slot-options-$pageid-$name-$permid", null, 'apostrophe') ?>
<?php $variants = aTools::getVariantsForSlotType($slot->type, $options) ?>
<?php if (count($variants) > 1): ?>
  <?php // You can't switch variants until you've saved something for architectural reasons, however ?>
  <?php // we do need this menu waiting in the wings so that we can turn it on on the first save of an edit view ?>
  <li class="a-ui variant" style="<?php echo $slot->isNew() ? "display:none" : "" ?>" id="a-<?php echo "$pageid-$name-$permid-variant" ?>">
		<a href="#" onclick="return false;" class="a-variant-options-toggle a-btn icon no-label a-settings" id="a-<?php echo $pageid ?>-<?php echo $name ?>-<?php echo $permid ?>-variant-options-toggle"><span class="icon"></span><?php echo __('Options', null, 'apostrophe') ?></a>
    <ul class="a-ui a-options a-variant-options dropshadow">
			<li class="a-options-heading"><h4>Options</h4></li>
      <?php foreach ($variants as $variant => $settings): ?>
        <?php // These classes and ids are carefully set up so that _ajaxUpdateSlot can ?>
        <?php // target them later to change the active variant without rewriting the ?>
        <?php // outer area container ?>
        <?php $id = "a-$pageid-$name-$permid-variant-$variant" ?>
        <?php $active = ($variant === $slot->getEffectiveVariant($options)) ?>
        <li id="<?php echo $id ?>-active" class="active current" style="<?php echo $active ? '' : 'display: none' ?>">
          <span class="a-btn alt a-disabled icon a-checked no-bg"><span class="icon"></span><?php echo $settings['label'] ?></span>
        </li>
        <li id="<?php echo $id ?>-inactive" class="inactive" style="<?php echo (!$active) ? '' : 'display: none' ?>">
          <?php echo a_js_button(a_($settings['label']), array('alt', 'icon', 'a-unchecked', 'no-bg'), $id . '-button') ?>
          <?php a_js_call('apostrophe.slotEnableVariantButton(?)', array('buttonId' => $id . '-button', 'slotContentId' => "a-slot-content-$pageid-$name-$permid", 'variant' => $variant, 'slotFullId' => "$pageid-$name-$permid", 'url' => a_url('a', 'setVariant', array('id' => $pageid, 'name' => $name, 'permid' => $permid, 'variant' => $variant)))) ?>
        </li>
      <?php endforeach ?>
    </ul>
  </li>

	<?php if (!$slot->isNew()): ?>
		<?php a_js_call('apostrophe.menuToggle(?)', array('button' => '#a-'.$pageid.'-'.$name.'-'.$permid.'-variant-options-toggle', 'classname' => 'a-options-open', 'overlay' => false)) ?>	
	<?php endif ?>

<?php endif ?>
