<?php
  // Compatible with sf_escaping_strategy: true
  $results = isset($results) ? $sf_data->getRaw('results') : null;
  $account = isset($account) ? $sf_data->getRaw('account') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $description = isset($description) ? $sf_data->getRaw('description') : null;
  $service = isset($service) ? $sf_data->getRaw('service') : null;
?>

<?php use_helper('a') ?>

<hr class="a-hr"/>
<h4 class="a-account-preview-name">Account: <span><?php echo a_entities($name) ?></span></h4>
<h5 class="a-account-preview-description"><?php echo $description ?></h5>
<ul class="a-account-preview-recent">
  <?php foreach ($results['results'] as $result): ?>
    <li><?php echo $service->embed($result['id'], aMediaTools::getOption('video_account_preview_width'), aMediaTools::getOption('video_account_preview_height')) ?></li>
  <?php endforeach ?>
</ul>

<div class="a-account-preview-confirm">
<p class="a-help">Add this account so that all new media associated with it will automatically be added to the media library on an ongoing basis.</p>
<ul class="a-ui a-controls">
  <li><?php echo a_js_button(a_('Add This Account'), array('big', 'icon', 'a-add'), 'a-account-preview-ok') ?></li>
  <li><?php echo a_js_button(a_('Cancel'), array('icon', 'a-cancel', 'big', 'alt'), 'a-account-preview-cancel') ?></li>
</ul>
</div>
