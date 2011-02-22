	<?php use_helper('I18N') ?>
<div class="a-history-browser dropshadow">
	<div class="a-history-browser-heading">
		<a href="#big-history-button" class="a-btn icon a-history-btn big" id="a-history-heading-button" style="margin: 5px" id="" onclick="return false;"><?php echo __('You are browsing past revisions for this area.', null, 'apostrophe') ?></a>
		<a href="#close-history-browser" onclick="return false;" id="a-history-close-button" class="a-btn no-label icon a-close nobg" title="<?php echo __('Close History Browser', null, 'apostrophe') ?>"><?php echo __('Close History Browser', null, 'apostrophe') ?></a>
	</div>
	<div class="a-history-browser-crop">
		<table cellspacing="0" cellpadding="0" border="0" title="<?php echo htmlspecialchars(__('Choose a revision.', null, 'apostrophe')) ?>">
			<thead>
			<tr>
				<th class="date"><?php echo __('Date', null, 'apostrophe') ?></th>
				<th class="editor"><?php echo __('Editor', null, 'apostrophe') ?></th>
				<th class="preview"><?php echo __('Preview', null, 'apostrophe') ?></th>
			</tr>
			</thead>
			<tbody class="a-history-items">
			<tr class="a-history-item">
				<td class="date"><img src="/apostrophePlugin/images/a-icon-loader.gif"></td>
				<td class="editor"></td>
				<td class="preview"></td>
			</tr>
			</tbody>
			<tfoot>
			  <tr>
				  <td colspan="3">
						<span id="a-history-browser-number-of-revisions">Revisions</span>
				    <a href="#" class="a-history-browser-view-more" id="a-history-browser-view-more"><?php echo __('View More Revisions', null, 'apostrophe') ?> <img src="/apostrophePlugin/images/a-icon-loader.gif" class="spinner" /></a>
          </td>
			  </tr>
			</tfoot>
		</table>
	</div>
</div>

<div class="a-history-preview-notice dropshadow">
	<h4>History Preview</h4>
	<p><?php echo __('You are previewing another version of this content area. This will not become the current version unless you click "Save As Current Revision." If you change your mind, click "Cancel."', null, 'apostrophe') ?></p>
	<div class="a-history-options">
		<a href="#save-current-revision" class="a-btn icon a-history-revert" id="a-history-revert-button"><?php echo __('Save as Current Revision', null, 'apostrophe') ?></a>	<a href="#cancel-history-browser" onclick="return false;" id="a-history-cancel-button" class="a-btn a-cancel"><?php echo __('Cancel', null, 'apostrophe') ?></a>
	</div>
</div>

