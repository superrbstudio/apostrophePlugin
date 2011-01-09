<?php
  // Compatible with sf_escaping_strategy: true
  $pager = isset($pager) ? $sf_data->getRaw('pager') : null;
  $pagerUrl = isset($pagerUrl) ? $sf_data->getRaw('pagerUrl') : null;
  // Our pager crashes the browser with 3,000+ pages as is common on YouTube
	$nb_pages = min($pager->getLastPage(), 300);
	$nb_links = isset($nb_links) ? $sf_data->getRaw('nb_links') : sfConfig::get('app_a_pager_nb_links', 5);
	$nb_links = ($nb_links > $nb_pages) ? $nb_pages : $nb_links;
?>
<?php use_helper('a') ?>
<?php # Not really a new pager - just a well-styled partial for use with ?>
<?php # sfPager implementations (i.e. sfDoctrinePager). ?>
<?php # Pass $pager and $pagerUrl. $pagerUrl can have other parameters in ?>
<?php # the query string already. This partial will add the page parameter ?>
<?php # as appropriate. ?>

<?php if ($pager->haveToPaginate()): ?>
<div class="a-pager-navigation a-ui">
		<a href="<?php echo url_for(aUrl::addParams($pagerUrl, array('page' => 1))) ?>" class="a-pager-navigation-image a-pager-navigation-first"><?php echo __('First Page', null, 'apostrophe') ?></a>
  	<a href="<?php echo url_for(aUrl::addParams($pagerUrl, array('page' => (($pager->getPage() - $nb_links) > 0) ? $pager->getPage() - $nb_links : 1))) ?>" class="a-pager-navigation-image a-pager-navigation-previous"><?php echo __('Previous Page', null, 'apostrophe') ?></a>

	<span class="a-pager-navigation-links-container-container">
	<span class="a-pager-navigation-links-container">
  <?php foreach ($pager->getLinks($nb_pages) as $page): ?>
    <?php if ($page == $pager->getPage()): ?>
      <span class="a-btn lite a-page-navigation-number a-pager-navigation-disabled"><?php echo $page ?></span>
    <?php else: ?>
      <a href="<?php echo url_for(aUrl::addParams($pagerUrl, array('page' => $page))) ?>" class="a-btn lite a-page-navigation-number<?php echo (($page < $pager->getPage())? " a-page-navigation-number-before" : (($page > $pager->getPage())? " a-page-navigation-number-after" : "")) ?>"><?php echo $page ?></a>
    <?php endif; ?>
  <?php endforeach; ?>
	</span>
	</span>

	  <a href="<?php echo url_for(aUrl::addParams($pagerUrl, array('page' => (($pager->getPage() + $nb_links) < $nb_pages) ? $pager->getPage() + $nb_links : $pager->getLastPage()))) ?>" class="a-pager-navigation-image a-pager-navigation-next"><?php echo __('Next Page', null, 'apostrophe') ?></a>
  	<a href="<?php echo url_for(aUrl::addParams($pagerUrl, array('page' => $pager->getLastPage()))) ?>" class="a-pager-navigation-image a-pager-navigation-last"><?php echo __('Last Page', null, 'apostrophe') ?></a>
	
</div>
<?php endif ?>

<?php a_js_call('apostrophe.pager(?, ?)', '.a-pager-navigation', array('nb-links' => $nb_links, 'nb-pages' => $nb_pages)) ?>
