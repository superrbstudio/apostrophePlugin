<?php use_helper('I18N') ?>
<div id="a-search">
  <form id="a-search-global" action="<?php echo url_for('a/search') ?>" method="get" class="a-search-form">
    <div><label for="a-search-cms-field" style="display:none;">Search</label><input type="text" name="q" value="<?php echo htmlspecialchars($sf_params->get('q')) ?>" class="a-search-field" id="a-search-cms-field"/></div>
    <div><input type="image" src="/apostrophePlugin/images/a-special-blank.gif" class="submit" value="Search Pages" alt="Search" title="Search"/></div>
  </form>
</div>
<?php a_js_call('apostrophe.selfLabel(?)', array('selector' => '#a-search-cms-field', 'title' => a_('Search'))) ?>
