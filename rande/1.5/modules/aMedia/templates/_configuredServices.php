<?php $unconfigured = aMediaTools::getEmbedServices(false) ?>
<ul class="a-media-unconfigured-services">
  <?php foreach ($unconfigured as $service): ?>
    <?php echo link_to(a_("<li>%service% requires configuration.</li>\n", array('%service%' => $service->getName())), $service->configurationHelpUrl()) ?>
  <?php endforeach ?>
</ul>
