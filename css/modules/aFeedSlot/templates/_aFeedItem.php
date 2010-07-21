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
    <li class="description"><?php echo aHtml::simplify($feedItem->getDescription(), $markup, false, (isset($attributes)? $attributes:false), (isset($styles)? $styles:false)) ?></li>
  </ul>
</li>
