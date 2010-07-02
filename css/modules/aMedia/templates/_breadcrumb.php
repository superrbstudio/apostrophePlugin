<div id="a-breadcrumb" class="media">
  <?php foreach ($crumbs as $crumb): ?>
    <?php if (!isset($crumb['first'])): ?>
      <span class="a-breadcrumb-slash media"> / </span>
    <?php endif ?>
    <?php if (isset($crumb['last'])): ?>
      <h2 class="you-are-here">
    <?php endif ?>
    <?php echo link_to($crumb['label'], $crumb['link']) ?>
    <?php if (isset($crumb['last'])): ?>
      </h2>
    <?php endif ?>
  <?php endforeach ?>
</div>
