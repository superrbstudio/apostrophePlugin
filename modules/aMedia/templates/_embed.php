<?php use_helper('a') ?>
<div class="a-media-add-subheading a-media-add-embed">
<h3><?php echo a_('Add by Embed Code') ?></h3>
<p><?php echo a_("You can paste almost any embed code below. You can also paste a URL for the following services: ") ?>
  <?php $list = array() ?>
  <?php echo implode(a_(', '), aMediaTools::getEmbedServiceNames()) ?>
</p>

<?php $form = new aMediaVideoEmbedForm() ?>
<form id="a-media-video-add-by-embed-form" class="a-media-search-form" method="POST" action="<?php echo url_for("aMedia/editVideo") ?>">

	<div class="a-form-row a-hidden">
  	<?php echo $form->renderHiddenFields() ?>
	</div>

	<div class="a-form-row a-search-field" style="position:relative">
    <label for="a-media-video-embed"></label>
		<div class="a-form-field">
    	<?php echo $form['embed']->render(array('class' => 'a-search-video a-search-form', 'id' => 'a-media-video-embed')) ?>
		</div>
	</div>

	<div class="a-form-row example">
    <p><?php echo __('Example: %embed%', array('%embed%' => htmlspecialchars('<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="437" height="291" ...</object>')), 'apostrophe') ?></p>
    <input type="hidden" name="first_pass" value="1" class="a-hidden" /> 
	</div>

	<ul class="a-ui a-controls" id="a-media-video-add-by-embed-form-submit">
    <li><input type="submit" value="<?php echo __('Save', null, 'apostrophe') ?>" class="a-btn a-submit" /></li>
  	<li><a href="#" class="a-btn icon a-cancel"><span class="icon"></span><?php echo a_('Cancel') ?></a></li>
  </ul>
</form>
</div>

<?php a_js_call('apostrophe.selfLabel(?)', array('selector' => '#a-media-video-embed', 'title' => (isset($search) ? $search : '<object>...</object>'))) ?>
