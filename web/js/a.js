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

	// Utility: A DOM ready that can be used to hook into Apostrophe related events
	this.ready = function(options)
	{
		// apostrophe.log('apostrophe.ready');
		// You can define this function in your site.js to execute code whenenever apostrophe calls aUI();
		// We use this for refreshing progressive enhancements such as Cufon following an Ajax request.
		if (typeof(apostropheReady) =="function")
		{ 
			apostropheReady(); 	
		}
		
		// This is deprecated, it's the old function name, 
		// preserved here for backwards compatibility
		if (typeof(aOverrides) =="function")
		{ 
			aOverrides(); 	
		}		
	}

	// Utility: Swap two DOM elements without cloning them -- http://blog.pengoworks.com/index.cfm/2008/9/24/A-quick-and-dirty-swap-method-for-jQuery
	this.swapNodes = function(a, b) {
    var t = a.parentNode.insertBefore(document.createTextNode(''), a); 
    b.parentNode.insertBefore(a, b); 
    t.parentNode.insertBefore(b, t); 
    t.parentNode.removeChild(t);
	}	
	
	// Utility: console.log wrapper prevents JS errors if we leave an apostrophe.log call hanging out in our code someplace
	this.log = function(output)
	{ 
		if (window.console && console.log) {
			console.log(output);
		};
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

	// Utility: Use to select contents of an input on focus
	this.selectOnFocus = function(selector)
	{
		$(selector).focus(function(){
			$(this).select();
		});
	}
	
	// Utility: Self Labeling Input Element 
	// Example: <?php a_js_call('apostrophe.selfLabel(?)', array('selector' => '#forgot_password_email_address', 'title' => 'Email', 'select' => true, 'focus' => true)) ?>
	// options['select'] = true -- Selects the input on focus
	// options['focus'] = true -- Focuses the input on ready
	this.selfLabel = function(options)
	{
		aInputSelfLabel(options['selector'], options['title'], options['select']);
		if (options['focus'])
		{
			$(options['selector']).focus();
		};
	};
	
	// Utility: Click an element once and convert it to a span
	// Useful for turning an <a> into a <span>
	this.aClickOnce = function(selector)
	{
		var selector = $(selector);
		selector.unbind('click').click(function(){   
			apostrophe.toSpan(selector);
		});
	}

	// Utility: Replaces selected node with <span>
	this.toSpan = function(selector)
	{
		selector = $(selector);
		if (selector.length) {
			var id = ""; var clss = "";
			if (selector.attr('id') != '') { id = "id='"+selector.attr('id')+"'"; };
			if (selector.attr('class') != '') { clss = "class='"+selector.attr('class')+"'"; };		
			selector.replaceWith("<span " + clss + " " + id +">" + selector.html() + "</span>");				
		}
		else
		{
			apostrophe.log('apostrophe.toSpan -- No Elements Found');
		};
	}	
	
	// Utility: an updated version of the jq_link_to_remote helper
	// Allows you to create the same functionality without outputting javascript in the markup.
	this.linkToRemote = function(options)
	{
		var link = $(options['link']);
		var update = $(options['update']);
		var method = (options['method'])? options['method']:'GET';
		var remote_url = options['url'];
		var eventType = (options['event'])? options['event']:'click';
		
		if (link.length) {
			link.bind(eventType, function(){
				$.ajax({
					type:method,
					dataType:'html',
					success:function(data, textStatus)
					{
						update.html(data);
					},
					url:remote_url
				}); 
				return false;			
			});
		}
		else
		{
		apostrophe.log('apostrophe.linkToRemote -- No Link Found');	
		};
		if (!update.length) 
		{
		apostrophe.log('apostrophe.linkToRemote -- No Update Target Found');				
		};
	}
	
	// Utility: Create an anchor button that toggles between two radio buttons
	this.radioToggleButton = function(options)
	{
		// Which label to show on load 'option-1' or 'option-2'
		var optDefault = (options['optDefault'])? options['optDefault'] : 'option-1';
		// Set the button toggle labels
		var opt1Label = (options['opt1Label'])? options['opt1Label'] : 'on';
		var opt2Label = (options['opt2Label'])? options['opt2Label'] : 'off';
		var field = $(options['field']);
		
		if (field.length)
		{
			options['debug'] ? apostrophe.log('apostrophe.radioToggleButton -- debugging') : field.find('.radio_list').hide();
			
			var radios = field.find('input[type="radio"]');
			radios.length ? '' : apostrophe.log('apostrophe.radioToggleButton -- No radio inputs found');			

			var toggleButton = $('<a/>');			
			toggleButton.addClass('a-btn icon lite a-toggle-btn');
			toggleButton.html('<span class="icon"></span><span class="option-1">' + opt1Label + '</span><span class="option-2">' + opt2Label + '</span>').addClass(optDefault);
			field.prepend(toggleButton);
			toggleButton.click(function(){
				updateToggle($(this));
			});
			function updateToggle(button)
			{
				button.toggleClass('option-1').toggleClass('option-2');
				if ($(radios[0]).is(':checked'))
				{
					$(radios[0]).attr('checked',null);
					$(radios[1]).attr('checked','checked');
				}
				else
				{
					$(radios[1]).attr('checked',null);
					$(radios[0]).attr('checked','checked');				
				};
			}
		}
		else
		{
			field.length ? '' : apostrophe.log('apostrophe.radioToggleButton -- No field found');
		};
	}

	// Utility: IE6 Users get a special message when they log into apostrophe
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
	
	// This sets up the Reorganization Tool
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
	        // To avoid creating an inconsistent tree we need to use a synchronous request. If the request fails, refresh the
	        // tree page (TODO: find out if there's some way to flunk an individual drag operation). This shouldn't happen anyway
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

	// aSlideshowSlot
	this.slideshowSlot = function(options)
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
				  setPosition(position);				
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
  				setPosition(position);
  			}
  	  	interval();
  	  }
	
			function setPosition(pos) 
			{ 
				positionHead.text(pos + 1);	
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

  		var position = 0;
  		$('#a-slideshow-item-' + id + '-' + position).show();

  		if (positionFlag)
  		{
    		var positionHead = $('#a-slideshow-controls-' + id + ' li.a-slideshow-position span.head');
    		setPosition(position);
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


  	  interval();
	  }
	};
	
	// aButtonSlot
	this.buttonSlot = function(options)
	{
		var button = (options['button'])? $(options['button']) : false;
		var rollover = (options['rollover']) ? options['rollover'] : false;
	
	
		if (button.length) 
		{
			if (rollover)
			{
				var link = button.find('.a-button-title .a-button-link');
				var image = button.find('.a-button-image img');
				image.hover(function(){ image.fadeTo(0,.65); },function(){ image.fadeTo(0,1); });
				link.hover(function(){ image.fadeTo(0,.65); },function(){ image.fadeTo(0,1); });			
			};			
		}
		else
		{
			apostrophe.log('apostrophe.buttonSlot -- no button found');
		};
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
				// TODO: Make this less hacky. 
				// Using a class for the selector could return multiple hits with possibly with different HREF values.
				// This would grab the first one and go, with no regard for if it's the correct one or not.
				document.location = $('.a-js-media-edit-multiple-cancel').attr('href');
			}
			return false;
		});
	}
	
	// Listens to the file input for a media form and returns visual feedback if a new file is selected 
	this.mediaReplaceFileListener = function(options)
	{
		var menu = $(options['menu']);
		var input = $(options['input']);
		var message = 'This file will be replaced with the new file you have selected after you click save.';
		var fileLabel = 'File: ';

		if (options['message']) 
		{
			message = options['message'];
		};

		if (options['fileLabel']) 
		{
			fileLabel = options['fileLabel'];
		};
		
		if (input.length) {
			input.change(function(){
				if (input.val()) 
				{
					menu.trigger('toggleClosed');
					var newFileMessage = $('<div/>');
					newFileMessage.html('<div class="a-options open"><p>'+ message + '</p><p>'+ fileLabel + '<span>' + input.val() + '</span>' + '</p></div>');
					newFileMessage.addClass('a-new-file-message help');
					apostrophe.log(newFileMessage);
					input.closest('.a-form-row').append(newFileMessage);
				};
			});
		}
		else
		{
			apostrophe.log('apostrophe.mediaReplaceFileListener -- no input found');
		};
	}
	
	// Upon submission, if the media form has an empty file field and it is in a context to do so, it submits with AJAX -- Otherwise, it will submit normally
	this.mediaAjaxSubmitListener = function(options)
	{
		var form = $(options['form']);
		var url = options['url'];
		var update = $(options['update']);
    var file = form.find('input[type="file"]');
		var descId = options['descId'];
		var fck = $('#'+descId);
		if (form.length) {
		  form.submit(function(event) {
				if (fck.length) {
					fck.val(FCKeditorAPI.GetInstance(descId).GetXHTML());						
				};
				// If the file field is empty we can submit the edit form asynchronously
		    if(file.val() == '')
		    { 
		      event.preventDefault();
		      $.post(url, form.serialize(), function(data) {
							update.html(data);
					});
		    }
		  });
		}
		else
		{
			apostrophe.log('apostrophe.mediaAjaxSubmitListener -- No form found');
		};
	}
	
	this.mediaFourUpLayoutEnhancements = function(options)
	{
		var items = $(options['selector']);

		if (typeof(items) == 'undefined' || !items.length) {
			apostrophe.log('apostrophe.mediaFourUpLayoutEnhancements -- Items is undefined or no items found');
			apostrophe.log(items);
		}

		items.mouseover(function(){
			var item  = $(this);
			item.addClass('over');
		})
		.mouseout(function(){
			var item  = $(this);
			item.find('img').removeClass('dropshadow');
			item.removeClass('over');
		}).
		mouseleave(function(){
			var item  = $(this);
			if (!item.data('hold_delete'))
			{
				destroyItemSlug(item);
			};
		});

		items.find('.a-media-item-thumbnail')
		.hoverIntent(function(){
			var item = $(this).closest('.a-media-item');
			if (!item.data('hold_create')) 
			{
				createItemSlug(item);				
			};
		},function(){
			// mouse out
		});
		
		items.each(function(){
			var item  = $(this);			
			if (item.hasClass('a-type-video')) 
			{
				// We don't want to play videos in this view
				// We want the click to pass through to showSuccess
				// So we unbind the mediaEmbeddableToggle();
				item.unbind('embedToggle').find('.a-media-thumb-link').unbind('click').click(function(){
					return true;
				});
			};
		});
			
		function createItemSlug(item)
		{
			var w = item.css('width');
			var h = item.css('height');
			var img = item.find('img');
			
			var slug = $('<div/>');
			slug.attr('id', item.attr('id')+'-slug');
			slug.addClass('a-media-item-slug');
			slug.css({ width:w, height:h });

			if (item.hasClass('last')) 
			{
				slug.addClass('last');
			};

			item.wrap(slug).addClass('dropshadow expand').data('hold_create', 1);
			var offset = '-' + Math.floor(img.attr('height')/2) + 'px';
			item.css('margin-top',offset);
		}
		
		function destroyItemSlug(item)
		{
			if (item.parent('.a-media-item-slug').length) {
				item.unwrap();
			};
			item.removeClass('over dropshadow expand').css('margin-top','').data('hold_create', null);							
		}
	}	
	
	this.mediaEnableLinkAccount = function(previewUrl)
	{
		var form = $('#a-media-add-linked-account');
		var ready = false;
		form.submit(function()
	  {
			if (ready)
			{
				return true;
			}
	    $('#a-media-account-preview-wrapper').load(
				previewUrl, 
				$('#a-media-add-linked-account').serialize(),
				function() {
					$('#a-account-preview-ok').click(function() {
						ready = true;
						form.submit();
					});
					$('#a-account-preview-cancel').click(function() {
						$('#a-media-account-preview-wrapper').hide();
						return false;
					});
					$('#a-media-account-preview-wrapper').show();
				});
	    return false;
	  });
 	}

	this.mediaEmbeddableToggle = function(options)
	{
		var items = $(options['selector']);
		if (items.length) {
			items.each(function(){
				var item = $(this);
				item.bind('embedToggle',function(){
					item.children('.a-media-item-thumbnail').addClass('a-hidden');
					item.children('.a-media-item-embed').removeClass('a-hidden');					
				});
				var link = item.find('.a-media-thumb-link');
				link.unbind('click').click(function(e){
					e.preventDefault();
					item.trigger('embedToggle');
				});
			});
		}
		else
		{
			apostrophe.log('apostrophe.mediaEmbeddableToggle -- no items found');
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
		
	this.slotShowEditView = function(pageid, name, permid, realUrl)
	{	
		var fullId = pageid + '-' + name + '-' + permid;
 		var editSlot = $('#a-slot-' + fullId);
	  if (!editSlot.children('.a-slot-content').children('.a-slot-form').length)
	  {
 		  $.get(editSlot.data('a-edit-url'), { id: pageid, slot: name, permid: permid, realUrl: realUrl }, function(data) { 
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
	
	this.slotEnableEditButton = function(pageid, name, permid, editUrl, realUrl)
	{
		var fullId = pageid + '-' + name + '-' + permid;
 		var editBtn = $('#a-slot-edit-' + fullId);
 		var editSlot = $('#a-slot-' + fullId);
		editSlot.data('a-edit-url', editUrl);
 		editBtn.click(function(event) {
			apostrophe.slotShowEditView(pageid, name, permid, realUrl);
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
			apostrophe.log('apostrophe.menuToggle -- Button is undefined');
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
			apostrophe.log('apostrophe.accordion -- Toggle is undefined.');
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
					apostrophe.log(data);
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
			titleField.focus();			
			titleField.change(changed);
			titleField.keyup(setChangedTimeout);

			// More Options... Button
			$(form).find('.a-more-options-btn').click(function(e){
				e.preventDefault();
				$(this).hide().next().removeClass('a-hidden');
			});	
		}

		var combinedPageType = form.find('[name=combined_page_type]');
		
		var template = form.find('[name=settings[template]]');
		var engine = form.find('[name=settings[engine]]');
		
		for (var i = 0; (i < template[0].options.length); i++)
		{
			var option = template[0].options[i];
			var item = $('<option></option>');
			item.text($(option).text());
			item.val(':' + option.value);
			combinedPageType.append(item);
		}
		for (var i = 0; (i < engine[0].options.length); i++)
		{
			var option = engine[0].options[i];
			if (option.value === '')
			{
				continue;
			}
			var item = $('<option></option>');
			item.text($(option).text());
			// For now the template field must be 'default'
			// when the engine field is not empty
			item.val(option.value + ':default');
			combinedPageType.append(item);
		}
		if (engine.val() !== '')
		{
			combinedPageType.val(engine.val() + ':default');
		}
		else
		{
			combinedPageType.val(':' + template.val());
		}
		
		combinedPageType.change(function() {
			updateEngineAndTemplate();
		});
		
		function updateEngineAndTemplate()
		{
			var components = combinedPageType.val().split(':');
			var engineVal = components[0];
			var templateVal = components[1];
			engine.val(engineVal);
			template.val(templateVal);
			var url = options['engineUrl'];

	    var engineSettings = form.find('.a-engine-page-settings');
			var val = engine.val();
		  if (!val.length)
		  {
		    engineSettings.html('');
		  }
		  else
		  {
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
	
	this.buttonSauce = function(options)
	{
		// buttonSauce only needs to be executed when logged in
		// It applies any classes or additional markup necessary for apostrophe buttons via the .a-btn class
		// called in partial a/globalJavascripts
		var target = '';
		
		if (options && options['target']) {	target = options['target'];	};
		
		// Grab Target if Passed Through
		if (typeof(target) == 'undefined') 
		{ // If Not Set
			target = '';
		}
		else if (typeof(target) == 'object')
		{ // If jQuery object get id
			target = "#"+ target.attr('id') +" ";
		}
		else 
		{ // probably a string
			target = target+" ";
		}

		var aBtns = $(target+' .a-btn, ' + target + ' .a-submit, ' + target + ' .a-cancel');
		aBtns.each(function() {
			var aBtn = $(this);
			// Setup Icons for buttons with icons that are missing the icon container
			// Markup: <a href="#" class="a-btn icon a-some-icon"><span class="icon"></span>Button</a>
			if (aBtn.is('a') && aBtn.hasClass('icon') && !aBtn.children('.icon').length) 
			{
				// Button Exterminator
				aBtn.prepend('<span class="icon"></span>').addClass('a-fix-me');						
			};
	  });
	}
	
	this.miscEnhancements = function(options)
	{
		// The contents of this function can be migrated to better homes
		// if it makes sense to move them.
		// Once this function is empty it can be deleted
		// called in partial a/globalJavascripts
		// Variants
		$('a.a-variant-options-toggle').click(function(){
			$(this).parents('.a-slots').children().css('z-index','699');
			$(this).parents('.a-slot').css('z-index','799');	
		});
		// Cross Browser Opacity Settings
		$('.a-nav .a-archived-page').fadeTo(0,.5); // Archived Page Labels
		// Apply clearfix on controls and options
		$('.a-controls, .a-options').addClass('clearfix');
		// Add 'last' Class To Last Option
		$('.a-controls li:last-child').addClass('last'); 
		// Valid way to have links open up in a new browser window
		// Example: <a href="..." rel="external">Click Meh</a>
		$('a[rel="external"]').attr('target','_blank');
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
			var aAudioInterface = aAudioContainer.find('.a-audio-player-interface');
			aAudioPlayer.jPlayer({
				ready: function ()
				{
					this.element.jPlayer("setFile", file);
					aAudioInterface.removeClass('a-loading');
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
	
	// Just the toggles to display different parts of the page settings dialog
	this.enablePermissionsToggles = function()
	{
		var stem = '.view-options-widget';
		$(stem).change(function() {
			var v = $(stem + ':checked').val();
			if (v === 'login')
			{
				$('#a-page-permissions-view-extended').show();
			}
			else
			{
				$('#a-page-permissions-view-extended').hide();
			}
		});
		$('#a_settings_settings_view_options_public').change();
	
		$('#a_settings_settings_edit_admin_lock').change(function()
		{
			if ($(this).attr('checked'))
			{
				$('#a-page-permissions-edit-extended').hide();
			}
			else
			{
				$('#a-page-permissions-edit-extended').show();
			}
		});
		$('#a_settings_settings_edit_admin_lock').change();
	}
	
	// One permissions widget. Invoked several times - there are several in the page settings dialog
	this.enablePermissions = function(options)
	{
		// We need a fairly complex permissions widget. Deal with that.
		// Strategy: on every action update a data structure.
		// On every action that adds or removes an item, rebuild
		// the HTML representation
		var w = $('#' + options['id']);
		// We take in a flat array of data about users,
		// flip it into a list of ids and a hash of information
		// about those ids for efficiency
		var ids = [];
		var input = eval($('#' + options['hiddenField']).val());
		for (var i = 0; (i < input.length); i++)
		{
			ids[ids.length] = input[i]['id'];
		}
		var data = { };
		for (var i = 0; (i < ids.length); i++)
		{
			data[ids[i]] = input[i];
		}
		function rebuild() {
			var select = $('<select class="a-permissions-add"></select>');
			var list = $('<ul class="a-permissions-entries"></ul>');
			var option = $('<option></option>');
			option.val('');
			option.text(options['addLabel']);
			select.append(option);
			for (var i = 0; (i < ids.length); i++)
			{
				var user = data[ids[i]];
				var id = user['id'];
				var who = user['name'];
				if (!user['selected'])
				{
					var option = $('<option></option>');
					option.val(id);
					option.text(who);
					select.append(option);
				}
				else
				{
					var liMarkup = '<li class="a-permission-entry"><ul><li class="a-who"></li>';
					if (options['extra'])
					{
						liMarkup += '<li class="a-extra"><input type="checkbox" value="1" /> ' + options['extraLabel'] + '</li>';
					}
					liMarkup += '<li class="a-apply-to-subpages"><input type="checkbox" value="1" /> ' + options['applyToSubpagesLabel'] + '</li>';
					liMarkup += '<li class="a-actions"><a href="#" class="a-remove">' + options['removeLabel'] + '</a></li></ul></li>';
					li = $(liMarkup);
					li.find('.a-who').text(who);
					if (options['extra'])
					{
						li.find('.a-extra [type=checkbox]').attr('checked', user['extra']);
					}
					li.find('.a-apply-to-subpages [type=checkbox]').attr('checked', user['applyToSubpages']);
					li.data('id', id);
					if (user['selected'] === 'remove')
					{
						li.addClass('a-removing');
						li.find('.a-extra input').attr('disabled', true);
					}
					list.append(li);
				}
			}
			select.val('');
			select.change(function() {
				var id = select.val();
				data[id]['selected'] = true;
				rebuild();
				return false;
			});
			list.find('.a-remove').click(function() {
				var id = $(this).parents('.a-permission-entry').data('id');
				var user = data[id];
				if (user['selected'] === 'remove')
				{
					user['selected'] = true;
				}
				else
				{
					user['selected'] = 'remove';
				}
				rebuild();
				return false;
			});
			list.find('.a-extra [type=checkbox]').change(function() {
				var id = $(this).parents('.a-permission-entry').data('id');
				data[id]['extra'] = $(this).attr('checked');
				updateHiddenField();
				return true;
			});
			list.find('.a-apply-to-subpages [type=checkbox]').change(function() {
				var id = $(this).parents('.a-permission-entry').data('id');
				data[id]['applyToSubpages'] = $(this).attr('checked');
				updateHiddenField();
				return true;
			});
			w.html('');
			w.append(list);
			w.append(select);
			updateHiddenField();
		}
		rebuild();
		function updateHiddenField()
		{
			// Flatten the data into an array again for readout
			var flat = [];
			for (var i = 0; (i < ids.length); i++)
			{
				flat[flat.length] = data[ids[i]];
			}
			$('#' + options['hiddenField']).val(JSON.stringify(flat));
		}
	}
	
	this.enableMediaEditMultiple = function()
	{
		$('.a-media-multiple-submit-button').click(function() {
	    $('#a-media-edit-form-0').submit();
	    return false;
	  });
	  $('#a-media-edit-form-0').submit(function() {
	    return true;
	  });
	  $('#a-media-edit-form-0 .a-media-editor .a-delete').click(function() {
			$(this).parents('.a-media-editor').remove();
			if ($('#a-media-edit-form-0 .a-media-editor').length === 0)
			{
				window.location.href = $('#a-media-edit-form-0 .a-controls .a-cancel:first').attr('href');
			}
			return false;
	  });
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


