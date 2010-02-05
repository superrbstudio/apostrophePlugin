<?php // For backwards compatibility reasons it is best to implement these as before and after partials ?>
<?php // rather than a wrapper partial. If we use a wrapper that passes on each variable individually to an inner partial, ?>
<?php // it will break as new variables are added. If we had used a single $params array as the only variable ?>
<?php // in the first place, we could have avoided this, but we didn't, so let's be backwards compatible with all ?>
<?php // of the existing overrides of _browser in our sites and those of others. ?>

<?php include_partial('aMedia/browserBefore') ?>

<?php use_helper('Url') ?>

<div id="a-subnav" class="media">
	<div class="a-subnav-wrapper">
		
  	<h3>Find in Media</h3>

	  <form method="POST" action="<?php echo url_for(aUrl::addParams($current, array("search" => false))) ?>" class="a-search-form media" id="a-search-form-sidebar">
	    <?php echo isset($search) ? link_to('Clear Search', aUrl::addParams($current, array('search' => '')), array('id' => 'a-media-search-remove', 'title' => 'Clear Search', )) : '' ?>
	    <?php echo $searchForm['search']->render() ?>
	    <input width="29" type="image" height="20" title="Click to Search" alt="Search" src="/apostrophePlugin/images/a-special-blank.gif" value="Submit" class="a-search-submit submit" id="a-media-search-submit" />
	  </form>

		<div class="a-media-filters">

	  	<h3>Media Types</h3>

		  <ul class="a-media-filter-options">
				<?php $type = isset($type) ? $type : '' ?>
				<li class="a-media-filter-option">
					<?php echo link_to('Image', aUrl::addParams($current, array('type' => ($type == 'image') ? '' : 'image')), array('class' => ($type=='image') ? 'selected' : '', )) ?>
				</li>
				<li class="a-media-filter-option">
					<?php echo link_to('Video', aUrl::addParams($current, array('type' => ($type == 'video') ? '' : 'video')), array('class' => ($type=='video') ? 'selected' : '', )) ?>				
				</li>
				<li class="a-media-filter-option">
					<?php echo link_to('PDF', aUrl::addParams($current, array('type' => ($type == 'pdf') ? '' : 'pdf')), array('class' => ($type=='pdf') ? 'selected' : '', )) ?>
				</li>
		  </ul>

			<div class="a-tag-sidebar">

			 <?php if (isset($selectedTag)): ?>
					<h4 class="a-tag-sidebar-title selected-tag">Selected Tag</h4>  
		    	<ul class="a-tag-sidebar-selected-tags">
		        <li class="selected">
							<?php echo link_to(htmlspecialchars($selectedTag), aUrl::addParams($current, array("tag" => false)), array('class' => 'selected',)) ?>
		        </li>
		    	</ul>
	      <?php endif ?>
    	
				<h3 class="a-tag-sidebar-title popular">Popular Tags</h3>
	    	<ul class="a-tag-sidebar-list popular">
	      	<?php foreach ($popularTags as $tag => $count): ?>
		        <li><a href="<?php echo url_for(aUrl::addParams($current, array("tag" => $tag))) ?>"><span class="a-tag-sidebar-tag"><?php echo htmlspecialchars($tag) ?></span> <span class="a-tag-sidebar-tag-count"><?php echo $count ?></span></a></li>
		      <?php endforeach ?>
	    	</ul>

	    	<h3 class="a-tag-sidebar-title all-tags">All Tags</h3>
		    <ul class="a-tag-sidebar-list all-tags">
		      <?php foreach ($allTags as $tag => $count): ?>
		        <li><a href="<?php echo url_for(aUrl::addParams($current, array("tag" => $tag))) ?>"><span class="a-tag-sidebar-tag"><?php echo htmlspecialchars($tag) ?></span> <span class="a-tag-sidebar-tag-count"><?php echo $count ?></span></a></li>
		      <?php endforeach ?>
		    </ul>

	  	</div>

 		</div>

	</div>
</div>
   
<script type="text/javascript" charset="utf-8">

	aInputSelfLabel('#a-media-search', <?php echo json_encode(isset($search) ? $search : 'Search') ?>);

  <?php if (isset($search)): ?>
    $('#a-media-search-remove').show();
    $('#a-media-search-submit').hide();
    var search = <?php echo json_encode($search) ?>;
    $('#a-media-search').bind("keyup blur", function(e) 
    {
      if ($(this).val() === search)
      {
        $('#a-media-search-remove').show();
        $('#a-media-search-submit').hide();
      }
      else
      {
        $('#a-media-search-remove').hide();
        $('#a-media-search-submit').show();
      }
    });

    $('#a-media-search').bind('aInputSelfLabelClear', function(e) {
      $('#a-media-search-remove').show();
      $('#a-media-search-submit').hide();
    });
  <?php endif ?>
  
	var allTags = $('.a-tag-sidebar-title.all-tags');

	allTags.hover(function(){
		allTags.addClass('over');
	},function(){
		allTags.removeClass('over');		
	});
	
	allTags.click(function(){
		allTags.toggleClass('open');
		allTags.next().toggle();
	})
	
</script>

<?php include_partial('aMedia/browserAfter') ?>
