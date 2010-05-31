<?php $variants = sfConfig::get('app_a_slot_variants') ?>
<?php if ((!$slot->isNew()) && isset($variants[$slot->type]) && count($variants[$slot->type])): ?>
  <li class="a-controls-item variant">
    <ul>
      <?php foreach ($variants[$slot->type] as $variant => $settings): ?>
        <?php if ($variant === $slot->getEffectiveVariant()): ?>
          <li class="current">
            <?php echo $settings['label'] ?>
          </li>
        <?php else: ?>
          <li>
            <?php echo jq_link_to_remote($settings['label'], array('url' => url_for('a/setVariant?' . http_build_query(array('id' => $page->id, 'name' => $name, 'permid' => $permid, 'variant' => $variant))), 'update' => "a-slot-content-$name-$permid")) ?>
          </li>
        <?php endif ?>
      <?php endforeach ?>
    </ul>
  </li>
<?php endif ?>