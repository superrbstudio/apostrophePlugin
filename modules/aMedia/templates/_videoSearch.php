<?php
  // Compatible with sf_escaping_strategy: true
  $url = isset($url) ? $sf_data->getRaw('url') : null;
  $pager = isset($pager) ? $sf_data->getRaw('pager') : null;
  $service = isset($service) ? $sf_data->getRaw('service') : null;
?>
<?php use_helper('a') ?>

<?php if ($pager->getNbResults() == 0): ?>
  <p><?php echo __('No matching items were found.', null, 'apostrophe') ?></p>
<?php else: ?>

  <ul class="a-ui" id="a-media-video-search-results">
    <?php foreach ($pager->getResults() as $result): ?>
      <li>
        <?php // embed is correct, the form takes either a URL or an embed code (and URLs are more reasonable) ?>
        <?php // Don't pass the title, the form will talk to the service ?>
        <?php echo link_to(a_('Select%buttonspan%', array('%buttonspan%' => '<span></span>')), 'aMedia/editVideo?' . http_build_query(array('first_pass' => 1, 'a_media_item' => array('_csrf_token' => md5(sfConfig::get('sf_csrf_secret') . session_id()), 'view_is_secure' => 0, 'embed' => $result['url']))), array('class' => 'a-media-search-select a-btn')) ?>
        <?php echo $service->embed($result['id'], aMediaTools::getOption('video_search_preview_width'), aMediaTools::getOption('video_search_preview_height')) ?>
      </li>
    <?php endforeach ?>  
  </ul>
  <br class="clear" />

  <div id="a-media-video-search-pagination" class="a-pager-navigation a-ui">
    <?php include_partial('aPager/pager', array('pager' => $pager, 'pagerUrl' => $url)) ?>
  </div>
  <br class="clear" />
<?php endif ?>