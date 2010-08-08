<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
  $results = isset($results) ? $sf_data->getRaw('results') : null;
?>
<div id="a-media-plugin-search-results">
<?php include_partial('aMedia/videoSearch', array('form' => $form, 'results' => $results)) ?>
</div>