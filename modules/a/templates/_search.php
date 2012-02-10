<?php use_helper('a') ?>
<div id="a-search" class="a-ui a-search global">
  <form action="<?php echo a_url('a', 'search') ?>" method="get">
		<div class="a-form-row"> <?php // div is for page validation ?>
			<label for="a-search-cms-field" style="display:none;">Search</label><?php // label for accessibility ?>
			<input type="text" name="q" value="<?php echo aHtml::entities($sf_data->getRaw('sf_params')->get('q')) ?>" class="a-search-field" id="a-search-cms-field"/>
			<input type="image" src="<?php echo image_path('/apostrophePlugin/images/a-special-blank.gif') ?>" class="submit a-search-submit" alt="Search" title="Search"/>
		</div>
  </form>
</div>
<?php a_js_call('apostrophe.selfLabel(?)', array('selector' => '#a-search-cms-field', 'title' => a_('Search'), 'focus' => false)) ?>