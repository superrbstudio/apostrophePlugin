<?php
  // Compatible with sf_escaping_strategy: true
  $editable = isset($editable) ? $sf_data->getRaw('editable') : null;
  $feed = isset($feed) ? $sf_data->getRaw('feed') : null;
  $invalid = isset($invalid) ? $sf_data->getRaw('invalid') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $pageid = isset($pageid) ? $sf_data->getRaw('pageid') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
  $slot = isset($slot) ? $sf_data->getRaw('slot') : null;
  $url = isset($url) ? $sf_data->getRaw('url') : null;
?>
<?php use_helper('a') ?>
<?php if ($editable): ?>
<?php include_partial('a/simpleEditWithVariants', array('name' => $name, 'permid' => $permid, 'pageid' => $pageid, 'slot' => $slot, 'label' => a_get_option($options, 'editLabel', a_('Edit')))) ?>
<?php endif ?>
<?php if (!isset($url)): ?>
  <p class="aFeedSelect"><?php echo __('Click Edit to select a feed URL.', null, 'apostrophe') ?></p>
<?php elseif ($invalid): ?>
  <?php include_partial('aFeedSlot/invalid') ?>
<?php else: ?>
  <ul class="a-feed">
    <?php $n = 0 ?>
    <?php foreach ($feed->getItems() as $feedItem): ?>
      <?php if (($options['posts'] !== false) && ($n >= $options['posts'])): ?>
        <?php break ?>
      <?php endif ?>
			<?php include_partial('aFeedSlot/'.$options['itemTemplate'], array('feedItem' => $feedItem, 'links' => $options['links'], 'dateFormat' => $options['dateFormat'], 'markup' => $options['markup'], 'styles' => $options['styles'], 'attributes' => $options['attributes'])) ?>
      <?php $n++ ?>
    <?php endforeach ?>
  </ul>
<?php endif ?>