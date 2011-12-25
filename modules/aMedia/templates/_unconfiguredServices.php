<?php if (aMediaTools::userHasAdminPrivilege()): ?>
  <?php $unconfigured = aMediaTools::getEmbedServices(false) ?>
  <p class="a-help">You must configure these services before you can use them.</p>
  <ul class="a-ui a-controls stacked a-media-unconfigured-services">
    <?php foreach ($unconfigured as $service): ?>
      <li><?php echo link_to(a_("Configure %service%", array('%service%' => $service->getName())), $service->configurationHelpUrl(), array('class' => 'a-btn alt')) ?></li>
    <?php endforeach ?>
  </ul>
<?php endif ?>