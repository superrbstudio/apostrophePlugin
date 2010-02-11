<?php include_partial('a/simpleEditWithVariants', array('name' => $name, 'permid' => $permid, 'pageid' => $pageid, 'slot' => $slot)) ?>
<?php if (!isset($url)): ?>
  <p class="aFeedSelect">Click Edit to select a feed URL.</p>
<?php elseif ($invalid): ?>
  <p class="aFeedInvalid">Invalid feed.</p>
<?php else: ?>
  <ul>
    <?php $n = 0 ?>
    <?php foreach ($feed->getItems() as $feedItem): ?>
      <?php if (($posts !== false) && ($n >= $posts)): ?>
        <?php break ?>
      <?php endif ?>
      <li>
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
