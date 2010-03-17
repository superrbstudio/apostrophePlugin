<?php use_helper('I18N') ?>
<div class="a-history-browser dropshadow">
	<h3>
		<?php echo __('You are browsing past revisions for this area.', null, 'apostrophe') ?>
  </h3>
	<div class="a-history-browser-crop">
		<table cellspacing="0" cellpadding="0" border="0" title="<?php echo htmlspecialchars(__('Choose a revision.', null, 'apostrophe')) ?>">
			<thead>
			<tr>
				<?php if (0): ?>
				  <th class="id"><?php echo __('ID', null, 'apostrophe') ?></th>
				<?php endif ?>
				<th class="date"><?php echo __('Date', null, 'apostrophe') ?></th>
				<th class="editor"><?php echo __('Editor', null, 'apostrophe') ?></th>
				<th class="preview"><?php echo __('Preview', null, 'apostrophe') ?></th>
			</tr>
			</thead>
			<tfoot>
			<?php if (1): ?>
			  <tr>
				  <td colspan="3">
				    <a href="#" class="a-history-browser-view-more"><?php echo __('View More Revisions', null, 'apostrophe') ?> <img src="/apostrophePlugin/images/a-icon-loader.gif" class="spinner" /></a>
          </td>
					<td class="number-of-revisions"></td>
			  </tr>
			<?php endif ?>
			</tfoot>
			<tbody class="a-history-items"> <?php // this replaces the history container, we want to return a list of populated rows <TR></TR> ?>
			<tr class="a-history-item">
				<?php if (0): ?>
				  <td class="id"></td>
				<?php endif ?>
				<td class="date"><img src="/apostrophePlugin/images/a-icon-loader.gif"></td>
				<td class="editor"></td>
				<td class="preview"></td>
			</tr>
			</tbody>
		</table>
	</div>
</div>

<div class="a-history-preview-notice">
	<div>
	<?php echo __('You are previewing another version of this material. This will not become the current version unless you click "Save As Current Revision." If you change your mind, click "Cancel."', null, 'apostrophe') ?>
	</div>
</div>

