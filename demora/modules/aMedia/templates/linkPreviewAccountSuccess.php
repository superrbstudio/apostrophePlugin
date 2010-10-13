<?php
  // Compatible with sf_escaping_strategy: true
  $results = isset($results) ? $sf_data->getRaw('results') : null;
  $account = isset($account) ? $sf_data->getRaw('account') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $description = isset($description) ? $sf_data->getRaw('description') : null;
  $service = isset($service) ? $sf_data->getRaw('service') : null;
?>

<?php use_helper('a') ?>

<h4 class="a-account-preview-name"><?php echo a_entities($name) ?></h4>
<div class="a-account-preview-description"><?php echo aHtml::textToHtml($description) ?></div>
<ul class="a-account-preview-recent">
  <?php foreach ($results['results'] as $result): ?>
    <li><?php echo $service->embed($result['id'], aMediaTools::getOption('video_account_preview_width'), aMediaTools::getOption('video_account_preview_height')) ?></li>
  <?php endforeach ?>
</ul>

<div class="a-account-preview-confirm">
<p>
Automatically add all new media in this account? If you click OK, all new media added to this account will automatically be added to the media repository on an ongoing basis.
</p>
<ul class="a-controls">
  <li><a href="#" id="a-account-preview-ok" class="">OK</a></li>
  <li><a href="#" id="a-account-preview-cancel" class="a-btn a-cancel">Cancel</a></li>
</ul>
</div>
