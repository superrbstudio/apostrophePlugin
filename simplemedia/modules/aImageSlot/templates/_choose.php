<?php $after = url_for($action) . "?" .
  http_build_query(
    array(
      "slot" => $name, 
      "slug" => $slug, 
      "actual_slug" => aTools::getRealPage()->getSlug(),
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
