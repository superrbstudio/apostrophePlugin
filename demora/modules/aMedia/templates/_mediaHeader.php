<div class="a-admin-header">
	<?php if (aMediaTools::userHasUploadPrivilege()): ?>
		  <ul class="a-ui a-controls a-admin-controls">
				<li><h3 class="a-admin-title"><?php echo link_to('<span class="icon"></span>'.__('Media Library', null, 'apostrophe'), '@a_media_index', array('class' => 'a-btn big lite'))?></h3></li>
		    <?php $typeLabel = aMediaTools::getBestTypeLabel() ?>
		    <?php if ($uploadAllowed): ?>
		      <li><a href="<?php echo url_for("aMedia/upload") ?>" class="a-btn icon big a-add"><span class="icon"></span><?php echo a_('Upload ' . $typeLabel) ?></a></li>
		    <?php endif ?>
		    <?php if ($embedAllowed): ?>
		      <li><a href="<?php echo url_for("aMedia/embed") ?>" class="a-btn icon big a-add"><span class="icon"></span><?php echo a_('Embed ' . $typeLabel) ?></a></li>
		      <li><a href="<?php echo url_for("aMedia/searchServices") ?>" class="a-btn icon big a-add"><span class="icon"></span><?php echo a_('Search Services') ?></a></li>
		      <?php if (aMediaTools::getOption('linked_accounts') && aMediaTools::userHasAdminPrivilege()): ?>
			      <li><a href="<?php echo url_for("aMedia/link") ?>" class="a-btn icon big a-add"><span class="icon"></span><?php echo a_('Linked Accounts') ?></a></li>
			    <?php endif ?>
		    <?php endif ?>
		 </ul>
	<?php endif ?>
</div>