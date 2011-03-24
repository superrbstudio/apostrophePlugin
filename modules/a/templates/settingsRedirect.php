<?php
  // Compatible with sf_escaping_strategy: true
  $page = isset($page) ? $sf_data->getRaw('page') : null;
?>
<script type="text/javascript">
<?php // We're in an AJAX request, so we can't do a normal redirect call ?>
window.location = <?php echo json_encode($page->getUrl()) ?>;
</script>
