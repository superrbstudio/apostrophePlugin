<?php use_helper('I18N', 'jQuery') ?>

	<?php echo $form->render() ?>

<ul class="a-controls">
  <li><input type="submit" value="<?php echo __('Go', null, 'apostrophe') ?>" class="a-submit" /></li>
	<li>
		<?php echo link_to_function(__("Cancel", null, 'apostrophe'), 
			"$('#a-media-video-search-form').hide(); 
			 $('#a-media-video-search-results-container').hide(); 
			 $('#a-media-video-search-heading').hide(); 
			 $('#a-media-video-buttons').show();", 
			array("class" => "a-btn a-cancel")) ?>
	</li>
</ul>

<script type="text/javascript" charset="utf-8">
	var aMediaVideoSearchResults = null;
</script>

<?php if ($results !== false): ?>
  <?php if (!count($results)): ?>
    <p><?php echo __('No matching videos were found. Try being less specific.', null, 'apostrophe') ?></p>
  <?php else: ?>

    <ul id="a-media-video-search-results"></ul>
    <br class="clear" />

    <div id="a-media-video-search-pagination" class="a-pager-navigation"></div>
    <br class="clear" />

		<script type="text/javascript" charset="utf-8">
		  var aMediaVideoSearchResults = <?php echo json_encode($results) ?>;
		  var aMediaVideoSearchPage = 1;
		</script>

  <?php endif ?>
<?php endif ?>

<script type="text/javascript" charset="utf-8">
function aMediaVideoSearchRenderResults()
{
  if (!aMediaVideoSearchResults)
  {
    return;
  }
  var perPage = <?php echo aMediaTools::getOption('video_search_per_page') ?>;
  var start = (aMediaVideoSearchPage - 1) * perPage;
  var template = <?php echo json_encode(aYoutube::embed('_ID_', aMediaTools::getOption('video_search_preview_width'), aMediaTools::getOption('video_search_preview_height'))) ?>;
  var i;
  var limit = start + perPage;
  var total = aMediaVideoSearchResults.length;
  var pages = Math.ceil(total / perPage);
  if (limit > total)
  {
    limit = total;
  }
  $('#a-media-video-search-results').html('');
  for (i = start; (i < limit); i++)
  {
		li_class = "normal";

		if (i%3 == 2)
		{
			li_class = "right-side";
		}
    var result = aMediaVideoSearchResults[i];
    var id = result.id;
    var embed = template.replace(/_ID_/g, id);
    var li = $("<li class='"+li_class+" video-"+i+"'><a href='#' class='a-media-search-select a-btn'><?php echo __('Select%buttonspan%', array('%buttonspan%' => '<span></span>'), 'apostrophe') ?></a><br class='clear c'/>" + embed + "</li>");
    var a = li.find('a:first');
    a.data('videoInfo', result);
    a.click(function() {
      $('#a-media-video-search-results').hide();
      $('#a-media-video-search-pagination').hide();
      aMediaVideoSelected($(this).data('videoInfo'));
    });
    $('#a-media-video-search-results').append(li);
  }
  if (pages > 1)
  {
    $('#a-media-video-search-pagination').html('');
    if (pages == 0)
    {
      pages = 1;
    }
    for (i = 1; (i <= pages); i++)
    {
      var item;
      if (i === aMediaVideoSearchPage)
      {
        item = $('<span class="a-page-navigation-number a-pager-navigation-disabled">' + i + '</span>');
      }
      else
      {
        item = $('<span class="a-page-navigation-number"><a href="#">' + i + '</a></span>');
      }  
      item.data('page', i);
      item.click(function() { 
        aMediaVideoSearchPage = $(this).data('page');
        aMediaVideoSearchRenderResults(); 
      });
      $('#a-media-video-search-pagination').append(item);
    }
  }
}

aMediaVideoSearchRenderResults();

function aMediaVideoSelected(videoInfo)
{
  <?php // TODO: find a cleaner way to implement the CSRF here. ?>
  document.location = <?php echo json_encode(url_for("aMedia/editVideo")) ?> + "?first_pass=1&a_media_item[title]=" + escape(videoInfo['title']) + "&a_media_item[_csrf_token]=<?php echo md5(sfConfig::get('sf_csrf_secret') . session_id()) ?>&a_media_item[service_url]=http://www.youtube.com/watch?v=" + escape(videoInfo['id']);
}

aUI();

// $(document).ready(function(){
// 	$('#videoSearch_q').css({
// 		'float':'left',
// 		'width':'auto',
// 	})
// });
</script>
