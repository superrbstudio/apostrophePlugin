<?php // Force a refresh of an entire page for the benefit of an area that ?>
<?php // contains a grumpy slot like the raw HTML slot, using a #hashtag to try ?>
<?php // to skip to the relevant part of the page again ?>
<?php $name = $sf_data->getRaw('name') ?>
<?php $version = isset($version) ? $sf_data->getRaw('version') : null ?>

<script type="text/javascript">
  $(function() {
    var href = apostrophe.addParameterToUrl(window.location.href, 'a-refresh-area', <?php echo json_encode($name) ?>);
    var version = version;
    if (version) {
      href = apostrophe.addParameterToUrl(href, 'a-refresh-reversion', version);
    }
    href = apostrophe.addParameterToUrl(href, 'a-refresh-unique', Math.floor(Math.random() * 1000000000));
    window.location.href = href;
  });
</script>
