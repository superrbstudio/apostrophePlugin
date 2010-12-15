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
        <?php // I could avoid the csrf nonsense by instantiating forms for all 9, but forms are very slow ?>
        <?php echo link_to(a_('Select%buttonspan%', array('%buttonspan%' => '<span></span>')), 'aMedia/editVideo?' . http_build_query(array('first_pass' => 1, 'a_media_item' => array('title' => $result['title'], '_csrf_token' => md5(sfConfig::get('sf_csrf_secret') . session_id()), 'service_url' => $result['url']))), array('class' => 'a-media-search-select a-btn')) ?>
        <?php echo $service->embed($result['id'], aMediaTools::getOption('video_search_preview_width'), aMediaTools::getOption('video_search_preview_height')) ?>
      </li>
    <?php endforeach ?>  
  </ul>
  <br class="clear" />

  <div id="a-media-video-search-pagination" class="a-pager-navigation a-ui">
    <?php include_partial('aPager/pager', array('pager' => $pager, 'pagerUrl' => $url)) ?>
    <?php if (0): ?>
      <?php // really handy if we decide to re-ajax this ?>
      <script type="text/javascript" charset="utf-8">
        $('#a-media-video-search-pagination a').click(function() {
          var href = $(this).attr('href');
          $('#a-media-video-search-results-container').load(href);
          return false;
        });
      </script>
    <?php endif ?>
  </div>
  <br class="clear" />
<?php endif ?>