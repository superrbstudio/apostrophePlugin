<?php if (isset($form['editors']) || isset($form['managers'])): ?>
	<h4><?php echo __('Page Permissions', null, 'apostrophe') ?></h4>
	<div class="a-page-permissions">
	  <?php include_partial('a/privileges', 
	    array('form' => $form, 'widget' => 'editors',
	      'label' => 'Editors', 'inherited' => $inherited['edit'],
	      'admin' => $admin['edit'])) ?>
	  <?php include_partial('a/privileges', 
	    array('form' => $form, 'widget' => 'managers',
	      'label' => 'Managers', 'inherited' => $inherited['manage'],
	      'admin' => $admin['manage'])) ?>
	</div>
<?php endif ?>
