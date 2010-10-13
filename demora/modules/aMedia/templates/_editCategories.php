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
          <?php echo jq_link_to_remote('<span class="icon"></span>'.__('Delete', null, 'apostrophe'), array('url' => "aMedia/deleteCategory?" . http_build_query(array('slug' => $info['slug'])), 'update' => 'a-media-edit-categories'), array("class" => "a-btn icon no-label a-delete")) ?>
        </li>
      </ul>
    </li>
  <?php endforeach ?>
</ul>

<form action="<?php echo url_for('aMedia/addCategory') ?>" method="post" class="a-remote-form">
	<?php echo $form ?>
	<div class="a-ui a-form-row submit">
	<a href="#add-category" onclick="return false;" class="a-btn icon a-add no-label a-remote-submit"><span class="icon"></span><?php echo __('add', null, 'apostrophe') ?></a>
	<a href="#cancel" onclick="$('#a-media-edit-categories-button, #a-media-no-categories-message, #a-category-sidebar-list').show(); $('#a-media-edit-categories').html(''); return false;" class="a-btn icon a-cancel no-label"><span class="icon"></span><?php echo __('Cancel', null, 'apostrophe') ?></a>
	</div>
</form>

<?php a_js_call('apostrophe.mediaCategories(?)', array('newCategoryLabel' => a_('New Category'))) ?>
<?php a_include_js_calls() ?>