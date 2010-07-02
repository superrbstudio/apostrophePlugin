<li class="a-feed-item">
  <ul>
    <li class="title"><?php echo link_to_if($feedItem->getLink() && $links, $feedItem->getTitle(), $feedItem->getLink()) ?></li>
    <?php $date = $feedItem->getPubDate() ?>
    <li class="date"><?php echo $dateFormat ? date($dateFormat, $date) : aDate::pretty($date) . ' ' . aDate::time($date) ?></li>
    <li class="description"><?php echo aHtml::simplify($feedItem->getDescription(), $markup) ?></li>
  </ul>
</li>
