<?php use_helper('a') ?>
<?php slot('body_class') ?>a-media<?php end_slot() ?>

<?php slot('a-page-header') ?>
  <div class="a-admin-header">
    <h3 class="a-admin-title"><?php echo link_to(a_('Media Library'), 'aMedia/resume') ?></h3>
  </div>
<?php end_slot() ?>
<?php include_component('aMedia', 'browser') ?>

<div class="a-media-library">
  <h3><?php echo a_('Add by Embed Code') ?></h3>
	<p><?php echo a_("You can paste almost any embed code below. You can also paste a URL for the following services: ") ?>
	  <?php $list = array() ?>
	  <?php echo implode(a_(', '), aMediaTools::getEmbedServiceNames()) ?>
	</p>
	<form id="a-media-video-add-by-embed-form" class="a-media-search-form" method="POST" action="<?php echo url_for("aMedia/editVideo") ?>">
  	<div class="a-form-row a-search-field" style="position:relative">
      <label for="a-media-video-embed"></label>
      <?php $form = new aMediaVideoEmbedForm() ?>
      <?php echo $form->renderHiddenFields() ?>
      <?php echo $form['embed']->render(array('class' => 'a-search-video a-search-form', 'id' => 'a-media-video-embed')) ?>
  	</div>

  	<div class="a-form-row example">
      <p><?php echo __('Example: %embed%', array('%embed%' => htmlspecialchars('<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="437" height="291" ...</object>')), 'apostrophe') ?></p>
      <input type="hidden" name="first_pass" value="1" /> 
  	</div>

  	<ul class="a-ui a-controls" id="a-media-video-add-by-embed-form-submit">
      <li><input type="submit" value="<?php echo __('Go', null, 'apostrophe') ?>" class="a-btn a-submit" /></li>
      <li>
  			<?php echo link_to(a_("Cancel"), 'aMedia/resume', array("class" => "a-btn icon a-cancel")) ?>
  		</li>
    </ul>
 </form>
</div>

<script type="text/javascript" charset="utf-8">
	$(document).ready(function() {
		aInputSelfLabel('#a-media-video-embed', <?php echo json_encode(isset($search) ? $search : '<object>...</object>') ?>);			
	});
</script>