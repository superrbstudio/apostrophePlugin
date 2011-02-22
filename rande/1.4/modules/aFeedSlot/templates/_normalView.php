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
      <li class="a-feed-item">
        <ul>
          <li class="title"><?php echo link_to_if($feedItem->getLink() && $links, $feedItem->getTitle(), $feedItem->getLink()) ?></li>
          <?php $date = $feedItem->getPubDate() ?>
          <li class="date"><?php echo $dateFormat ? date($dateFormat, $date) : aDate::pretty($date) . ' ' . aDate::time($date) ?></li>
          <li class="description"><?php echo aHtml::simplify($feedItem->getDescription(), $markup) ?></li>
        </ul>
      </li>
      <?php $n++ ?>
    <?php endforeach ?>
  </ul>
<?php endif ?>
