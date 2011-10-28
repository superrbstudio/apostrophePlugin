<?php if (sfConfig::get('app_a_googleAnalytics') && ((sfConfig::get('sf_environment') != 'dev') || sfConfig::get('app_a_dev_analytics'))) : ?>
  <?php $analytics = sfConfig::get('app_a_googleAnalytics') ?>
  <script type="text/javascript">

    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', <?php echo json_encode($analytics['account']) ?>]);
    <?php // Not all sites need this field ?>

    <?php if (isset($analytics['domainName'])): ?>
      _gaq.push(['_setDomainName', <?php echo json_encode($analytics['domainName']) ?>]);
    <?php endif ?>

    <?php if (isset($analytics['allowLinker'])): ?>
  		_gaq.push(['_setAllowLinker', <?php echo $analytics['allowLinker'] ?>]);
    <?php endif ?>

    _gaq.push(['_trackPageview']);

    (function() {
      var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();

  </script>
<?php endif ?>