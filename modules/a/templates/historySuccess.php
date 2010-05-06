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

<script type="text/javascript" charset="utf-8">
	$(document).ready(function() {

		<?php if(count($versions)  == 10 && is_null($all)): ?>
				$('#a-history-browser-view-more').show();
		<?php else: ?>
				$('#a-history-browser-view-more').hide().before('&nbsp;');
		<?php endif ?>

		$('#a-history-browser-number-of-revisions').text('<?php echo count($versions) ?> Revisions');

		$('.a-history-browser-view-more').mousedown(function(){
			$(this).children('img').fadeIn('fast');
		});

		$('.a-history-item').click(function() {

			$('.a-history-browser').hide();
		
		  var params = $(this).data('params');
	
			var targetArea = "#"+$(this).parent().attr('rel');								<?php // this finds the associated area that the history browser is displaying ?>
			var historyBtn = $(targetArea+ ' .a-area-controls a.a-history');	<?php // this grabs the history button ?>
			var cancelBtn = $('#a-history-cancel-button');										<?php // this grabs the cancel button for this area ?>
			var revertBtn = $('#a-history-revert-button');										<?php // this grabs the history revert button for this area ?>
		
			$(historyBtn).siblings('.a-history-options').show();

		  $.post( //User clicks to PREVIEW revision
		    <?php echo json_encode(url_for('a/revert')) ?>,
		    params.preview,
		    function(result)
		    {
					$('#a-slots-<?php echo "$id-$name" ?>').html(result);
					$(targetArea).addClass('previewing-history');
					historyBtn.addClass('a-disabled');				
					$('.a-page-overlay').hide();
					aUI(targetArea);
		    }
		  );

			// Assign behaviors to the revert and cancel buttons when THIS history item is clicked
			revertBtn.click(function(){
			  $.post( // User clicks Save As Current Revision Button
			    <?php echo json_encode(url_for('a/revert')) ?>,
			    params.revert,
			    function(result)
			    {
						$('#a-slots-<?php echo "$id-$name" ?>').html(result);			
						historyBtn.removeClass('a-disabled');						
						aCloseHistory();
						aUI(targetArea, 'history-revert');
			  	}
				);	
			});
						
			cancelBtn.click(function(){ 
			  $.post( // User clicks CANCEL
			    <?php echo json_encode(url_for('a/revert')) ?>,
			    params.cancel,
			    function(result)
			    {
			     	$('#a-slots-<?php echo "$id-$name" ?>').html(result);
					 	historyBtn.removeClass('a-disabled');								
						aCloseHistory();
					 	aUI(targetArea);
			  	}
				);
			});
		});

		$('.a-history-item').hover(function(){
			$(this).css('cursor','pointer');
		},function(){
			$(this).css('cursor','default');		
		});

});
</script>