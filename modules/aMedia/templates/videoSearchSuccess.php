<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
  $results = isset($results) ? $sf_data->getRaw('results') : null;
  $pager = isset($pager) ? $sf_data->getRaw('pager') : null;
  $account = isset($account) ? $sf_data->getRaw('account') : null;
  $service = isset($service) ? $sf_data->getRaw('service') : null;
?>

<?php use_helper('a') ?>
<?php slot('body_class') ?>a-media<?php end_slot() ?>

<div class="a-ui a-admin-header">
  <h3 class="a-admin-title"><?php echo link_to(a_('Media Library'), 'aMedia/resume') ?></h3>
    
    
  <?php echo a_('View %account% on %service%', array('%account%' => $account->username, '%service%' => a_($account->service))) ?></h3>
</div>

<div class="a-media-library">
  
	<?php include_component('aMedia', 'browser') ?>
	<?php include_partial('aMedia/videoSearch', array('service' => $service, 'pager' => $pager, 'url' => $url)) ?>
	
</div>
