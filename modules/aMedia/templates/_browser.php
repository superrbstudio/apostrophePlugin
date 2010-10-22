<?php
  // Compatible with sf_escaping_strategy: true
  $allTags = isset($allTags) ? $sf_data->getRaw('allTags') : null;
  $current = isset($current) ? $sf_data->getRaw('current') : null;
  $params = isset($params) ? $sf_data->getRaw('params') : null;
  $popularTags = isset($popularTags) ? $sf_data->getRaw('popularTags') : null;
  $search = isset($search) ? $sf_data->getRaw('search') : null;
  $searchForm = isset($searchForm) ? $sf_data->getRaw('searchForm') : null;
  $selectedCategory = isset($selectedCategory) ? $sf_data->getRaw('selectedCategory') : null;
  $selectedTag = isset($selectedTag) ? $sf_data->getRaw('selectedTag') : null;
?>
<?php use_helper('a') ?>
<?php // Media is now an engine, so there's a page ?>
<?php $page = aTools::getCurrentPage() ?>
  
<?php // Entire media browser goes into what would otherwise be the regular apostrophe subnav ?>
<?php slot('a-subnav') ?>

<?php // For backwards compatibility reasons it is best to implement these as before and after partials ?>
<?php // rather than a wrapper partial. If we use a wrapper that passes on each variable individually to an inner partial, ?>
<?php // it will break as new variables are added. If we had used a single $params array as the only variable ?>
<?php // in the first place, we could have avoided this, but we didn't, so let's be backwards compatible with all ?>
<?php // of the existing overrides of _browser in our sites and those of others. ?>

<?php include_partial('aMedia/browserBefore') ?>				

<div class="a-subnav-wrapper a-admin-sidebar">
	<div class="a-subnav-inner">

  	<h4><?php echo __('Find in Media', null, 'apostrophe') ?></h4>

		<div id="a-search-media" class="a-search media">
		  <form method="GET" action="<?php echo url_for(aUrl::addParams($current, array("search" => false))) ?>" class="a-search-form media" id="a-search-form-sidebar">
				<div class="a-form-row a-hidden">
					<?php echo $searchForm->renderHiddenFields() ?>
				</div>
				<div class="a-form-row"> <?php // div is for page validation ?>
					<label for="a-search-cms-field" style="display:none;">Search</label><?php // label for accessibility ?>
	    		<?php echo $searchForm['search']->render(array('class' => 'a-search-field')) ?>					
					<?php if (isset($search)): ?>
				    <?php echo link_to(__('Clear Search', null, 'apostrophe'), aUrl::addParams($current, array('search' => '')), array('id' => 'a-media-search-remove', 'title' => __('Clear Search', null, 'apostrophe'), )) ?>						
					<?php else: ?>
						<input type="image" src="<?php echo image_path('/apostrophePlugin/images/a-special-blank.gif') ?>" class="submit a-search-submit" value="Search Pages" alt="Search" title="Search"/>
					<?php endif ?>
				</div>
		  </form>
		</div>

    <?php if (!aMediaTools::getType()): ?>
			<div class='a-subnav-section types'>
		  	<h4><?php echo __('Media Types', null, 'apostrophe') ?></h4>

			  <div class="a-filter-options type">
					<?php $type = isset($type) ? $type : '' ?>
			    <?php $typesInfo = aMediaTools::getOption('types') ?>
					<?php foreach ($typesInfo as $typeName => $typeInfo): ?>
	  				<div class="a-filter-option">
	  					<?php echo link_to(a_($typeInfo['label']), aUrl::addParams($current, array('type' => ($typeName == $type) ? '' : $typeName)), array('class' => ($typeName == $type) ? 'selected' : '', )) ?>
	  				</div>
	  			<?php endforeach ?>
			  </div>
			</div>
		<?php endif ?>		
		
		<hr />

    <?php // If an engine page is locked down to one category, don't show a category browser. ?>
    <?php // Also don't bother if all categories are empty ?>

    <?php $categoriesInfo = $page->getCategoriesInfo('aMediaItem') ?>
    <?php $categoriesInfo = $categoriesInfo['counts'] ?>
		<div class='a-subnav-section categories section'>

 		  <?php if (isset($selectedCategory)): ?>
 				<h5 class="a-category-sidebar-title selected-category"><?php echo __('Selected Category', null, 'apostrophe') ?></h5>  
 	    	<div class="a-category-sidebar-selected-categories">
 	        <div class="selected">
 						<?php echo link_to(htmlspecialchars($selectedCategory->name), aUrl::addParams($current, array("category" => false)), array('class' => 'selected',)) ?>
 	        </div>
 	    	</div>
			<?php endif ?>
				
   		<h4><?php echo __('Categories', null, 'apostrophe') ?></h4>
	    <?php if ($sf_user->hasCredential(aMediaTools::getOption('admin_credential'))): ?>
	    	<?php // The editor for adding and removing categories FROM THE SYSTEM, ?>
	    	<?php // not an individual media item or engine page. ?>
				<span class="a-ui">
	    	<?php echo link_to('<span class="icon"></span>'.__('edit categories', null, 'apostrophe'), 'aCategoryAdmin/index', array(
						'class' => 'edit-categories a-btn icon a-edit no-label lite', 
						'id' => 'a-media-edit-categories-button',
						'title' => 'Edit Categories', 
					)) ?>
				</span>
	    <?php endif ?>

	    <?php if (!count($categoriesInfo)): ?>

			<h5 id="a-media-no-categories-message" class="a-media-no-categories-message"><?php echo __('There are no categories that contain media.', null, 'apostrophe') ?></h5>

			<?php else: ?>
				
	      <ul class="a-ui a-category-sidebar-list" id="a-category-sidebar-list">
	        <?php $n=1; foreach ($categoriesInfo as $categoryInfo): ?>
		  			<li <?php echo ($n == count($categoriesInfo) ? 'class="last"':'') ?>>
								<span class="a-category-sidebar-category"><?php echo link_to($categoryInfo['name'], aUrl::addParams($current, array("category" => $categoryInfo['slug']))) ?></span>
								<span class="a-category-sidebar-category-count"><?php echo $categoryInfo['count'] ?></span>
						</li>
	  	    <?php $n++; endforeach ?>	
	      </ul>    
	    <?php endif ?>
    
    <?php if ($sf_user->hasCredential(aMediaTools::getOption('admin_credential'))): ?>
    	<?php // AJAX goodness warps into our universe here ?>
      <div id="a-media-edit-categories"></div>
    <?php endif ?>
    
  </div>

	<hr/>

		<div class='a-subnav-section section tags'>

		 <?php if (isset($selectedTag)): ?>
				<h4 class="a-tag-sidebar-title selected-tag"><?php echo __('Selected Tag', null, 'apostrophe') ?></h4>  
	    	<div class="a-tag-sidebar-selected-tags">
	        <div class="selected">
						<?php echo link_to(htmlspecialchars($selectedTag), aUrl::addParams($current, array("tag" => false)), array('class' => 'selected',)) ?>
	        </div>
	    	</div>
      <?php endif ?>
   	
			<h4 class="a-tag-sidebar-title popular"><?php echo __('Popular Tags', null, 'apostrophe') ?></h4>
    	<ul class="a-ui a-tag-sidebar-list popular">
      	<?php $n=1; foreach ($popularTags as $tag => $count): ?>
	  			<li <?php echo ($n == count($tag) ? 'class="last"':'') ?>>
						<span class="a-tag-sidebar-tag"><?php echo link_to($tag, aUrl::addParams($current, array("tag" => $tag))) ?></span>
						<span class="a-tag-sidebar-tag-count"><?php echo $count ?></span>
					</li>
	      <?php $n++; endforeach ?>
    	</ul>

    	<h4 class="a-tag-sidebar-title all-tags"><?php echo __('All Tags', null, 'apostrophe') ?></h4>
	    <ul class="a-ui a-tag-sidebar-list all-tags">
	      <?php $n=1; foreach ($allTags as $tag => $count): ?>
	  			<li <?php echo ($n == count($tag) ? 'class="last"':'') ?>>
						<span class="a-tag-sidebar-tag"><?php echo link_to($tag, aUrl::addParams($current, array("tag" => $tag))) ?></span>
						<span class="a-tag-sidebar-tag-count"><?php echo $count ?></span>
					</li>
	      <?php $n++; endforeach ?>
	    </ul>

  	</div>

	</div>
</div>
   
<script type="text/javascript" charset="utf-8">

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

<?php a_js_call('apostrophe.selfLabel(?)', array('selector' => '#a-media-search', 'title' => a_('Search'))) ?>