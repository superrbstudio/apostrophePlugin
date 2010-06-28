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
<?php echo link_to($buttonLabel,
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
      'class' => $class)) ?>
<?php aRouteTools::popTargetEnginePage('aMedia') ?>
