<?php use_helper('Url', 'jQuery', 'I18N', 'Date') ?>

<?php $n=0; foreach ($versions as $version => $data): ?>
<tr class="a-history-item" id="a-history-item-<?php echo $data['version'] ?>">
  <?php if (0): ?>
	  <td class="id">
		  <?php echo __('ID#', null, 'apostrophe') ?>
	  </td>
	<?php endif ?>
	<td class="date">
	  <?php // Localize the date. We used to do: "j M Y - g:iA" ?>
		<?php echo format_date(strtotime($data['created_at'])) ?>
	</td>
	<td class="editor">
		<?php echo $data['author'] ?>
	</td>
	<td class="preview">
		<?php echo $data['diff'] ?>
	</td>
</tr>
<?php $n++; endforeach ?>

<?php $n=0; foreach ($versions as $version => $data): ?>
<script type="text/javascript" charset="utf-8">
	$("#a-history-item-<?php echo $data['version'] ?>").data('params',
		{ 'preview': 
			{ 
	      id: <?php echo $id ?>, 
	      name: <?php echo json_encode($name) ?>, 
	      subaction: 'preview', 
	      version: <?php echo json_encode($version) ?>
	    },
			'revert':
			{
	      id: <?php echo $id ?>, 
	      name: <?php echo json_encode($name) ?>, 
	      subaction: 'revert', 
	      version: <?php echo json_encode($version) ?>
			},
			'cancel':
			{
	      id: <?php echo $id ?>, 
	      name: <?php echo json_encode($name) ?>, 
	      subaction: 'cancel', 
	      version: <?php echo json_encode($version) ?>
			}
		});
</script>
<?php $n++; endforeach ?>

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

<?php if(count($versions)  == 10 && is_null($all)): ?>
<script type="text/javascript">
  $(function() {
        $('.a-history-browser .a-history-browser-view-more').show();
  });
</script>
<?php else: ?>
<script type="text/javascript">
  $(function() {
        $('.a-history-browser .a-history-browser-view-more').hide();
  });
</script>
<?php endif ?>


<script type="text/javascript">
$(document).ready(function() {
	// Stuff to do as soon as the DOM is ready;
	$('.a-history-browser-view-more').mousedown(function(){
		$(this).children('img').fadeIn('fast');
	})

	$('.a-history-item').click(function() {

		$('.a-history-browser').hide();
		
	  var params = $(this).data('params');
	
		var targetArea = "#"+$(this).parent().attr('rel');					// this finds the associated area that the history browser is displaying
		var historyBtn = $(targetArea+ ' .a-area-controls a.a-history');				// this grabs the history button
		var cancelBtn = $(targetArea+ ' .a-area-controls a.a-cancel');					// this grabs the cancel button for this area
		var revertBtn = $(targetArea+ ' .a-area-controls a.a-history-revert');	// this grabs the history revert button for this area
		
		$(historyBtn).siblings('.a-history-options').show();

	  $.post( //User clicks to PREVIEW revision
	    <?php echo json_encode(url_for('a/revert')) ?>,
	    params.preview,
	    function(result)
	    {
				$('#a-slots-<?php echo "$id-$name" ?>').html(result);
				$(targetArea).addClass('previewing-history');
				// cancelBtn.parent().addClass('cancel-history');				
				$(targetArea+' .a-controls-item').siblings('.cancel, .history').css('display', 'block'); // turn off all controls initially				
				$(targetArea+' .a-controls-item.cancel').addClass('cancel-history');				
				$(targetArea+' .a-history-options').css('display','inline');
				$('.a-page-overlay').hide();
				aUI(targetArea,'history-preview');
	    }
	  );

		// Assign behaviors to the revert and cancel buttons when THIS history item is clicked
		
		revertBtn.click(function(){
		  $.post( // User clicks REVERT
		    <?php echo json_encode(url_for('a/revert')) ?>,
		    params.revert,
		    function(result)
		    {
					$('#a-slots-<?php echo "$id-$name" ?>').html(result);
					$('.a-history-options').hide();
					$(this).parents('.a-controls').find('a.a-cancel').parent().hide();
					aUI(targetArea, 'history-revert');
		  	}
			);	
		});
			
		cancelBtn.mouseup(function(){ // * 9/1/09 I Had to change this to MOUSEUP from CLICK because of a necessary unbind call in aUI applied to the cancel button. 
			// additional functionality added to the existing cancel button
		  $.post( // User clicks CANCEL
		    <?php echo json_encode(url_for('a/revert')) ?>,
		    params.cancel,
		    function(result)
		    {
		     $('#a-slots-<?php echo "$id-$name" ?>').html(result);
				 aUI(targetArea, 'history-cancel');
		  	}
			);
		});
							
	});

	$('.a-history-item').hover(function(){
		$(this).css('cursor','pointer');
	},function(){
		$(this).css('cursor','default');		
	})

	});
</script>

<?php
/*
<?php echo jq_form_remote_tag(
  array(
    'update' => "a-contents-$name",
    'url' => 'a/revert',
    'script' => true),
  array(
    "name" => "a-vc-form-$name", 
    "id" => "a-vc-form-$name")) ?>
<?php echo input_hidden_tag('id', $id)?>
<?php echo input_hidden_tag('name', $name)?>
<?php echo input_hidden_tag('subaction', '', array("id" => "a-vc-subaction-$name"))?>
<?php echo select_tag('version',
  options_for_select(
    $versions, $version), array("id" => "a-vc-$name-version")) ?>
<?php echo submit_tag("Preview", array(
  "name" => "preview", "class" => "submit", "id" => "a-preview-$name", "onClick" => "$('#a-vc-subaction-$name').val('preview'); return true")) ?>
<?php echo submit_tag("Revert", array(
  "name" => "revert", "class" => "submit", "id" => "a-revert-$name", "onClick" => "$('#a-vc-subaction-$name').val('revert'); return true")) ?>
<?php echo submit_tag("Cancel", array(
  "name" => "cancel", "class" => "submit", "id" => "a-cancel-$name", "onClick" => "$('#a-vc-subaction-$name').val('cancel'); return true")) ?>
</form>
	*/
?>

