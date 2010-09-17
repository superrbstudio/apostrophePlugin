function aConstructor() 
{
  this.onSubmitHandlers = new Object();

  this.registerOnSubmit = function (slotId, callback) 
  {
    if (!this.onSubmitHandlers[slotId])
    {
      this.onSubmitHandlers[slotId] = [ callback ];
      return;
    }
    this.onSubmitHandlers[slotId].push(callback);
  };

  this.callOnSubmit = function (slotId)
  {
    handlers = this.onSubmitHandlers[slotId];
    if (!handlers)
    {
      return;
    }
    for (i = 0; (i < handlers.length); i++)
    {
      handlers[i](slotId);
    }
  }

	// Swap two DOM elements without cloning them
	// http://blog.pengoworks.com/index.cfm/2008/9/24/A-quick-and-dirty-swap-method-for-jQuery
	this.swapNodes = function(a, b) {
    var t = a.parentNode.insertBefore(document.createTextNode(''), a); 
    b.parentNode.insertBefore(a, b); 
    t.parentNode.insertBefore(b, t); 
    t.parentNode.removeChild(t);
	}	
	
	this.jsTree = function(options)
	{
		var treeData = options['treeData'];
		var moveURL = options['moveUrl'];
		var aPageTree = $('#a-page-tree');
		
		aPageTree.tree({
	    data: {
	      type: 'json',
	      // Supports multiple roots so we have to specify a list
	      json: [ treeData ]
	    },
			ui: {
				theme_path: "/apostrophePlugin/js/jsTree/source/themes/",
	      theme_name: "punk",
				context: false
			},
	    rules: {
	      // Turn off most operations as we're only here to reorg the tree.
	      // Allowing renames and deletes here is an interesting thought but
	      // there's back end stuff that must exist for that.
	      renameable: false,
	      deletable: false,
	      creatable: false,
	      draggable: 'all',
	      dragrules: 'all'
	    },
	    callback: {
	      // move completed (TYPE is BELOW|ABOVE|INSIDE)
	      onmove: function(node, refNode, type, treeObj, rb)
	      {
	        // To avoid creating an inconsistent tree we need to use
	        // a synchronous request. If the request fails, refresh the
	        // tree page (TODO: find out if there's some way to flunk an 
	        // individual drag operation). This shouldn't happen anyway
	        // but don't get into an inconsistent state if it does!

					aPageTree.parent().addClass('working');
					
	        var nid = node.id;
	        var rid = refNode.id;
					
	        jQuery.ajax({
	          url: options['moveURL'] + "?" + "id=" + nid.substr("tree-".length) + "&refId=" + rid.substr("tree-".length) + "&type=" + type,
	          error: function(result) {
							// 404 errors etc
	            window.location.reload();
	          },
	          success: function(result) {
							// Look for a specific "all is well" response
	            if (result !== 'ok')
	            {
	              window.location.reload();
	            }
							aPageTree.parent().removeClass('working');
	          },
	          async: false
	        });
	      }
	    }  
	  });
	}

	this.slideshow = function(options)
	{
	  var id = options['id'];
	  var intervalEnabled = !!options['interval'];
	  var intervalSetting = options['interval'];
	  var positionFlag = options['position'];
	  var title = options['title'];

		var slideshowItems = $('#a-slideshow-' + id + ' .a-slideshow-item');
		var img_count = slideshowItems.length;
    if (img_count === 1)
    {
      $('#a-slideshow-item-' + id + '-0').show().parents(".a-slideshow, .aSlideshow").addClass("single-image");
    }
    else
    {
      // Clear any interval timer left running by a previous slot variant
      if (window.aSlideshowIntervalTimeouts !== undefined)
      {
        if (window.aSlideshowIntervalTimeouts['a-' + id])
        {
          clearTimeout(window.aSlideshowIntervalTimeouts['a-' + id]);
        } 
      }
      else
      {
        window.aSlideshowIntervalTimeouts = {};
      }

  		var position = 0;
  		$('.a-context-media-show-item').hide();
  		$('#a-slideshow-item-' + id + '-' + position).show();

  		if (positionFlag)
  		{
    		var positionHead = $('#a-slideshow-controls-' + id + ' li.a-slideshow-position span.head');
    		setHead(position);
  		}
		
  		function setHead(current_position)
  		{
  			positionHead.text(current_position + 1);
  		}
		
  		slideshowItems.attr('title', title);
	
  		$('#a-slideshow-' + id).bind('showImage', function(e, num){
  			position = num;
  			slideshowItems.hide();
  			$('#a-slideshow-item-' + id + '-' + position).fadeIn('slow');
  		});
		
  	  slideshowItems.find('.a-slideshow-image').click(function(event) {
  			event.preventDefault();
  			next();
  	  });

  		$('#a-slideshow-controls-' + id + ' .a-arrow-left').click(function(event){
  			event.preventDefault();
  			intervalEnabled = false;
  			previous();
  		});

  		$('#a-slideshow-controls-' + id + ' .a-arrow-right').click(function(event){
  			event.preventDefault();
  			intervalEnabled = false;
  			next();
  		});

  		$('.a-slideshow-controls li').hover(function(){
  			$(this).addClass('over');	
  		},function(){
  			$(this).removeClass('over');
  		});

  	  function previous() 
  	  {
  		  var oldItem = $('#a-slideshow-item-' + id + '-' + position);

				position--;
				if ( position < 0 ) 
				{ 
				  position = img_count - 1; 
				}

				var newItem = $('#a-slideshow-item-' + id + '-' + position);
				newItem.parents('.a-slideshow').css('height',newItem.height());
				newItem.fadeIn('slow');			
				oldItem.hide();
				if (positionFlag)
				{
				  setHead(position);				
				}
  			interval();
  	  }
 
  	  function next()
  	  {
    	  var oldItem = $('#a-slideshow-item-' + id + '-'+position);

	  		position++;
	  		if ( position == img_count) 
	  		{ 
	  		  position = 0; 
	  		}

				var newItem = $('#a-slideshow-item-' + id + '-' + position);
				newItem.parents('.a-slideshow').css('height',newItem.height());
	  		newItem.fadeIn('slow');			
	  		oldItem.hide();
	  		if (positionFlag)
	  		{
  				setHead(position);
  			}
  	  	interval();
  	  }
	  
  		var intervalTimeout = null;
  	  function interval()
  	  {
  	    if (intervalTimeout)
  	    {
  	      clearTimeout(intervalTimeout);
  	    }
  	    if (intervalEnabled)
  	    {
  	  	  intervalTimeout = setTimeout(next, intervalSetting * 1000);
  	  	  window.aSlideshowIntervalTimeouts['a-' + id] = intervalTimeout;
  	  	}
  	  }
  	  interval();
	  }
	};
	
	this.selfLabel = function(options)
	{
		aInputSelfLabel(options['selector'], options['title']);
	};
	
	this.aClickOnce = function(options)
	{
		var selector = $(options['selector']);
		selector.unbind('click').click(function(){   
 			selector.replaceWith("<span class='" + selector.attr('class') + "' id='"+selector.attr('id')+"'>" + selector.text() + "</span>");	
		});
	}
	
	this.aClickOnce_old = function(options)
	{
		// For some reason, this didn't work as a single click event. 
		// Nesting the click event was the only way to get this to work properly.		
		var selector = $(options['selector']);
		selector.data('clicked',0); // Using .data() to keep track of the click
		selector.unbind('click').click(function(){
			if (!selector.data('clicked')) { // Is this is the first click ?
				selector.unbind('click').click(function(event){ // Unbind the click event and reset it to preventDefault()
					event.preventDefault();
				});
			selector.data('clicked',1);	// No longer the first click
			}
		});
	}
	
	this.afterAddingSlot = function(name)
	{
		$('#a-add-slot-form-' + name).hide();
	}
	
	this.historyOpen = function(options)
	{
		var id = options['id'];
		var name = options['name'];
		var versionsInfo = options['versionsInfo'];
		var all = options['all'];
		var revert = options['revert'];
		var revisionsLabel = options['revisionsLabel'];
	  for (i = 0; (i < versionsInfo.length); i++)
		{
			version = versionsInfo[i].version;
	  	$("#a-history-item-" + version).data('params',
	  		{ 'preview': 
	  			{ 
	  	      id: id,
	  	      name: name,
	  	      subaction: 'preview', 
	  	      version: version
	  	    },
	  			'revert':
	  			{
	  	      id: id,
	  	      name: name,
	  	      subaction: 'revert', 
	  	      version: version
	  			},
	  			'cancel':
	  			{
	  	      id: id,
	  	      name: name,
	  	      subaction: 'cancel', 
	  	      version: version
	  			}
	  		});
	  }
		if ((versionsInfo.length == 10) && (!all))
		{
			$('#a-history-browser-view-more').show();
		}
		else
		{
			$('#a-history-browser-view-more').hide().before('&nbsp;');
		}

		$('#a-history-browser-number-of-revisions').text(versionsInfo.length + revisionsLabel);

		$('.a-history-browser-view-more').mousedown(function(){
			$(this).children('img').fadeIn('fast');
		});

		$('.a-history-item').click(function() {

			$('.a-history-browser').hide();

		  var params = $(this).data('params');

			var targetArea = "#"+$(this).parent().attr('rel');								// this finds the associated area that the history browser is displaying
			var historyBtn = $(targetArea+ ' .a-area-controls a.a-history');	// this grabs the history button
			var cancelBtn = $('#a-history-cancel-button');										// this grabs the cancel button for this area 
			var revertBtn = $('#a-history-revert-button');										// this grabs the history revert button for this area 

			$(historyBtn).siblings('.a-history-options').show();

		  $.post( //User clicks to PREVIEW revision
		    revert,
		    params.preview,
		    function(result)
		    {
					$('#a-slots-' + id + '-' + name).html(result);
					$(targetArea).addClass('previewing-history');
					historyBtn.addClass('a-disabled');				
					$('.a-page-overlay').hide();
					aUI(targetArea);
		    }
		  );

			// Assign behaviors to the revert and cancel buttons when THIS history item is clicked
			revertBtn.click(function(){
			  $.post( // User clicks Save As Current Revision Button
			    revert,
			    params.revert,
			    function(result)
			    {
						$('#a-slots-' + id + '-' + name).html(result);			
						historyBtn.removeClass('a-disabled');						
						_closeHistory();
						aUI(targetArea, 'history-revert');
			  	}
				);	
			});

			cancelBtn.click(function(){ 
			  $.post( // User clicks CANCEL
			    revert,
			    params.cancel,
			    function(result)
			    {
			     	$('#a-slots-' + id + '-' + name).html(result);
					 	historyBtn.removeClass('a-disabled');								
						_closeHistory();
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
	}

	this.enableBrowseHistoryButtons = function(options)
	{
		var historyBtn = $(options['history_buttons']);
		historyBtn.unbind("click").click(function(event){
			event.preventDefault();			
			_closeHistory();
			_browseHistory(historyBtn.closest('div.a-area'));		
		});
	}
	
	this.enableCloseHistoryButtons = function(options)
	{
		var closeHistoryBtns = $(options['close_history_buttons']);
		closeHistoryBtns.click(function(){
			_closeHistory();
		});
	}
	
	function _browseHistory(area)
	{
		var areaControls = area.find('ul.a-area-controls');
		var areaControlsTop = areaControls.offset().top;
	
		$('.a-page-overlay').show();
	
		// Clear Old History from the Browser
		if (!area.hasClass('browsing-history')) 
		{
			$('.a-history-browser .a-history-items').html('<tr class="a-history-item"><td class="date"><img src="\/apostrophePlugin\/images\/a-icon-loader.gif"><\/td><td class="editor"><\/td><td class="preview"><\/td><\/tr>');
			area.addClass('browsing-history');
		}
	
		// Positioning the History Browser
		$('.a-history-browser').css('top',(areaControlsTop-5)+"px"); //21 = height of buttons plus one margin
		$('.a-history-browser').fadeIn();
		$('.a-page-overlay').click(function(){
			_closeHistory();
			$(this).unbind('click');
		});
	
		$('#a-history-preview-notice-toggle').click(function(){
			$('.a-history-preview-notice').children(':not(".a-history-options")').slideUp();
		});
	}

	function _closeHistory()
	{
		$('a.a-history-btn').parents('.a-area').removeClass('browsing-history');
		$('a.a-history-btn').parents('.a-area').removeClass('previewing-history');
		$('.a-history-browser, .a-history-preview-notice').hide();
	  $('body').removeClass('history-preview');	
		$('.a-page-overlay').fadeOut();
	}

	this.enablePageSettingsButtons = function(options)
	{
		var aPageSettingsURL = options['aPageSettingsURL'];
		var aPageSettingsCreateURL = options['aPageSettingsCreateURL'];

		apostrophe.menuToggle({"button":"#a-page-settings-button","classname":"","overlay":true, 
			"beforeOpen": function() {
				$.ajax({
						type:'POST',
						dataType:'html',
						success:function(data, textStatus){
							$('#a-page-settings').html(data);
						},
						complete:function(XMLHttpRequest, textStatus){
							aUI('#a-page-settings');
						},
						url: aPageSettingsURL
				});	
			},
			"afterClosed": function() {
				$('#a-page-settings').html('');
			}
		});
		apostrophe.menuToggle({"button":"#a-create-page-button","classname":"","overlay":true, 
			"beforeOpen": function() {
				$.ajax({
						type:'POST',
						dataType:'html',
						success:function(data, textStatus){
							$('#a-create-page').html(data);
						},
						complete:function(XMLHttpRequest, textStatus){
							aUI('#a-create-page');
						},
						url: aPageSettingsCreateURL
				});	
			},
			"afterClosed": function() {
				$('#a-create-page').html('');
			}
		});
	}
	
	this.mediaCategories = function(options) 
	{	
		var newCategoryLabel = options['newCategoryLabel'];	
		apostrophe.selfLabel('#a_media_category_name', newCategoryLabel);	
		$('#a-media-edit-categories-button, #a-media-no-categories-messagem, #a-category-sidebar-list').hide();
		$('#a_media_category_description').parents('div.a-form-row').addClass('hide-description').parent().attr('id','a-media-category-form');
		$('.a-remote-submit').aRemoteSubmit('#a-media-edit-categories');
	}
	
	this.mediaEnableRemoveButton = function(i)
	{
		var editor = $('#a-media-item-' + i);
		editor.find('.a-media-delete-image-btn').click(function()
		{
			editor.remove();
			if ($('.a-media-item').length == 0)
			{
				// This is a bit hacky
				document.location = $('.a-media-edit-multiple-cancel').attr('href');
			}
			return false;
		});
	}
	
	// console.log wrapper prevents JS errors if we leave an apostrophe.log call hanging out in our code someplace
	this.log = function(output)
	{ 
		if (window.console && console.log) {
			console.log(output);
		};
	}
	
	this.slotShowEditView = function(pageid, name, permid)
	{	
		var fullId = pageid + '-' + name + '-' + permid;
 		var editSlot = $('#a-slot-' + fullId);
	  if (!editSlot.children('.a-slot-content').children('.a-slot-form').length)
	  {
 		  $.get(editSlot.data('a-edit-url'), { id: pageid, slot: name, permid: permid }, function(data) { 
	      editSlot.children('.a-slot-content').html(data);
	      slotShowEditViewPreloaded(pageid, name, permid);
	    });
	  }
	  else
	  {
	    // Reuse edit view
      slotShowEditViewPreloaded(pageid, name, permid);
	  }
	}
	
	this.areaUpdateMoveButtons = function(updateAction, id, name)
	{
		var area = $('#a-area-' + id + '-' + name);
		// Be precise - take care not to hoover up controls related to slots in nested areas, if there are any
		var slots = area.children('.a-slots').children('.a-slot');
		var newSlots = area.children('.a-slots').children('.a-new-slot');
		if (newSlots.length)
		{
			// TODO: this is not sensitive enough to nested areas
			
			// You have to save a new slot before you can do any reordering.
			// TODO: with a little more finesse we could support saving it with
			// a rank, but think about how messy that might get
		  slots.find('.a-slot-controls .a-move').hide();
			return;
		}
		// I actually want a visible loop variable here
		for (n = 0; (n < slots.length); n++)
		{
			var slot = slots[n];
			// We use a nested function here because 
			// a loop variable does *not* get captured
			// in the closure at its current value otherwise
			slotUpdateMoveButtons(id, name, slot, n, slots, updateAction);
		}
	}
	
	this.slotNotNew = function(pageid, name, permid)
	{
		$("#a-slot-" + pageid + "-" + name + "-" + permid).removeClass('a-new-slot');
	}
	
	this.slotEnableEditButton = function(pageid, name, permid, editUrl)
	{
		var fullId = pageid + '-' + name + '-' + permid;
 		var editBtn = $('#a-slot-edit-' + fullId);
 		var editSlot = $('#a-slot-' + fullId);
		editSlot.data('a-edit-url', editUrl);
 		editBtn.click(function(event) {
			apostrophe.slotShowEditView(pageid, name, permid);
 		  return false;
 		});
  }

	this.menuToggle = function(options)
	{		
		var button = options['button'];
		var menu;
		if (typeof(options[menu]) != "undefined")
		{
			menu = options[menu];
		}
		else
		{
			menu = $(button).parent();
		}
		var classname = options['classname'];
		var overlay = options['overlay'];

		if (typeof(button) == "undefined") {
			apostrophe.log('[Apostrophe] menuToggle button is undefined');
		}
		else
		{
			if (typeof button == "string") { button = $(button); } /* button that toggles the menu open & closed */
			if (typeof classname == "undefined" || classname == '') { classname = "show-options";	} /* optional classname override to use for toggle & styling */
			if (typeof overlay != "undefined" && overlay) { overlay = $('.a-page-overlay'); } /* optional full overlay */ 

			if (typeof(menu) == "object") {
				_menuToggle(button, menu, classname, overlay, options['beforeOpen'], options['afterClosed']);			
			};	
		};
	}

	this.IE6 = function(options)
	{
		var authenticated = options['authenticated'];
		var message = options['message'];
		// This is called within a conditional comment for IE6 in Apostrophe's layout.php
		if (authenticated)
		{
			$(document.body).addClass('ie6').prepend('<div id="ie6-warning"><h2>' + message + '</h2></div>');	
		}
	}
	
		/* Example Mark-up
		<script>
			apostrophe.accordion({'accordion_toggle': '.a-accordion-item h3' });
		</script> 

		BEFORE:
		<div>
			<h3>Heading</h3>
			<div>Content</div>
		</div>

		AFTER:
		<div class="a-accordion">
			<h3 class="a-accordion-toggle">Heading</h3>
			<div class="a-accordion-content">Content</div>
		</div>
		*/
	
	this.accordion = function(options)
	{
		var toggle = options['accordion_toggle'];
		
		if (typeof toggle == "undefined") {
			apostrophe.log('[Apostrophe] Accordion Toggle is undefined.');
		}
		else
		{
			if (typeof toggle == "string") { toggle = $(toggle); }

			var container = toggle.parent();
			var content = toggle.next();

			container.addClass('a-accordion');
			content.addClass('a-accordion-content');

			toggle.each(function() {
				var t = $(this);
				t.click(function(event){
					event.preventDefault();					
					t.closest('.a-accordion').toggleClass('open');
				})
				.hover(function(){
					t.addClass('hover');
				},function(){
					t.removeClass('hover');
				});			
			}).addClass('a-accordion-toggle');
		};
	}
	
	this.mediaItemsIndicateSelected = function(cropOptions)
	{
	  var ids = cropOptions.ids;
	  aCrop.init(cropOptions);
		$('.a-media-selected-overlay').remove();		
		$('.a-media-selected').removeClass('a-media-selected');
		
	  var i;
	  for (i = 0; (i < ids.length); i++)
	  {
	    id = ids[i];
	    var selector = '#a-media-item-' + id;
	    if (!$(selector).hasClass('a-media-selected')) 
	    {
	      $(selector).addClass('a-media-selected');
			}
		}
			
		$('.a-media-item.a-media-selected').each(function(){
			$(this).children('.a-media-item-thumbnail').prepend('<div class="a-media-selected-overlay"></div>');
		});
		
		$('.a-media-selection-help').hide();
		if (!ids.length) {
      $('.a-media-selection-help').show();
		}

	 	$('.a-media-selected-overlay').fadeTo(0, 0.66);
	}
	
	this.mediaUpdatePreview = function()
	{
	  $('#a-media-selection-preview').load(apostrophe.selectOptions.updateMultiplePreviewUrl, function(){
  	  // the preview images are by default set to display:none
	    $('#a-media-selection-preview li:first').addClass('current');
	    // set up cropping again; do hard reset to reinstantiate Jcrop
	    aCrop.resetCrop(true);
			// Selection may have changed
			apostrophe.mediaItemsIndicateSelected(apostrophe.selectOptions);
	  });
	}

	this.mediaDeselectItem = function(id)
	{
		$('#a-media-item-'+id).removeClass('a-media-selected');
		$('#a-media-item-'+id).children('.a-media-selected-overlay').remove();
	}

	$('.a-media-thumb-link').click(function(){
		$(this).addClass('a-media-selected');
	});

	this.mediaEnableSelect = function(options)
	{
		apostrophe.selectOptions = options;
		// Binding it this way avoids a cascade of two click events when someone
		// clicks on one of the buttons hovering on this
		
		// I had to bind to all of these to guarantee a click would come through
	  $('.a-media-selection-list-item .a-delete').click(function(e) {
			var p = $(this).parents('.a-media-selection-list-item');
			var id = p.data('id');
			$.get(options['removeUrl'], { id: id }, function(data) {
				$('#a-media-selection-list').html(data);
				aUI('a-media-selection-list');
				apostrophe.mediaDeselectItem(id);
				apostrophe.mediaUpdatePreview();
			});
			return false;
		});

		apostrophe.mediaItemsIndicateSelected(options);
		
		$('.a-media-selected-item-overlay').fadeTo(0,.35); //cross-browser opacity for overlay
		$('.a-media-selection-list-item').hover(function(){
			$(this).addClass('over');
		},function(){
			$(this).removeClass('over');			
		});
	
		$('.a-media-thumb-link').click(function() {
			$.get(options['multipleAddUrl'], { id: $(this).data('id') }, function(data) {
				$('#a-media-selection-list').html(data);
				aUI('#a-media-selection-list');
				apostrophe.mediaUpdatePreview();
			});
			return false;
		});
	}

	// Often JS code relating to an object needs to be able to find the
	// database id of that object as a property of some enclosing 
	// DOM object, like an li or div representing a particular media item.
	// This method makes it convenient to write:
	// <?php $domId = 'a-media-item-' . $id ?>
	// <li id="<?php echo $domId ?>"> ... <li> 
	// <?php a_js_call('apostrophe.setObjectId(?, ?)', $domId, $id) ?>
	this.setObjectId = function(domId, objectId)
	{
		$('#' + domId).data('id', objectId);
	}

	this.selectOnFocus = function(selector)
	{
		$(selector).focus(function(){
			$(this).select();
		});
	}
	
	this.mediaItemRefresh = function(options) 
	{ 
		var id = options['id'];
		var url = options['url'];
		window.location = url;	
	} 
	
	this.mediaEnableMultiplePreview = function()
	{
	  // the preview images are by default set to display:none
    $('#a-media-selection-preview li:first').addClass('current');
    // set up cropping again; do hard reset to reinstantiate Jcrop
    aCrop.resetCrop(true);
	}
	
	this.mediaEnableSelectionSort = function(multipleOrderUrl)
	{
		$('#a-media-selection-list').sortable({ 
      update: function(e, ui) 
      {
        var serial = jQuery('#a-media-selection-list').sortable('serialize', {});
        $.post(multipleOrderUrl, serial);
      }
    });	
	}
		
	this.enablePageSettings = function(options)
	{
		var form = $('#' + options['id'] + '-form');
		// The form will not actually submit until ajaxDirty is false. This allows us
		// to wait for asynchronous things like the slug field AJAX updates to complete
		var ajaxDirty = false;
		form.submit(function() {
			tryPost();
			return false;
		});
		
		function tryPost()
		{
			if (ajaxDirty)
			{
				setTimeout(tryPost, 250);
			}
			else
			{
				$.post(options['url'], form.serialize(), function(data) {
					$('.a-page-overlay').hide();
					$('#' + options['id']).html(data);
				});
			}
		}
		
		if (options['new'])
		{
			var slugField = form.find('[name=settings[slug]]');
			var titleField = form.find('[name=settings[realtitle]]');
			var timeout = null;
			function changed()
			{
				ajaxDirty = true;
				$.get(options['slugifyUrl'], { slug: $(titleField).val() }, function(data) {
					slugField.val(options['slugStem'] + '/' + data);
					ajaxDirty = false;
				});
				timeout = null;
			}
			function setChangedTimeout()
			{
				// AJAX on every keystroke kills the server and isn't nice to the
				// browser either. Set a half-second timeout to do it if we don't
				// already have such a timeout ticking down
				if (!timeout)
				{
					timeout = setTimeout(changed, 500);
				}
			}
			titleField.change(changed);
			titleField.keyup(setChangedTimeout);
		}

		var engine = form.find('[name=settings[engine]]');
		engine.change(function() {
			updateEngineAndTemplate();
		});
		
		function updateEngineAndTemplate()
		{
			var url = options['engineUrl'];

	    var engineSettings = form.find('.a-engine-page-settings');
			var val = engine.val();
			var template = form.find('[name=settings[template]]').parents('.a-form-row');
		  if (!val.length)
		  {
		    engineSettings.html('');
				template.slideDown();
		  }
		  else
		  {
		    // AJAX replace engine settings form as needed
				template.slideUp();
				// null comes through as a string "null". false comes through as a string "false". 0 comes
				// through as a string "0", but PHP accepts that, fortunately
		    $.get(url, { id: options['pageId'] ? options['pageId'] : 0, engine: val }, function(data) {
					engineSettings.html(data);
					aUI(engineSettings);
		    });
		  }
		}
		updateEngineAndTemplate();
	}
	
	this.audioPlayerSetup = function(aAudioContainer, file)
	{
		aAudioContainer = $(aAudioContainer);
		if (typeof(aAudioContainer) == 'object' && aAudioContainer.length) 
		{
			var global_lp = 0;
			var global_wtf = 0;

			var btnPlay = aAudioContainer.find(".a-audio-play");
			var btnPause = aAudioContainer.find(".a-audio-pause");
			var sliderPlayback = aAudioContainer.find('.a-audio-playback');
			var sliderVolume = aAudioContainer.find('.a-audio-volume');
			var loadingBar = aAudioContainer.find('.a-audio-loader');
			var time = aAudioContainer.find('.a-audio-time');
			var aAudioPlayer = aAudioContainer.find('.a-audio-player');

			aAudioPlayer.jPlayer({
				ready: function ()
				{
					this.element.jPlayer("setFile", file);
				},
				swfPath: '/apostrophePlugin/swf',
				customCssIds: true
			})
			.jPlayer("onProgressChange", function(lp,ppr,ppa,pt,tt) {
		 		var lpInt = parseInt(lp);
		 		var ppaInt = parseInt(ppa);
		 		global_lp = lpInt;
				loadingBar.progressbar('option', 'value', lpInt);
		 		sliderPlayback.slider('option', 'value', ppaInt);

				if (global_wtf && global_wtf == parseInt(tt)) {
					timeLeft = parseInt(tt) - parseInt(pt);
					time.text($.jPlayer.convertTime(timeLeft));
				}
				else
				{
					global_wtf = parseInt(tt);				
				}
			})
			.jPlayer("onSoundComplete", function() {
				// this.element.jPlayer("play"); // Loop
			});
			btnPause.hide();
			loadingBar.progressbar();

			btnPlay.click(function() {
				aAudioPlayer.jPlayer("play");
				btnPlay.hide();
				btnPause.show();					
				return false;
			});

			btnPause.click(function() {
				aAudioPlayer.jPlayer("pause");
				btnPause.hide();
				btnPlay.show();
				return false;
			});

			sliderPlayback.slider({
				max: 100,
				range: 'min',
				animate: false,
				slide: function(event, ui) {
					aAudioPlayer.jPlayer("playHead", ui.value*(100.0/global_lp));
				}
			});

			sliderVolume.slider({
				value : 50,
				max: 100,
				range: 'min',
				animate: false,
				slide: function(event, ui) {
					aAudioPlayer.jPlayer("volume", ui.value);
				}
			});
		}
		else
		{
			throw "Cannot find DOM Element for Audio Player.";
		}
	}
	
	// Private methods callable only from the above (no this.foo = bar)
	function slotUpdateMoveButtons(id, name, slot, n, slots, updateAction)
	{
		var up = $(slot).find('.a-arrow-up');
		var down = $(slot).find('.a-arrow-down');
					
		if (n > 0)
		{
			// TODO: this is not sensitive enough to nested areas
			up.parent().show();
			up.unbind('click').click(function() {
				// It would be nice to confirm success here in some way
				$.get(updateAction, { id: id, name: name, permid: $(slot).data('a-permid'), up: 1 });
				apostrophe.swapNodes(slot, slots[n - 1]);
				apostrophe.areaUpdateMoveButtons(updateAction, id, name);
				return false;
			});
		}
		else
		{
		  up.parent().hide();
		}
		if (n < (slots.length - 1))
		{

			down.parent().show();
			down.unbind('click').click(function() {
				// It would be nice to confirm success here in some way
				$.get(updateAction, { id: id, name: name, permid: $(slot).data('a-permid'), up: 0 });
				apostrophe.swapNodes(slot, slots[n + 1]);
				apostrophe.areaUpdateMoveButtons(updateAction, id, name);
				return false;
			});
		}
		else
		{
			down.parent().hide();
		}
	}
	
	function slotShowEditViewPreloaded(pageid, name, permid)
	{
		var fullId = pageid + '-' + name + '-' + permid;
 		var editBtn = $('#a-slot-edit-' + fullId);
 		var editSlot = $('#a-slot-' + fullId);
		
		editBtn.parents('.a-slot, .a-area').addClass('editing-now'); // Apply a class to the Area and Slot Being Edited
		editSlot.children('.a-slot-content').children('.a-slot-content-container').hide(); // Hide the Content Container
		editSlot.children('.a-slot-content').children('.a-slot-form').fadeIn(); // Fade In the Edit Form
		editSlot.children('.a-control li.variant').hide(); // Hide the Variant Options
		aUI(editBtn.parents('.a-slot').attr('id')); // Refresh the UI scoped to this Slot
	}
	
	function _pageTemplateToggle(aPageTypeSelect, aPageTemplateSelect)
	{
	}
	
	function _menuToggle(button, menu, classname, overlay, beforeOpen, afterClosed)
	{	
		// Menu must have an ID. 
		// If the menu doesn't have one, we create it by appending 'menu' to the Button ID		
		if (menu.attr('id') == '') 
		{
			newID = button.attr('id')+'-menu';
			menu.attr('id', newID).addClass('a-options-container');
		}

		// Button Toggle
		button.unbind('click').click(function(){
			if (!button.hasClass('aActiveMenu')) 
			{ 
				menu.trigger('toggleOpen'); 
			}
			else 
			{
				menu.trigger('toggleClosed');
			}
		}).addClass('a-options-button');

		if (beforeOpen)
		{
			menu.bind('beforeOpen', beforeOpen);
		}
		if (afterClosed)
		{
			menu.bind('afterClosed', afterClosed);
		}

		// Open Menu, Create Listener
		menu.bind('toggleOpen', function(){
			menu.trigger('beforeOpen');
			button.addClass('aActiveMenu');
			menu.addClass(classname);			
			if (overlay) { overlay.stop().show(); }
			$(document).click(function(event){
				var target = $(event.target);
				if (target.hasClass('.a-page-overlay') || target.hasClass('.a-cancel')) 
				{
					menu.trigger('toggleClosed');
				}
				if ( !target.parents().is('#'+menu.attr('id')) ) 
				{
					menu.trigger('toggleClosed');
				}
			});	
		});

		menu.bind('toggleClosed', function(){
			// Close Menu, Destroy Listener
			button.removeClass('aActiveMenu');
			menu.removeClass(classname);
			if (overlay) { overlay.fadeOut(); };
			$(document).unbind('click'); // Clear out click event		
			menu.trigger('afterClosed');
		});

		menu.click(function(event){
			target = $(event.target);
			if (target.hasClass('a-options-cancel')) 
			{
				menu.trigger('toggleClosed');
			};			
		});

	}
} 

window.apostrophe = new aConstructor();


