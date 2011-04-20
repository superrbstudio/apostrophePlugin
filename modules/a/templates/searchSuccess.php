<?php
  // Compatible with sf_escaping_strategy: true
  $pager = isset($pager) ? $sf_data->getRaw('pager') : null;
  $pagerUrl = isset($pagerUrl) ? $sf_data->getRaw('pagerUrl') : null;
  $results = isset($results) ? $sf_data->getRaw('results') : null;
?>
<?php use_helper('a') ?>
<?php slot('body_class') ?>a-search-success<?php end_slot() ?>

<?php include_partial('a/searchBefore', array('q' => $sf_request->getParameter('q', ESC_RAW))) ?>

	<h2 class="a-search-results-heading"><?php echo __('Search: "%phrase%"', array('%phrase%' =>  htmlspecialchars($sf_request->getParameter('q', ESC_RAW))), 'apostrophe') ?></h2>
	
	<h4 class="a-search-results-count">
		<?php if (!$pager->getNbResults()): ?>
			No results were found.
		<?php endif ?>
		<?php if ($pager->getNbResults() == 1): ?>
			1 result was found.
		<?php endif ?>
		<?php if ($pager->getNbResults() > 1): ?>
			<?php echo $pager->getNbResults() ?> results were found.
		<?php endif ?>
	</h4>	
	
	<dl class="a-search-results">
	<?php foreach ($results as $result): ?>
	  <?php if (isset($result->partial)): ?>
	    <?php include_partial($result->partial, array('result' => $result)) ?>
	  <?php else: ?>
  	  <?php $url = $result->url ?>
  	  <dt class="result-title <?php echo $result->class ?>">
  			<?php echo link_to($result->title, $url) ?>
  		</dt>
  	  <dd class="result-summary"><?php echo $result->summary ?></dd>
  		<dd class="result-url"><?php echo link_to($url,$url) ?></dd>
  	<?php endif ?>
	<?php endforeach ?>
	</dl>

	<div class="a-search-footer">
	  <?php include_partial('aPager/pager', array('pager' => $pager, 'pagerUrl' => $pagerUrl)) ?>
	</div>

<?php include_partial('a/searchAfter', array('q' => $sf_request->getParameter('q', ESC_RAW))) ?>
