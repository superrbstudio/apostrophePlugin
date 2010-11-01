<?php use_helper('a') ?>
<?php slot('body_class') ?>a-media<?php end_slot() ?>

<?php slot('a-page-header') ?>
	<?php include_partial('aMedia/mediaHeader', array('uploadAllowed' => $uploadAllowed, 'embedAllowed' => $embedAllowed)) ?>
<?php end_slot() ?>
<?php include_component('aMedia', 'browser') ?>

<div class="a-media-library">

	<div class="a-media-select a-search">
	  <h3><?php echo a_('Search Services') ?></h3>
	  <form method="POST" action="<?php echo url_for('aMedia/searchServices') ?>" class="a-search-form a-media-search-services a-media-services-form a-ui" id="a-media-search-services">
			<div class="a-form-row a-hidden">
				<?php echo $form->renderHiddenFields() ?>
			</div>

			<div class="a-form-row service">
				<div class='a-form-field'>
					<?php echo $form['service']->render() ?>
				</div>
				<?php echo $form['service']->renderError() ?>
			</div>
	
			<div class="a-form-row search"> <?php // div is for page validation ?>
				<label for="a-search-cms-field" style="display:none;">Search</label><?php // label for accessibility ?>
    		<?php echo $form['q']->render(array('class' => 'a-search-field')) ?>					
				<?php if (isset($q)): ?>
			    <?php echo link_to(__('Clear Search', null, 'apostrophe'), aUrl::addParams($current, array('q' => '')), array('id' => 'a-media-search-remove', 'title' => __('Clear Search', null, 'apostrophe'), )) ?>						
				<?php else: ?>
					<input type="image" src="/apostrophePlugin/images/a-special-blank.gif" class="submit a-search-submit" value="Search Pages" alt="Search" title="Search"/>
				<?php endif ?>
			</div>

	    <div class="a-form-row cancel" id="a-media-video-add-by-embed-form-submit">
				<?php echo link_to('<span class="icon"></span>'.a_("Cancel"), 'aMedia/resume', array("class" => "a-btn icon a-cancel")) ?>
	    </div>	
	  </form>
	
	</div>
  <?php if (isset($pager)): ?>
    <?php include_partial('aMedia/videoSearch', array('url' => $url, 'pager' => $pager, 'service' => $service)) ?>
  <?php endif ?>
</div>

<?php a_js_call('apostrophe.selfLabel(?)', array('selector' => '#a-media-search-services .a-search-field', 'title' => a_('Search'))) ?>