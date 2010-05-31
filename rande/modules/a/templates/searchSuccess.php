<?php use_helper('a', 'I18N') ?>
<?php slot('body_class') ?>a-search-results<?php end_slot() ?>


<div id="a-search-results-container">

	<h2><?php echo __('Search: "%phrase%"', array('%phrase%' =>  htmlspecialchars($sf_request->getParameter('q'))), 'apostrophe') ?></h2>
	
	<dl class="a-search-results">
	<?php foreach ($results as $result): ?>
	  <?php $url = $result->url ?>
	  <dt class="result-title <?php echo $result->class ?>">
			<?php echo link_to($result->title, $url) ?>
		</dt>
	  <dd class="result-summary"><?php echo $result->summary ?></dd>
		<dd class="result-url"><?php echo $url ?></dd>
	<?php endforeach ?>
	</dl>

	<div class="a-search-footer">
	  <?php include_partial('aPager/pager', array('pager' => $pager, 'pagerUrl' => $pagerUrl)) ?>
	</div>

</div>
