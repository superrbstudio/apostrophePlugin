<?php
  // Compatible with sf_escaping_strategy: true
  $all = isset($all) ? $sf_data->getRaw('all') : null;
  $id = isset($id) ? $sf_data->getRaw('id') : null;
  $n = isset($n) ? $sf_data->getRaw('n') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $versions = isset($versions) ? $sf_data->getRaw('versions') : null;
?>
<?php use_helper('a', 'Date') ?>

<?php $n=0; foreach ($versions as $data): ?>
<tr class="a-history-item" id="a-history-item-<?php echo $data['version'] ?>">
  <?php if (0): ?>
	  <td class="id">
		  <?php echo __('ID#', null, 'apostrophe') ?>
	  </td>
	<?php endif ?>
	<td class="date">
	  <?php // Localize the date. We used to do: "j M Y - g:iA" ?>
	  <?php // Avoid a crash in some versions of PHP when date columns ?>
	  <?php // are null or all zeroes ?>
	  <?php if ($data['created_at'] > '0000-00-00 00:00:00'): ?>
		  <?php echo aDate::pretty($data['created_at']) . ' ' . aDate::time($data['created_at']) ?>
		<?php endif ?>
	</td>
	<td class="editor">
		<?php echo $data['author'] ?>
	</td>
	<td class="preview">
		<?php echo $data['diff'] ?>
	</td>
</tr>
<?php $n++; endforeach ?>

<?php a_js_call('apostrophe.historyOpen(?)', array('id' => $id, 'name' => $name, 'versionsInfo' => $versions, 'all' => $all, 'revert' => a_url('a', 'revert'), 'revisionsLabel' => a_(' Revisions'))) ?>

<?php if (count($versions) == 0): ?>
	<tr class="a-history-item">
		<td class="id">
		</td>
		<td class="date">
			<?php echo __('No history found.', null, 'apostrophe') ?>
		</td>
		<td class="editor">
		</td>
		<td class="preview">
		</td>
	</tr>
<?php endif ?>

<?php include_partial('a/globalJavascripts') ?>
