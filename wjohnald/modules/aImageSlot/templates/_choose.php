<?php
  // Compatible with sf_escaping_strategy: true
  $action = isset($action) ? $sf_data->getRaw('action') : null;
  $buttonLabel = isset($buttonLabel) ? $sf_data->getRaw('buttonLabel') : null;
  $class = isset($class) ? $sf_data->getRaw('class') : null;
  $constraints = isset($constraints) ? $sf_data->getRaw('constraints') : null;
  $itemId = isset($itemId) ? $sf_data->getRaw('itemId') : null;
  $label = isset($label) ? $sf_data->getRaw('label') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $slug = isset($slug) ? $sf_data->getRaw('slug') : null;
  $type = isset($type) ? $sf_data->getRaw('type') : null;
?>
<?php // Make sure we target the administrative media engine page and not a public instance ?>
<?php aRouteTools::pushTargetEnginePage('/admin/media') ?>
<?php $after = url_for($action) . "?" .
  http_build_query(
    array(
      "slot" => $name, 
      "slug" => $slug, 
      // TODO: remove this parameter entirely in 1.5, it is strictly for backwards compatibility
      // with any existing overrides out there
      "actual_slug" => aTools::getRealPage() ? aTools::getRealPage()->getSlug() : 'global',
      "actual_url" => aTools::getRealUrl(),
      "permid" => $permid,
      "noajax" => 1)) ?>
<li><?php echo link_to($buttonLabel,
  'aMedia/select',
  array('query_string' =>
    http_build_query(
      array_merge(
        $constraints,
        array(
        "aMediaId" => $itemId,
        "type" => $type,
        "label" => $label,
        "after" => $after))),
      'class' => $class)) ?></li>
<?php aRouteTools::popTargetEnginePage('aMedia') ?>
