<?php
  // Compatible with sf_escaping_strategy: true
  $dateFormat = isset($dateFormat) ? $sf_data->getRaw('dateFormat') : null;
  $feed = isset($feed) ? $sf_data->getRaw('feed') : null;
  $invalid = isset($invalid) ? $sf_data->getRaw('invalid') : null;
  $itemTemplate = isset($itemTemplate) ? $sf_data->getRaw('itemTemplate') : null;
  $links = isset($links) ? $sf_data->getRaw('links') : null;
  $markup = isset($markup) ? $sf_data->getRaw('markup') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $pageid = isset($pageid) ? $sf_data->getRaw('pageid') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $posts = isset($posts) ? $sf_data->getRaw('posts') : null;
  $slot = isset($slot) ? $sf_data->getRaw('slot') : null;
  $url = isset($url) ? $sf_data->getRaw('url') : null;
?>
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