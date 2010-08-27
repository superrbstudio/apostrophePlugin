<?php
  // Compatible with sf_escaping_strategy: true
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $pageid = isset($pageid) ? $sf_data->getRaw('pageid') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $slot = isset($slot) ? $sf_data->getRaw('slot') : null;
?>
<?php use_helper('I18N') ?>
<?php $options = $sf_user->getAttribute("slot-options-$pageid-$name-$permid", null, 'apostrophe') ?>
<?php $variants = aTools::getVariantsForSlotType($slot->type, $options) ?>
<?php if (count($variants) > 1): ?>
  <?php // You can't switch variants until you've saved something for architectural reasons, however ?>
  <?php // we do need this menu waiting in the wings so that we can turn it on on the first save of an edit view ?>
  <li class="variant" style="<?php echo $slot->isNew() ? "display:none" : "" ?>" id="a-<?php echo "$pageid-$name-$permid-variant" ?>">
		<a href="#" onclick="return false;" class="a-variant-options-toggle a-btn icon a-settings" id="a-<?php echo $pageid ?>-<?php echo $name ?>-<?php echo $permid ?>-variant-options-toggle"><?php echo __('Options', null, 'apostrophe') ?></a>
    <ul class="a-options a-variant-options dropshadow">
      <?php foreach ($variants as $variant => $settings): ?>
        <?php // These classes and ids are carefully set up so that _ajaxUpdateSlot can ?>
        <?php // target them later to change the active variant without rewriting the ?>
        <?php // outer area container ?>
        <?php $id = "a-$pageid-$name-$permid-variant-$variant" ?>
        <?php $active = ($variant === $slot->getEffectiveVariant($options)) ?>
        <li id="<?php echo $id ?>-active" class="active current" style="<?php echo $active ? '' : 'display: none' ?>">
          <span class="a-btn alt a-disabled icon a-checked no-bg"><?php echo $settings['label'] ?></span>
        </li>
        <li id="<?php echo $id ?>-inactive" class="inactive" style="<?php echo (!$active) ? '' : 'display: none' ?>">
          <?php echo jq_link_to_remote(__($settings['label'], null, 'apostrophe'), array('url' => url_for('a/setVariant?' . http_build_query(array('id' => $pageid, 'name' => $name, 'permid' => $permid, 'variant' => $variant))), 'update' => "a-slot-content-$pageid-$name-$permid"), array('class' => 'a-btn alt icon a-unchecked no-bg',)) ?>
        </li>
    		<script type="text/javascript" charset="utf-8">
    			$(document).ready(function() {
    			  <?php // When the link to activate an inactive variant is clicked... ?>
    				$('<?php echo "#a-$pageid-$name-$permid-variant-$variant-inactive a" ?>').click(function() {
      				<?php // Add the loading class... ?>
    					$('<?php echo "#a-$pageid-$name-$permid-variant ul.a-variant-options" ?>').addClass('loading');		
    					<?php // Hide the active state of whatever variant was active (by brute force)... ?>			
    					$('<?php echo "#a-$pageid-$name-$permid-variant ul.a-variant-options li.active" ?>').hide();
    					<?php // Show all the inactive states... ?>
    					$('<?php echo "#a-$pageid-$name-$permid-variant ul.a-variant-options li.inactive" ?>').show();
    					<?php // And then show the one active state that is newly appropriate ?>
    					$('<?php echo "#a-$pageid-$name-$permid-variant-$variant-active" ?>').show();
    					<?php // And hide the corresponding inactive state ?>
    					$('<?php echo "#a-$pageid-$name-$permid-variant-$variant-inactive" ?>').hide();
  				 });
					$('<?php echo "#a-$pageid-$name-$permid-variant" ?>').children("ul.a-variant-options").hide();
					aMenuToggle('#a-<?php echo $pageid ?>-<?php echo $name ?>-<?php echo $permid ?>-variant-options-toggle', $('#a-<?php echo $pageid ?>-<?php echo $name ?>-<?php echo $permid ?>-variant-options-toggle').parent(), 'a-options-open', false);
  			});
    		</script>
      <?php endforeach ?>
    </ul>
  </li>
<?php endif ?>
