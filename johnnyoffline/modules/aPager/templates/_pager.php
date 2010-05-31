<?php # Not really a new pager - just a well-styled partial for use with ?>
<?php # sfPager implementations (i.e. sfDoctrinePager). ?>
<?php # Pass $pager and $pagerUrl. $pagerUrl can have other parameters in ?>
<?php # the query string already. This partial will add the page parameter ?>
<?php # as appropriate. ?>

<?php if ($pager->haveToPaginate()): ?>
<div class="a_pager_navigation">
	<?php if ($pager->getPage() == 1):?>
		<span class="a_pager_navigation_image a_pager_navigation_first a_pager_navigation_disabled">First Page</span>	
	  <span class="a_pager_navigation_image a_pager_navigation_previous a_pager_navigation_disabled">Previous Page</span>
	<?php else: ?>
		<a href="<?php echo url_for(aUrl::addParams($pagerUrl, array('page' => 1))) ?>" class="a_pager_navigation_image a_pager_navigation_first">First Page</a>
  	<a href="<?php echo url_for(aUrl::addParams($pagerUrl, array('page' => $pager->getPreviousPage()))) ?>" class="a_pager_navigation_image a_pager_navigation_previous">Previous Page</a>
	<?php endif ?>

  <?php foreach ($pager->getLinks() as $page): ?>
    <?php if ($page == $pager->getPage()): ?>
      <span class="a_page_navigation_number a_pager_navigation_disabled"><?php echo $page ?></span>
    <?php else: ?>
      <a href="<?php echo url_for(aUrl::addParams($pagerUrl, array('page' => $page))) ?>" class="a_page_navigation_number"><?php echo $page ?></a>
    <?php endif; ?>
  <?php endforeach; ?>
	<?php if ($pager->getPage() >= $pager->getLastPage()):?>
	  <span class="a_pager_navigation_image a_pager_navigation_next a_pager_navigation_disabled">Next Page</span>
		<span class="a_pager_navigation_image a_pager_navigation_last a_pager_navigation_disabled">Last Page</span>	
	<?php else: ?>
	  <a href="<?php echo url_for(aUrl::addParams($pagerUrl, array('page' => $pager->getNextPage()))) ?>" class="a_pager_navigation_image a_pager_navigation_next">Next Page</a>
  	<a href="<?php echo url_for(aUrl::addParams($pagerUrl, array('page' => $pager->getLastPage()))) ?>" class="a_pager_navigation_image a_pager_navigation_last">Last Page</a>
	<?php endif ?>
</div>
<?php endif ?>