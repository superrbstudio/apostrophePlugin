<?php
  // Pull in the Symfony Text Helper
  sfContext::getInstance()->getConfiguration()->loadHelpers(array('Text'));
  // Compatible with sf_escaping_strategy: true
  $feedItem = isset($feedItem) ? $sf_data->getRaw('feedItem') : null;
?>
<li class="a-feed-item">
  <ul>
    <li class="title">
			<?php if (isset($links) && $feedItem->getLink()): ?>
				<a href="<?php echo $feedItem->getLink() ?>"><?php echo $feedItem->getTitle() ?></a>
			<?php else: ?>
				<?php echo $feedItem->getTitle() ?>
			<?php endif ?>
		</li>
    <?php $date = $feedItem->getPubDate() ?>
    <li class="date"><?php echo $dateFormat ? date($dateFormat, $date) : aDate::pretty($date) . ' ' . aDate::time($date) ?></li>
    <li class="description"><?php echo auto_link_text(aHtml::simplify($feedItem->getDescription(), $markup, false, (isset($attributes)? $attributes:false), (isset($styles)? $styles:false))) ?></li>
  </ul>
</li>
