<td class="a-admin-text name"><?php echo get_partial('aCategoryAdmin/name', array('type' => 'list', 'a_category' => $a_category)) ?></td>
<?php $counts = $helper->counts ?>
<?php // Show counts for various categorizable things (media, posts, etc). ?>
<?php foreach ($counts as $info): ?>
  <td class="a-admin-text <?php echo $info['class'] ?>">
    <?php if (isset($info['counts'][$a_category->id])): ?>
      <?php echo $info['counts'][$a_category->id]['count'] ?>
    <?php endif ?>
  </td>
<?php endforeach ?>
<td class="a-admin-text users"><?php echo get_partial('aCategoryAdmin/users', array('type' => 'list', 'a_category' => $a_category)) ?></td>
<td class="a-admin-text groups"><?php echo get_partial('aCategoryAdmin/groups', array('type' => 'list', 'a_category' => $a_category)) ?></td>
