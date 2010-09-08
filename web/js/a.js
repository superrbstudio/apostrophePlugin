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
	
	this.updateEngineAndTemplate = function(options)
	{
		var id = options['id'];
		var url = options['url'];
		
		var val = $('#a_settings_settings_engine').val();
	  if (!val.length)
	  {
	    // $('#a_settings_settings_template').attr('disabled',false); // Symfony doesn't like this.
			$('#a_settings_settings_template').siblings('div.a-overlay').remove();
	    $('#a_settings_engine_settings').html('');
	  }
	  else
	  {
			$('#a_settings_settings_template').siblings('div.a-overlay').remove();
			$('#a_settings_settings_template').before("<div class='a-overlay'></div>");
			$('#a_settings_settings_template').siblings('div.a-overlay').fadeTo(0,0.5).css('display','block');
	    // $('#a_settings_settings_template').attr('disabled','disabled'); // Symfony doesn't like this.
	    // AJAX replace engine settings form as needed
	    $.get(url, { id: id, engine: val }, function(data) {
  	    $('#a_settings_engine_settings').html(data);
	    });
	  }
	
		aAccordion('.a-page-settings-section-head');
		aRadioSelect('.a-radio-select', { });
		$('#a-page-settings').show();
		aUI();
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
						aCloseHistory();
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
	}

	this.pageSettings = function(options)
	{
		var aPageSettingsURL = options['aPageSettingsURL'];
		var aPageSettingsButton = $('#a-page-settings-button');		

		aMenuToggle('#a-page-settings-button', $('#a-page-settings-button').parent(), '', true);

		aPageSettingsButton.click(function() {
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
		});
	}
	
	this.mediaCategories = function(options) 
	{	
		var newCategoryLabel = options['newCategoryLabel'];	
		aInputSelfLabel('#a_media_category_name', newCategoryLabel);	
		$('#a-media-edit-categories-button, #a-media-no-categories-messagem, #a-category-sidebar-list').hide();
		$('#a_media_category_description').parents('div.a-form-row').addClass('hide-description').parent().attr('id','a-media-category-form');
		$('.a-remote-submit').aRemoteSubmit('#a-media-edit-categories');
	}
	
	this.mediaEnableRemoveButton = function(i)
	{
		var editor = $('#a-media-item-' + i);
		editor.find('.a-media-remove-file').click(function()
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
	
	// This is just the beginning of bigger refactoring needed in this area
	this.slotShowEditView = function(editBtn, editSlot)
	{
		editBtn.parents('.a-slot, .a-area').addClass('editing-now'); // Apply a class to the Area and Slot Being Edited
		editSlot.children('.a-slot-content').children('.a-slot-content-container').hide(); // Hide the Content Container
		editSlot.children('.a-slot-content').children('.a-slot-form').fadeIn(); // Fade In the Edit Form
		editSlot.children('.a-control li.variant').hide(); // Hide the Variant Options
		aUI(editBtn.parents('.a-slot').attr('id')); // Refresh the UI scoped to this Slot
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
		  slots.find('.a-arrow-up,.a-arrow-down').hide();
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
	
	// Private methods callable only from the above (no this.foo = bar)
	function slotUpdateMoveButtons(id, name, slot, n, slots, updateAction)
	{
		if (n > 0)
		{
			// TODO: this is not sensitive enough to nested areas
			$(slot).find('.a-arrow-up').show().unbind('click').click(function() {
				// It would be nice to confirm success here in some way
				$.get(updateAction, { id: id, name: name, permid: $(slot).data('a-permid'), up: 1 });
				apostrophe.swapNodes(slot, slots[n - 1]);
				apostrophe.areaUpdateMoveButtons(updateAction, id, name);
				return false;
			});
		}
		else
		{
		  $(slot).find('.a-arrow-up').hide();
		}
		if (n < (slots.length - 1))
		{
			$(slot).find('.a-arrow-down').show().unbind('click').click(function() {
				// It would be nice to confirm success here in some way
				$.get(updateAction, { id: id, name: name, permid: $(slot).data('a-permid'), up: 0 });
				apostrophe.swapNodes(slot, slots[n + 1]);
				apostrophe.areaUpdateMoveButtons(updateAction, id, name);
				return false;
			});
		}
		else
		{
			$(slot).find('.a-arrow-down').hide();
		}
	}
} 

window.apostrophe = new aConstructor();


