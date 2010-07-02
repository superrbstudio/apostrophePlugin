<?php use_helper('I18N') ?>
<?php include_partial('a/simpleEditWithVariants', array('name' => $name, 'permid' => $permid, 'pageid' => $pageid, 'slot' => $slot)) ?>
<?php if (!isset($url)): ?>
  <p class="aFeedSelect"><?php echo __('Click Edit to select a feed URL.', null, 'apostrophe') ?></p>
<?php elseif ($invalid): ?>
  <p class="aFeedInvalid"><?php echo __('Invalid feed.', null, 'apostrophe') ?></p>
<?php else: ?>
  <ul class="a-feed">
    <?php $n = 0 ?>
    <?php foreach ($feed->getItems() as $feedItem): ?>
      <?php if (($posts !== false) && ($n >= $posts)): ?>
        <?php break ?>
      <?php endif ?>
			<?php include_partial('aFeedSlot/'.$itemTemplate, array('feedItem' => $feedItem, 'links' => $links, 'dateFormat' => $dateFormat, 'markup' => $markup)) ?>
      <?php $n++ ?>
    <?php endforeach ?>
  </ul>
<?php endif ?>