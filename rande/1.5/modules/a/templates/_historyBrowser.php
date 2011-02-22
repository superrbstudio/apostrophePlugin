<?php use_helper('a') ?>
<div class="a-ui a-history-browser dropshadow clearfix">
	<div class="a-history-browser-heading-container clearfix">
		<h4 class="a-history-browser-heading"><?php echo a_('You are browsing past revisions for this area.') ?></h4>
		<?php echo a_js_button(a_('Close History Browser'), array('icon', 'a-close', 'no-label', 'big', 'no-bg'), 'a-history-close-button') ?>
	</div>
	<div class="a-history-browser-crop clearfix">
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
						<span class="a-history-browser-revisions" id="a-history-browser-number-of-revisions">Revisions</span>
				    <a href="#" class="a-history-browser-view-more" id="a-history-browser-view-more"><?php echo __('View More Revisions', null, 'apostrophe') ?> <img src="/apostrophePlugin/images/a-icon-loader.gif" class="spinner" /></a>
          </td>
			  </tr>
			</tfoot>
		</table>
	</div>
</div>

<div class="a-history-preview-notice dropshadow">
	<h4>History Preview</h4>
	<a href="#" onclick="return false;" id="a-history-preview-notice-toggle">Hide</a>
	<p><?php echo __('You are previewing another version of this content area. This will not become the current version unless you click "Save As Current Revision." If you change your mind, click "Cancel."', null, 'apostrophe') ?></p>
	<div class="a-history-options">
		<a href="#save-current-revision" class="a-btn icon a-history-revert" id="a-history-revert-button"><span class="icon"></span><?php echo a_('Save as Current Revision') ?></a>
		<a href="#cancel-history-browser" onclick="return false;" id="a-history-cancel-button" class="a-btn icon a-cancel"><span class="icon"></span><?php echo a_('Cancel') ?></a>
	</div>
</div>