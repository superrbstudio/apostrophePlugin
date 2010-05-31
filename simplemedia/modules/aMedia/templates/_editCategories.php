<script>
$('#a-media-edit-categories-button').hide();
</script>
<h3>Edit Categories</h3>
<ul>
  <?php foreach ($categoriesInfo as $info): ?>
    <li>
      <ul>
        <li class="name">
          <?php echo htmlspecialchars($info['name']) ?> (<?php echo $info['count'] ?>)
        </li>
        <li class="actions">
          <?php echo jq_link_to_remote("Delete", array('url' => "aMedia/deleteCategory?" . http_build_query(array('slug' => $info['slug'])), 'update' => 'a-media-edit-categories'), array("class" => "a_btn a_delete")) ?>
        </li>
      </ul>
    </li>
  <?php endforeach ?>
</ul>
<?php echo jq_form_remote_tag(array('url' => 'aMedia/addCategory', 'update' => 'a-media-edit-categories')) ?>
<?php echo $form ?>
<input type="submit" name="Add" value="Add" class="" />
</form>
<?php echo jq_link_to_function('Close', '$("#a-media-edit-categories-button").show(); $("#a-media-edit-categories").html("")') ?>