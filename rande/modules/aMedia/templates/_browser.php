<?php use_helper('I18N') ?>
  
<?php // Entire media browser goes into what would otherwise be the regular apostrophe subnav ?>
<?php slot('a-subnav') ?>

<?php // Media is now an engine, so there's a page ?>
<?php $page = aTools::getCurrentPage() ?>

<?php // For backwards compatibility reasons it is best to implement these as before and after partials ?>
<?php // rather than a wrapper partial. If we use a wrapper that passes on each variable individually to an inner partial, ?>
<?php // it will break as new variables are added. If we had used a single $params array as the only variable ?>
<?php // in the first place, we could have avoided this, but we didn't, so let's be backwards compatible with all ?>
<?php // of the existing overrides of _browser in our sites and those of others. ?>

<?php include_partial('aMedia/browserBefore') ?>

<?php use_helper('Url') ?>

<div class="a-subnav-wrapper media">
	<div class="a-subnav-inner">

  	<h4><?php echo __('Find in Media', null, 'apostrophe') ?></h4>

	  <form method="POST" action="<?php echo url_for(aUrl::addParams($current, array("search" => false))) ?>" class="a-search-form media" id="a-search-form-sidebar">
	    <?php echo isset($search) ? link_to(__('Clear Search', null, 'apostrophe'), aUrl::addParams($current, array('search' => '')), array('id' => 'a-media-search-remove', 'title' => __('Clear Search', null, 'apostrophe'), )) : '' ?>
	    <?php echo $searchForm->renderHiddenFields() ?>
	    <?php echo $searchForm['search']->render() ?>
	    <input width="29" type="image" height="20" title="<?php echo __('Click to Search', null, 'apostrophe') ?>" alt="<?php echo __('Search', null, 'apostrophe') ?>" src="/apostrophePlugin/images/a-special-blank.gif" value="<?php echo __('Submit', null, 'apostrophe') ?>" class="a-search-submit submit" id="a-media-search-submit" />
	  </form>

		<div class='a-subnav-section types'>
	  	<h4><?php echo __('Media Types', null, 'apostrophe') ?></h4>

		  <ul class="a-filter-options type">
				<?php $type = isset($type) ? $type : '' ?>
				<li class="a-filter-option">
					<?php echo link_to(__('Image', null, 'apostrophe'), aUrl::addParams($current, array('type' => ($type == 'image') ? '' : 'image')), array('class' => ($type=='image') ? 'selected' : '', )) ?>
				</li>
				<li class="a-filter-option">
					<?php echo link_to(__('Video', null, 'apostrophe'), aUrl::addParams($current, array('type' => ($type == 'video') ? '' : 'video')), array('class' => ($type=='video') ? 'selected' : '', )) ?>				
				</li>
				<li class="a-filter-option">
					<?php echo link_to(__('PDF', null, 'apostrophe'), aUrl::addParams($current, array('type' => ($type == 'pdf') ? '' : 'pdf')), array('class' => ($type=='pdf') ? 'selected' : '', )) ?>
				</li>
		  </ul>
		</div>

    <?php // If an engine page is locked down to one category, don't show a category browser. ?>
    <?php // Also don't bother if all categories are empty ?>
    <?php $categoriesInfo = $page->getMediaCategoriesInfo() ?>

		<div class='a-subnav-section categories'>
			<div class="subnav-categories-header">

 		  <?php if (isset($selectedCategory)): ?>
 				<h5 class="a-category-sidebar-title selected-category">Selected Category</h5>  
 	    	<ul class="a-category-sidebar-selected-categories">
 	        <li class="selected">
 						<?php echo link_to(htmlspecialchars($selectedCategory->name), aUrl::addParams($current, array("category" => false)), array('class' => 'selected',)) ?>
 	        </li>
 	    	</ul>
			<?php endif ?>
				
   		<h4><?php echo __('Categories', null, 'apostrophe') ?></h4>

	    <?php if ($sf_user->hasCredential('media_admin')): ?>
	    	<?php // The editor for adding and removing categories FROM THE SYSTEM, ?>
	    	<?php // not an individual media item or engine page. ?>
	    	<?php // See the _editCategories partial ?>
	    	<?php echo jq_link_to_remote(__('Edit', null, 'apostrophe'), array(
					'url' => url_for('aMedia/editCategories'), 
					'update' => 'a-media-edit-categories'), array(
						'class' => 'a-btn icon a-edit no-label flag flag-right', 
						'id' => 'a-media-edit-categories-button',
					)) ?>
	    <?php endif ?>


			</div>

	    <?php if (!count($categoriesInfo)): ?>

			<span id="a-media-no-categories-message"><?php echo __('There are no categories that contain media.', null, 'apostrophe') ?></span>

			<?php else: ?>
				
	      <ul id="a-category-sidebar-list">
	        <?php foreach ($categoriesInfo as $categoryInfo): ?>
	  	       <li><a href="<?php echo url_for(aUrl::addParams($current, array("category" => $categoryInfo['slug']))) ?>"><span class="a-category-sidebar-category"><?php echo htmlspecialchars($categoryInfo['name']) ?></span> <span class="a-category-sidebar-category-count"><?php echo $categoryInfo['count'] ?></span></a></li>
	  	    <?php endforeach ?>
	      </ul>    
	    <?php endif ?>
    
    <?php if ($sf_user->hasCredential('media_admin')): ?>
    	<?php // AJAX goodness warps into our universe here ?>
      <div id="a-media-edit-categories"></div>
    <?php endif ?>
    
  </div>


		<div class='a-subnav-section tags'>

		 <?php if (isset($selectedTag)): ?>
				<h4 class="a-tag-sidebar-title selected-tag"><?php echo __('Selected Tag', null, 'apostrophe') ?></h4>  
	    	<ul class="a-tag-sidebar-selected-tags">
	        <li class="selected">
						<?php echo link_to(htmlspecialchars($selectedTag), aUrl::addParams($current, array("tag" => false)), array('class' => 'selected',)) ?>
	        </li>
	    	</ul>
      <?php endif ?>
   	
			<h4 class="a-tag-sidebar-title popular"><?php echo __('Popular Tags', null, 'apostrophe') ?></h4>
    	<ul class="a-tag-sidebar-list popular">
      	<?php foreach ($popularTags as $tag => $count): ?>
	        <li><a href="<?php echo url_for(aUrl::addParams($current, array("tag" => $tag))) ?>"><span class="a-tag-sidebar-tag"><?php echo htmlspecialchars($tag) ?></span> <span class="a-tag-sidebar-tag-count"><?php echo $count ?></span></a></li>
	      <?php endforeach ?>
    	</ul>

    	<h4 class="a-tag-sidebar-title all-tags"><?php echo __('All Tags', null, 'apostrophe') ?></h4>
	    <ul class="a-tag-sidebar-list all-tags">
	      <?php foreach ($allTags as $tag => $count): ?>
	        <li><a href="<?php echo url_for(aUrl::addParams($current, array("tag" => $tag))) ?>"><span class="a-tag-sidebar-tag"><?php echo htmlspecialchars($tag) ?></span> <span class="a-tag-sidebar-tag-count"><?php echo $count ?></span></a></li>
	      <?php endforeach ?>
	    </ul>

  	</div>

	</div>
</div>
   
<script type="text/javascript" charset="utf-8">

	aInputSelfLabel('#a-media-search', <?php echo json_encode(isset($search) ? $search : __('Search', null, 'apostrophe')) ?>);

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

<?php end_slot() ?>

