<div class="a-history-browser dropshadow">
	<h3>
		You are browsing past revisions for this area.
  </h3>
	<div class="a-history-browser-crop">
		<table cellspacing="0" cellpadding="0" border="0" title="Choose a revision.">
			<thead>
			<tr>
				<?php if (0): ?>
				  <th class="id">ID</th>
				<?php endif ?>
				<th class="date">Date</th>
				<th class="editor">Editor</th>
				<th class="preview">Preview</th>
			</tr>
			</thead>
			<tfoot>
			<?php if (1): ?>
			  <tr>
				  <td colspan="3">
				    <a href="#" class="a-history-browser-view-more">View More Revisions <img src="/apostrophePlugin/images/a-icon-loader.gif" class="spinner" /></a>
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
  You are previewing another version of this material. 
  This will not become the current version unless you click "Save As Current Revision." If you change your
  mind, click "Cancel."
	</div>
</div>

