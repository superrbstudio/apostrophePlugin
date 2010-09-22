
	<?php slot('a-admin.current-header') ?>
	<th class="a-admin-text a-admin-list-th-name">
		  <?php if ('name' == $sort[0]): ?>
	    <?php echo link_to(__('Category', array(), 'apostrophe'), 'aCategoryAdmin/index?sort=name&sort_type='.($sort[1] == 'asc' ? 'desc' : 'asc')) ?>
	    <?php echo image_tag(((sfConfig::get('app_aAdmin_web_dir'))?sfConfig::get('app_aAdmin_web_dir'):'/apostrophePlugin').'/images/'.$sort[1].'.png', array('alt' => __($sort[1], array(), 'apostrophe'), 'title' => __($sort[1], array(), 'apostrophe'))) ?>
	  <?php else: ?>
	    <?php echo link_to(__('Category', array(), 'apostrophe'), 'aCategoryAdmin/index?sort=name&sort_type=asc') ?>
	  <?php endif; ?>
		</th>
	<?php end_slot(); ?>

<?php include_slot('a-admin.current-header') ?>

<?php $counts = $helper->counts ?>

<?php foreach ($counts as $info): ?>
	<th class="a-admin-text a-admin-list-th-<?php echo $info['class'] ?>">
	  <?php echo __($info['name'], array(), 'apostrophe') ?>
	</th>
<?php endforeach ?>

	<?php slot('a-admin.current-header') ?>
	<th class="a-admin-text a-admin-list-th-users">
		  <?php echo __('Active Users', array(), 'apostrophe') ?>
		</th>
	<?php end_slot(); ?>

<?php include_slot('a-admin.current-header') ?>

	<?php slot('a-admin.current-header') ?>
	<th class="a-admin-text a-admin-list-th-groups">
		  <?php echo __('Groups', array(), 'apostrophe') ?>
		</th>
	<?php end_slot(); ?>

<?php include_slot('a-admin.current-header') ?>
