<?php use_helper('a') ?>
<div id="a-search" class="a-search global a-form-row">
  <form action="<?php echo url_for('a/search') ?>" method="get">
		<div> <?php // div is for page validation ?>
			<label for="a-search-cms-field" style="display:none;">Search</label><?php // label for accessibility ?>
			<input type="text" name="q" value="<?php echo htmlspecialchars($sf_params->get('q', ESC_RAW)) ?>" class="a-search-field" id="a-search-cms-field"/>
			<input type="image" src="/apostrophePlugin/images/a-special-blank.gif" class="submit a-search-submit" value="Search Pages" alt="Search" title="Search"/>
		</div>
  </form>
</div>
<?php a_js_call('apostrophe.selfLabel(?)', array('selector' => '#a-search-cms-field', 'title' => a_('Search'))) ?>
