<?php use_helper('I18N') ?>
<?php # Not really a new pager - just a well-styled partial for use with ?>
<?php # sfPager implementations (i.e. sfDoctrinePager). ?>
<?php # Pass $pager and $pagerUrl. $pagerUrl can have other parameters in ?>
<?php # the query string already. This partial will add the page parameter ?>
<?php # as appropriate. ?>

<?php if ($pager->haveToPaginate()): ?>
<div class="a-pager-navigation">
	<?php if ($pager->getPage() == 1):?>
		<span class="a-pager-navigation-image a-pager-navigation-first a-pager-navigation-disabled"><?php echo __('First Page', null, 'apostrophe') ?></span>	
	  <span class="a-pager-navigation-image a-pager-navigation-previous a-pager-navigation-disabled"><?php echo __('Previous Page', null, 'apostrophe') ?></span>
	<?php else: ?>
		<a href="<?php echo url_for(aUrl::addParams($pagerUrl, array('page' => 1))) ?>" class="a-pager-navigation-image a-pager-navigation-first"><?php echo __('First Page', null, 'apostrophe') ?></a>
  	<a href="<?php echo url_for(aUrl::addParams($pagerUrl, array('page' => $pager->getPreviousPage()))) ?>" class="a-pager-navigation-image a-pager-navigation-previous"><?php echo __('Previous Page', null, 'apostrophe') ?></a>
	<?php endif ?>

  <?php foreach ($pager->getLinks() as $page): ?>
    <?php if ($page == $pager->getPage()): ?>
      <span class="a-page-navigation-number a-pager-navigation-disabled"><?php echo $page ?></span>
    <?php else: ?>
      <a href="<?php echo url_for(aUrl::addParams($pagerUrl, array('page' => $page))) ?>" class="a-page-navigation-number"><?php echo $page ?></a>
    <?php endif; ?>
  <?php endforeach; ?>
	<?php if ($pager->getPage() >= $pager->getLastPage()):?>
	  <span class="a-pager-navigation-image a-pager-navigation-next a-pager-navigation-disabled"><?php echo __('Next Page', null, 'apostrophe') ?></span>
		<span class="a-pager-navigation-image a-pager-navigation-last a-pager-navigation-disabled"><?php echo __('Last Page', null, 'apostrophe') ?></span>	
	<?php else: ?>
	  <a href="<?php echo url_for(aUrl::addParams($pagerUrl, array('page' => $pager->getNextPage()))) ?>" class="a-pager-navigation-image a-pager-navigation-next"><?php echo __('Next Page', null, 'apostrophe') ?></a>
  	<a href="<?php echo url_for(aUrl::addParams($pagerUrl, array('page' => $pager->getLastPage()))) ?>" class="a-pager-navigation-image a-pager-navigation-last"><?php echo __('Last Page', null, 'apostrophe') ?></a>
	<?php endif ?>
</div>
<?php endif ?>