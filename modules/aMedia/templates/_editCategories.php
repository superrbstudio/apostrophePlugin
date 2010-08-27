<?php
  // Compatible with sf_escaping_strategy: true
  $categoriesInfo = isset($categoriesInfo) ? $sf_data->getRaw('categoriesInfo') : null;
  $form = isset($form) ? $sf_data->getRaw('form') : null;
?>
<?php use_Helper('I18N') ?>
<ul class="a-ui a-media-categories-list" id="a-media-categories-list">
  <?php foreach ($categoriesInfo as $info): ?>
    <li class="category">
      <ul>
        <li class="name">
          <?php echo htmlspecialchars($info['name']) ?> (<?php echo $info['count'] ?>)
        </li>
        <li class="actions">
          <?php echo jq_link_to_remote(__('Delete', null, 'apostrophe'), array('url' => "aMedia/deleteCategory?" . http_build_query(array('slug' => $info['slug'])), 'update' => 'a-media-edit-categories'), array("class" => "a-btn icon no-label a-delete")) ?>
        </li>
      </ul>
    </li>
  <?php endforeach ?>
</ul>

<?php echo jq_form_remote_tag(array(
	'url' => 'aMedia/addCategory', 
	'update' => 'a-media-edit-categories',
	'complete' => '$("#a_media_category_name").val("")', 
)) ?>

<?php echo $form ?>

<div class="a-ui a-form-row submit">
<input type="submit" name="add" value="<?php echo __('add', null, 'apostrophe') ?>" class="a-btn a-submit icon a-add no-label" />
<a href="#cancel" onclick="$('#a-media-edit-categories-button, #a-media-no-categories-message, #a-category-sidebar-list').show(); $('#a-media-edit-categories').html(''); return false;" class="a-btn icon a-cancel no-label"><span class="icon"></span><?php echo __('Cancel', null, 'apostrophe') ?></a>
</div>
</form>

<script type="text/javascript" charset="utf-8">
$(document).ready(function() {
	aInputSelfLabel('#a_media_category_name', <?php echo json_encode(__('New Category', null, 'apostrophe')) ?>);	
	$('#a-media-edit-categories-button, #a-media-no-categories-messagem, #a-category-sidebar-list').hide();
	$('#a_media_category_name').focus();
	// Temporary - See CSS for Notes
	$('#a_media_category_description').parents('div.a-form-row').addClass('hide-description').parent().attr('id','a-media-category-form');
});
</script>