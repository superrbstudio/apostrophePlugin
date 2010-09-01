<?php
  // Pull in the Symfony Text Helper
  sfContext::getInstance()->getConfiguration()->loadHelpers(array('Text'));

  // Compatible with sf_escaping_strategy: true
  $attributes = isset($attributes) ? $sf_data->getRaw('attributes') : null;
  $dateFormat = isset($dateFormat) ? $sf_data->getRaw('dateFormat') : null;
  $feedItem = isset($feedItem) ? $sf_data->getRaw('feedItem') : null;
  $links = isset($links) ? $sf_data->getRaw('links') : null;
  $markup = isset($markup) ? $sf_data->getRaw('markup') : null;
  $styles = isset($styles) ? $sf_data->getRaw('styles') : null;
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
