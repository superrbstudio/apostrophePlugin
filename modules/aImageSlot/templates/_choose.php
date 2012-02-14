<?php
  // Compatible with sf_escaping_strategy: true
  $action = isset($action) ? $sf_data->getRaw('action') : null;
  $buttonLabel = isset($buttonLabel) ? $sf_data->getRaw('buttonLabel') : null;
  $class = isset($class) ? $sf_data->getRaw('class') : null;
  // Constraints are optional
  $constraints = isset($constraints) ? $sf_data->getRaw('constraints') : array();
  $itemId = isset($itemId) ? $sf_data->getRaw('itemId') : null;
  $label = isset($label) ? $sf_data->getRaw('label') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $permid = isset($permid) ? $sf_data->getRaw('permid') : null;
  $slug = isset($slug) ? $sf_data->getRaw('slug') : null;
  $type = isset($type) ? $sf_data->getRaw('type') : null;
  $now = isset($now) ? $sf_data->getRaw('now') : null;
?>
<?php // Make sure we target the administrative media engine page and not a public instance ?>
<?php aRouteTools::pushTargetEngineSlug('/admin/media', 'aMedia') ?>
<?php $after = url_for($action) . "?" .
  http_build_query(
    array(
      "slot" => $name, 
      "slug" => $slug, 
      // actual_url will be added by JS, window.location is more reliable than
      // guessing at the full context here when we might be in an AJAX update etc.
      "permid" => $permid,
      "noajax" => 1)) ?>
<?php $url = url_for('aMedia/select') . '?' . http_build_query(
  array_merge(
    $constraints,
    array(
    "aMediaId" => $itemId,
    "type" => $type,
    "label" => $label,
    "after" => $after))) ?>

<?php // When we've just added a file slot, mp3 slot or image slot there's nothing to do but ?>
<?php // select that file, so go do that now. ?>
<?php if ($now): ?>
  <script type="text/javascript">
    window.location.href = apostrophe.injectActualUrlIntoHref(<?php echo json_encode($url) ?>);
  </script>
<?php endif ?>
 <li><a href="<?php echo $url ?>" class="<?php echo $class . ' a-inject-actual-url a-js-choose-button' ?>"><span class="icon"></span><?php echo $buttonLabel ?></a></li>
<?php aRouteTools::popTargetEnginePage('aMedia') ?>
