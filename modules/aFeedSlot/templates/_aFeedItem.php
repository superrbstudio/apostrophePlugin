<?php
  // Pull in the Symfony Text Helper
  sfContext::getInstance()->getConfiguration()->loadHelpers(array('Text'));
  // Compatible with sf_escaping_strategy: true
  $feedItem = isset($feedItem) ? $sf_data->getRaw('feedItem') : null;
  $markup = isset($markup) ? $sf_data->getRaw('markup') : null;
  $links = isset($links) ? $sf_data->getRaw('links') : null;
	//$today = ;
?>
<li class="a-feed-item">
  <ul>
    <?php if ((!sfConfig::get('app_a_feed_hide_identical_description')) || (trim($feedItem->getTitle()) !== trim($feedItem->getDescription()))): ?>
			<li class="a-feed-arrow"><a href="<?php echo $feedItem->getLink() ?>"></a></li>
      <li class="twitter-results"><?php echo auto_link_text(aHtml::simplify($feedItem->getDescription(), $markup, false, (isset($attributes)? $attributes:false), (isset($styles)? $styles:false))) ?></li>
    <?php endif ?>
    <?php $date = $feedItem->getPubDate() ?>
    <?php if ($date): ?>
      <li class="date"><a href="<?php echo $feedItem->getLink() ?>">Posted <?php echo $dateFormat ? date($dateFormat, $date) : aDate::dayMonthYear($date) . ' ' . aDate::time($date) ?></a></li>
    <?php endif ?>
  </ul>
</li>
