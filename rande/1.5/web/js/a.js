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

	this.setMessages = function(messages)
	{
		this.messages = messages;
	}

	// Utility: A DOM ready that can be used to hook into Apostrophe related events
	this.ready = function(options)
	{
		// apostrophe.log('apostrophe.ready');
		// You can define this function in your site.js
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
	// The mouseup event is a workaround for a Chrome bug that deselects the text after focus
	this.selectOnFocus = function(selector)
	{
		$(selector).focus(function(){
			$(this).select();
		}).mouseup(function(e){
			e.preventDefault();
		});
	}

	// Utility: Self Labeling Input Element
	// Example: <?php a_js_call('apostrophe.selfLabel(?)', array('selector' => '#input_id', 'title' => 'Input Label', 'select' => true, 'focus' => false, 'persisentLabel' => false )) ?>
	// options['select'] = true -- Selects the input on focus
	// options['focus'] = true -- Focuses the input on ready
	// options['persisentLabel'] = true -- Keeps the label visible until the person starts typing
	this.selfLabel = function(options)
	{
		aInputSelfLabel(options['selector'], options['title'], options['select'], options['focus'], options['persistentLabel']);
	};

	// Utility: Click an element once and convert it to a span
	// Useful for turning an <a> into a <span>
	this.clickOnce = function(selector)
	{
		var selector = $(selector);
		selector.unbind('click.aClickOnce').bind('click.aClickOnce', function(){
			apostrophe.toSpan(selector);
		});
	}

	// Utility: Replaces selected node with <span>
	this.toSpan = function(selector)
	{
		// Use an each here to avoid problems with all of the items getting the
		// same span label
		$(selector).each(function() {
			var id = ""; var clss = "";
			if ($(this).attr('id') != '') { id = "id='"+$(this).attr('id')+"'"; };
			if ($(this).attr('class') != '') { clss = "class='"+$(this).attr('class')+"'"; };
			$(this).replaceWith("<span " + clss + " " + id +">" + $(this).html() + "</span>");
		});
	}

	// Utility: an updated version of the jq_link_to_remote helper
	// Allows you to create the same functionality without outputting javascript in the markup.
	// Restore feature stashes the old content in .data() and binds restore to a cancel button returned within data
	this.linkToRemote = function(options)
	{
		var link = $(options['link']);
		var update = $(options['update']);
		var method = (options['method'])? options['method']:'GET';
		var remote_url = options['url'];
		var eventType = (options['event'])? options['event']:'click';
		var restore = (options['restore']) ? options['restore'] : false;

		if (link.length) {
			link.bind(eventType, function(){
				$.ajax({
					type:method,
					dataType:'html',
					beforeSend:function(){
						update.addClass('a-remote-data-loading');
					},
					success:function(data, textStatus)
					{
						if (restore)
						{
							update.data('aBeforeUpdate', update.children().clone(true));						
						};
						update.html(data);
					},
					complete:function(){
						if (restore)
						{
							update.find('.a-cancel').unbind('click.aRestore').bind('click.aRestore', function(event){
								event.preventDefault();
								update.html(update.data('aBeforeUpdate'));
							});
						};
						update.removeClass('a-remote-data-loading');						
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

  this.unobfuscateEmail = function(id, email, label)
  {
    $('#' + id).attr('href', unescape(email)).html(unescape(label));
  }

	// Turns a form into an AJAX form that updates the element
	// with the DOM ID specified by options['update']. You must
	// specify a 'selector' option as well to identify the form.
	// This replaces jq_remote_form for some cases. For fancy cases
	// you should write a separate method here

	this.formUpdates = function(options)
	{
		var form = $(options['selector']);

		// Named bind prevents redundancy
		form.unbind('submit.aFormUpdates');
		form.bind('submit.aFormUpdates', function() {
			var updating = $('#' + options['update']);
			apostrophe.updating(updating);
			var action = form.attr('action');
			$.post(action, form.serialize(), function(data) {
				updating.trigger('aUpdated');
				updating.html(data);
			});
			return false;
		});
	}

	// Pass a selector (or jQuery object) and an 'updating' tab will appear above it
	// (or on its best alternative, if it has an ancestor with the a-ajax-attach-updating class).
	// Then call .trigger('aUpdated') on your element when you're ready for the notice to change
	// to "updated" and disappear shortly thereafter on its own. Slick, no?
	//
	// You can bind additional handlers to the aUpdating and aUpdated events if you wish.

	this.updating = function(selector)
	{
		var updating = $(selector);
		var submit = updating.find('.a-show-busy');

		if (!submit.data('busy'))
		{
			submit.data('busy',1).addClass('a-busy');
			if (!submit.hasClass('icon'))
			{
				submit.addClass('icon').prepend('<span class="icon"></span>');
			}
		};
	}

	// Utility: Create an anchor button that toggles between two radio buttons
	this.radioToggleButton = function(options)
	{
		// Set the button toggle labels
		var opt1Label = (options['opt1Label'])? options['opt1Label'] : 'on';
		var opt2Label = (options['opt2Label'])? options['opt2Label'] : 'off';
		var field = $(options['field']);
		var radios = field.find('input[type="radio"]');
		radios.length ? '' : apostrophe.log('apostrophe.radioToggleButton --' + field + '-- No radio inputs found');

		if (field.length)
		{
			options['debug'] ? apostrophe.log('apostrophe.radioToggleButton --' + field + '-- debugging') : field.find('.radio_list').hide();

			var toggleButton = $('<a/>');
			toggleButton.addClass('a-btn icon lite a-toggle-btn');
			toggleButton.html('<span class="icon"></span><span class="option-1">' + opt1Label + '</span><span class="option-2">' + opt2Label + '</span>');

			if (!field.find('.a-toggle-btn').length)
			{
				field.prepend(toggleButton);
				var btn = field.find('.a-toggle-btn');

				updateToggle(btn);

				btn.click(function(){
					toggle(btn);
				});
			};

		}
		else
		{
			field.length ? '' : apostrophe.log('apostrophe.radioToggleButton -- No field found');
		};

		function toggle(button)
		{
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
			updateToggle(button);
		};

		function updateToggle(button)
		{
			if ($(radios[0]).is(':checked'))
			{
				button.addClass('option-1').removeClass('option-2');
			}
			else
			{
				button.addClass('option-2').removeClass('option-1');
			};
		}

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
		var debug = options['debug'];
		var transition = options['transition'];
	  var id = options['id'];
	  var intervalEnabled = !!options['interval'];
	  var intervalSetting = options['interval'];
	  var positionFlag = options['position'];
   	var position = (options['startingPosition']) ? options['startingPosition'] : 0;
   	var duration = (options['duration']) ? options['duration'] : 300;
		var slideshow = $('#a-slideshow-' + id);
		var slideshowControls = slideshow.next('.a-slideshow-controls');
		var slideshowItems = slideshow.find('.a-slideshow-item');
		var itemCount = slideshowItems.length;
		var positionHead = slideshowControls.find('.a-slideshow-position-head');
		var intervalTimeout = null;
		var currentItem;
		var newItem;
		var oldItem;
		
 		(options['title']) ? slideshowItems.attr('title', options['title']) : slideshowItems.attr('title','');

		( debug ) ? apostrophe.log('apostrophe.slideshowSlot --'+id+'-- Debugging') : '';
		( debug ) ? apostrophe.log('apostrophe.slideshowSlot --'+id+'-- Item Count : ' + itemCount ) : '';

    if (itemCount === 1)
    {
			slideshow.addClass('single-image');
			$(slideshowItems[0]).show();
			( debug ) ? apostrophe.log('apostrophe.slideshowSlot --'+id+'-- Single Image') : '';
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

			function init()
			{
				// Initialize the slideshow
				// Hiding all of the items, showing the first one, setting the position, and starting the timer
				slideshowItems.hide();
				$(slideshowItems[position]).show();
	  		setPosition(position);
	  	  interval();
			}

  	  function previous()
  	  {
				currentItem = position;
				(position == 0) ? position = itemCount - 1 : position--;
				showItem(position, currentItem);
				( debug ) ? apostrophe.log('apostrophe.slideshowSlot --'+id+'-- Previous : ' + currentItem + ' / ' + position) : '';
  	  };

  	  function next()
  	  {
				currentItem = position;
				(position == itemCount-1) ? position = 0 : position++;
				showItem(position, currentItem);
				( debug ) ? apostrophe.log('apostrophe.slideshowSlot --'+id+'-- Next : ' + currentItem + ' / ' + position) : '';
  	  };

			function showItem(position, currentItem)
			{
				if (!slideshow.data('showItem'))
				{
					slideshow.data('showItem', 1);
					newItem = $(slideshowItems[position]);
					oldItem = (currentItem) ? $(slideshowItems[currentItem]) : slideshowItems;
					if (transition == 'crossfade')
					{
						oldItem.fadeOut(duration);
					}
					else
					{
						// Some browsers jump / scroll up if the parent loses height for the split second the oldItem is hidden
						// So we set the height here before changing the slideshow item. This is not a problem when crossfading, because there is always an item visible
						newItemHeight = newItem.height() + 'px';
						slideshow.css('height',newItemHeight);
						// Since we are not crossfading, just hide all of the slideshowItems
						slideshowItems.hide();
					};
					newItem.fadeIn(duration,function(){
						slideshow.data('showItem', 0);
					  setPosition(position);
						interval();
					});
				};
			};

			function setPosition(p)
			{
				slideshow.data('position', p);
				( debug ) ? apostrophe.log('apostrophe.slideshowSlot --'+id+'-- positionFlag : ' + positionFlag ) : '';
				( debug ) ? apostrophe.log('apostrophe.slideshowSlot --'+id+'-- setPosition : ' + (p + 1) ) : '';
	  		if (positionFlag && positionHead.length)
				{
					positionHead.text(parseInt(p) + 1);
					( debug ) ? apostrophe.log('apostrophe.slideshowSlot --'+id+'-- setPosition : ' + p + 1 ) : '';
				};
			};

  	  function interval()
  	  {
  	    if (intervalTimeout)
  	    {
  	      clearTimeout(intervalTimeout);
  	    };
  	    if (intervalEnabled)
  	    {
  	  	  intervalTimeout = setTimeout(next, intervalSetting * 1000);
  	  	  window.aSlideshowIntervalTimeouts['a-' + id] = intervalTimeout;
					( debug ) ? apostrophe.log('apostrophe.slideshowSlot --'+id+'-- Interval : ' + intervalSetting ) : '';
  	  	}
  	  };

			// 1. Initialize the slideshow
			init();

			// 2. Bind events
  		slideshow.bind('showItem', function(e,p){ showItem(p); });
			slideshow.bind('previousItem', function(){ previous(); });
			slideshow.bind('nextItem', function(){ next(); });

  	  slideshow.find('.a-slideshow-image').click(function(event) {
				event.preventDefault();
  			intervalEnabled = false;
				next();
			});

  		slideshowControls.find('.a-arrow-left').click(function(event){
  			event.preventDefault();
  			intervalEnabled = false;
  			previous();
  		});

  		slideshowControls.find('.a-arrow-right').click(function(event){
  			event.preventDefault();
  			intervalEnabled = false;
  			next();
  		});

  		slideshowControls.find('.a-arrow-left, .a-arrow-right').hover(function(){
  			$(this).addClass('over');
  		},function(){
  			$(this).removeClass('over');
  		});

	  }
	};

	// aButtonSlot
	this.buttonSlot = function(options)
	{
		var button = (options['button'])? $(options['button']) : false;
		var rollover = (options['rollover']) ? options['rollover'] : false;

		apostrophe.slotEnhancements({slot:'#'+button.closest('.a-slot').attr('id'), editClass:'a-options'});

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

	this.areaEnableDeleteSlotButton = function(options) {
		$('#' + options['buttonId']).click(function() {
			if (confirm(options['confirmPrompt']))
			{
				$(this).closest(".a-slot").fadeOut();
				$.post(options['url'], {}, function(data) {
					$("#a-slots-" + options['pageId'] + "-" + options['name']).html(data);
				});
			}
			return false;
		});
	}

	this.areaEnableAddSlotChoice = function(options) {
		var debug = options['debug'];
		var button = $("#" + options['buttonId']);
		(debug) ? apostrophe.log('apostrophe.areaEnableAddSlotChoice -- Debug') : '';
		(debug) ? apostrophe.log(button) : '';
		$(button).click(function() {
			var name = options['name'];
			var pageId = options['pageId'];
			$.post(options['url'], {}, function(data) {
				var slots = $('#a-slots-' + pageId + '-' + name);
				slots.html(data);
				var area = $('#a-area-' + pageId + '-' + name);
				area.removeClass('a-options-open');
			});
			return false;
		});
	}

	this.areaEnableHistoryButton = function(options) {
		var pageId = options['pageId'];
		var name = options['name'];
		var url = options['url'];
		var moreUrl = options['moreUrl'];
		var buttonId = options['buttonId'];
		$('#' + buttonId).click(function() {
			_closeHistory();
			_browseHistory($(this).closest('div.a-area'));
			$(".a-history-browser .a-history-items").data("area", "a-area-" + pageId + "-" + name);
			$(".a-history-browser .a-history-browser-view-more").click(function() {
		    $.post(moreUrl, {}, function(data) {
					$('.a-history-browser .a-history-items').html(data);
					$(".a-history-browser .a-history-browser-view-more .spinner").hide();
				});
				$(this).hide();
				return false;
			});
			$.post(url, {}, function (data) {
				$('.a-history-browser .a-history-items').html(data);
			});
			return false;
		});
	}

	this.areaUpdateMoveButtons = function(updateAction, id, name)
	{
		var area = $('#a-area-' + id + '-' + name);
		// Be precise - take care not to hoover up controls related to slots in nested areas, if there are any
		var slots = area.children('.a-slots').children('.a-slot');
		var newSlots = area.children('.a-slots').children('.a-new-slot');

		// I actually want a visible loop variable here
		for (n = 0; (n < slots.length); n++)
		{
			var slot = slots[n];
			// We use a nested function here because
			// a loop variable does *not* get captured
			// in the closure at its current value otherwise
			slotUpdateMoveButtons(id, name, slot, n, slots, updateAction);
		}

		if (newSlots.length)
		{
			// TODO: this is not sensitive enough to nested areas
			// TODO: with a little more finesse we could support saving it with
			// a rank, but think about how messy that might get

			// Hide the new slot's controls because it can't be moved until it is saved
		  newSlots.find('.a-slot-controls .a-move').addClass('a-hidden');

			// Hide the next slot's UP arrow because the slot cannot switch places with the unsaved new slot
			newSlots.next('.a-slot').find('.a-move.up').addClass('a-hidden');

			// Hide the prev slot's DOWN arrow because the slot cannot switch places with the unsaved new slot
			newSlots.prev('.a-slot').find('.a-move.down').addClass('a-hidden');

			// apostrophe.log('apostrophe.areaUpdateMoveButtons -- newSlots in ' + area.attr('id'));
			return;
		}
		// apostrophe.log('apostrophe.areaUpdateMoveButtons -- ' + area.attr('id'));
	}

	this.areaHighliteNewSlot = function(options)
	{
		var pageId = options['pageId'];
		var slotName = options['slotName'];
		var newSlot = $('#a-area-' + pageId + '-' + slotName).find('.a-new-slot');
		if (newSlot.length)
		{
			newSlot.effect("highlight", {}, 1000);
			$('#a-add-slot-' + pageId + '-' + slotName).parent().trigger('toggleClosed');
		};
	}

	this.areaSingletonSlot = function(options)
	{
		var pageId = options['pageId'];
		var slotName = options['slotName'];
		// Singleton Slot Controls
		$('#a-area-' + pageId + '-' + slotName + '.singleton .a-slot-controls-moved').remove();
		// Move up the slot controls and give them some class names.
		$('#a-area-' + pageId + '-' + slotName + '.singleton .a-slot-controls').prependTo($('#a-area-' + pageId + '-' + slotName)).addClass('a-area-controls a-slot-controls-moved').removeClass('a-slot-controls');
		// Singleton Slots can't have big history buttons!
		$('ul.a-slot-controls-moved a.a-btn.a-history-btn').removeClass('big');
	}

	this.slotEnableVariantButton = function(options)
	{
		var button = $('#' + options['buttonId']);
		// This gets called more than once, use namespaces to avoid double binding without
		// breaking other binds
		button.unbind('click.slotEnableVariantButton');
		button.bind('click.slotEnableVariantButton', function() {
			// Change the visibility of the variant buttons to their active and inactive states as appropriate
			var variants = $('#a-' + options['slotFullId'] + '-variant');
  		variants.find('ul.a-variant-options').addClass('loading');
  		variants.find('li.active').hide();
			variants.find('ul.a-variant-options li.inactive').show();
			var variantStem = '#a-' + options['slotFullId'] + '-variant-' + options['variant'];
			$(variantStem + '-active').show();
			$(variantStem + '-inactive').hide();
			variants.find('ul.a-variant-options').hide();

			$.post(options['url'], {}, function(data) {
				$('#' + options['slotContentId']).html(data);
			});
			return false;
		});
	}

	this.slotShowVariantsMenu = function(slot)
	{
		var outerWrapper = $(slot);
		var singletonArea = outerWrapper.closest('.singleton');
    if (singletonArea.length)
    {
      singletonArea.find('.a-controls li.variant').show();
    }
    else
    {
      outerWrapper.find('.a-controls li.variant').show();
    }
	}

	this.slotHideVariantsMenu = function(menu)
	{
	  var menu = $(menu);
		menu.removeClass('loading').fadeOut('slow').parent().removeClass('open');
	}

	this.slotApplyVariantClass = function(slot, variant)
	{
		var outerWrapper = $(slot);
	  outerWrapper.addClass(variant);
	}

	this.slotRemoveVariantClass = function(slot, variant)
	{
		var outerWrapper = $(slot);
	  outerWrapper.removeClass(variant);
	}

	this.slotEnhancements = function(options)
	{
		var slot = $(options['slot']);
		var editClass = options['editClass'];
		if (slot.length)
		{
			if (editClass);
			{
				slot.find('.a-edit-view').addClass(editClass);
			};
		}
		else
		{
			apostrophe.log('apostrophe.slotEnhancements -- No slot found.');
			apostrophe.log('apostrophe.slotEnhancements -- Selector: '+ options['slot']);
		};
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

	this.slotEnableForm = function(options)
	{
		$(options['slot-form']).submit(function() {
			apostrophe.updating(options['slot-form']);
	    $.post(
	      // These fields are the context, not something the user gets to edit. So rather than
	      // creating a gratuitous collection of hidden form widgets that are never edited, let's
	      // attach the necessary context fields to the URL just like Doctrine forms do.
	      // We force a query string for compatibility with our simple admin routing rule
	      options['url'],
	      $(options['slot-form']).serialize(),
	      function(data) {
	        $(options['slot-content']).html(data);
	      },
	      'html'
	    );
	    return false;
  	});
	}

	this.slotEnableFormButtons = function(options)
	{
    var view = $(options['view']);

		$(options['cancel']).click(function(e){
			e.preventDefault();
  		$(view).children('.a-slot-content').children('.a-slot-content-container').fadeIn();
  		$(view).children('.a-controls li.variant').fadeIn();
  		$(view).children('.a-slot-content').children('.a-slot-form').hide();
  		$(view).find('.a-editing').removeClass('a-editing').addClass('a-normal');
 			$(view).parents('.a-area.a-editing').removeClass('a-editing').addClass('a-normal').find('.a-editing').removeClass('a-editing').addClass('a-normal'); // for singletons
  	});

  	$(options['save']).click(function(){
  		$(view).find('.a-editing').removeClass('a-editing').addClass('a-normal');
 			$(view).parents('.a-area.a-editing').removeClass('a-editing').addClass('a-normal').find('.a-editing').removeClass('a-editing').addClass('a-normal'); // for singletons
 			window.apostrophe.callOnSubmit(options['slot-full-id']);
 			return true;
  	});

		if (options['showEditor'])
		{
			var editBtn = $(options['edit']);
			editBtn.parents('.a-slot, .a-area').addClass('a-editing').removeClass('a-normal'); // Apply a class to the Area and Slot Being Edited
	  }
	}

	this.mediaCategories = function(options)
	{
		var newCategoryLabel = options['newCategoryLabel'];
		apostrophe.selfLabel('#a_media_category_name', newCategoryLabel);
		$('#a-media-edit-categories-button, #a-media-no-categories-messagem, #a-category-sidebar-list').hide();
		$('#a_media_category_description').parents('div.a-form-row').addClass('hide-description').parent().attr('id','a-media-category-form');
		$('.a-remote-submit').aRemoteSubmit('#a-media-edit-categories');
	}

	// We send people away to the media repo to pick things and then they
	// decide to wander off and not pick things. We need to be realistic about
	// this and cancel their selection. A better idea would be to make
	// media admin/selection a "most-of-page" experience, maybe via an iframe, but
	// that's more of a 1.6 idea. For 1.5, this is a good band-aid fix

	this.mediaClearSelectingOnNavAway = function(mediaClearSelectingUrl)
	{
		$('a').click(function() {
			var href = $(this).attr('href');
			if (href === undefined)
			{
				return;
			}
			if (href.substr(0, 1) === '#')
			{
				return;
			}
			// Be tolerant of this being in the middle as a stopgap solution for the problem
			// of alternate document roots and frontend controllers in URLs
			if (href.match(/\/admin\/media/))
			{
				return;
			}
			apostrophe.log("Cancelling select for " + href);
			// "Why is this synchronous?" So that we can allow the events associated with
			// this link to execute normally (return true) after we request the cancel,
			// rather than second-guessing the nature of the link and screwing lots of
			// things up any more than we'realready going to by interfering here
			$.ajax({ url: mediaClearSelectingUrl, async: false });
			return;
		});
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
		var embedChanged = false;
		if (form.length) {
			form.find('.a-form-row.embed textarea').change(function() {
				embedChanged = true;
			});
		  form.submit(function(event) {
				if (fck.length) {
					fck.val(FCKeditorAPI.GetInstance(descId).GetXHTML());
				};
				// If the file field is empty and the embed code hasn't been changed,
				// we can submit the edit form asynchronously
				apostrophe.log(embedChanged);
		    if((file.val() == '') && (!embedChanged))
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
					$('#a-account-preview-ok').click(function(event) {
						event.preventDefault();
						ready = true;
						form.submit();
					});
					$('#a-account-preview-cancel').click(function(event) {
						event.preventDefault();
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
					var embed = item.data('embed_code');
					item.find('.a-media-item-thumbnail').addClass('a-previewing');
					item.find('.a-media-item-embed').removeClass('a-hidden').html(embed);
				});
				var link = item.find('.a-media-play-video');
				link.unbind('click.mediaEmbeddableToggle').bind('click.mediaEmbeddableToggle',function(e){
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

	this.mediaAttachEmbed = function(options)
	{
		var id = options['id'];
		var embed = options['embed'];
		var mediaItem = $('#a-media-item-' + id);
		mediaItem.data('embed_code', embed);
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
			// Normalize heights of thumbnails for visual consistency
			var items = $('.a-media-selection-list-item');
			var listHeight = 0;
			items.each(function(){
				var item = $(this);
				(listHeight < item.height()) ? listHeight = item.height() : '';
			});
			items.css('height',listHeight);
			apostrophe.log(listHeight);
	  });
	}

	this.mediaDeselectItem = function(id)
	{
		$('#a-media-item-'+id).removeClass('a-media-selected');
		$('#a-media-item-'+id).children('.a-media-selected-overlay').remove();
	}

	this.mediaEnableSelect = function(options)
	{
		apostrophe.selectOptions = options;
		// Binding it this way avoids a cascade of two click events when someone
		// clicks on one of the buttons hovering on this

		// I had to bind to all of these to guarantee a click would come through
	  $('.a-media-selection-list-item .a-delete').unbind('click.aMedia').bind('click.aMedia', function(e) {
			var p = $(this).parents('.a-media-selection-list-item');
			var id = p.data('id');
			$.get(options['removeUrl'], { id: id }, function(data) {
				$('#a-media-selection-list').html(data);
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

		// When you're in selecting mode, you can't click through to the showSuccess
		// So we use the thumbnail AND the title for making your media selection.
		$('.a-media-thumb-link, .a-media-item-title-link').unbind('click.aMedia').bind('click.aMedia', function(e) {
			e.preventDefault();
			$.get(options['multipleAddUrl'], { id: $(this).data('id') }, function(data) {
				$('#a-media-selection-list').html(data);
				apostrophe.mediaUpdatePreview();
			});
			$(this).addClass('a-media-selected');
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

	this.mediaEnableUploadMultiple = function()
	{
		function aMediaUploadSetRemoveHandler(element)
	  {
	    $(element).find('.a-close').click(function() {
	        // Move the entire row to the inactive form
	        var element = $($(this).parent().parent().parent()).remove();
	        $('#a-media-upload-form-inactive').append(element);
	        $('#a-media-add-photo').show();
	        return false;
	      });
	  }
	  // Move the first inactive element back to the active form
	  $('#a-media-add-photo').click(function() {
	      var elements = $('#a-media-upload-form-inactive .a-form-row');
	        $('#a-media-upload-form-subforms').append(elements);
	        $('#a-media-add-photo').hide();
	      return false;
	    });
	  // Move all the initially inactive elements to the inactive form
	  function aMediaUploadInitialize()
	  {
	    $('#a-media-upload-form-inactive').append($('#a-media-upload-form-subforms .a-form-row.initially-inactive').remove());
	    aMediaUploadSetRemoveHandler($('#a-media-upload-form-subforms'));
	    $('#a-media-upload-form .a-cancel').click(function() {
	      $('#a-media-add').hide();
	      return false;
	    });
	  }
	  aMediaUploadInitialize();
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
				_menuToggle(button, menu, classname, overlay, options['beforeOpen'], options['afterClosed'], options['afterOpen'], options['beforeClosed'], options['focus'], options['debug']);
			};
		};
	}

	this.pager = function(selector, pagerOptions)
	{
		$(selector + ':not(.a-pager-processed)').each(function() {

			var pager = $(this);
			pager.addClass('a-pager-processed');
			pager.find('.a-page-navigation-number').css('display', 'block');
			pager.find('.a-page-navigation-number').css('float', 'left');

			var nb_pages = parseInt(pagerOptions['nb-pages']);
			var nb_links = parseInt(pagerOptions['nb-links']);
			var selected = parseInt($(this).find('.a-page-navigation-number.a-pager-navigation-disabled').text());

			// If the number of links allowed is greater than the total number of pages returned
			// then we do not need the arrows. So let's use this class name so scope 'disabled' styles.
			(nb_links >= nb_pages) ? pager.addClass('a-pager-arrows-disabled') : pager.removeClass('a-pager-arrows-disabled');

			var min = selected;
			var max = selected + nb_links - 1;

			var links_container_container = pager.find('.a-pager-navigation-links-container-container');
			links_container_container.width((nb_links * pager.find('.a-page-navigation-number').first().outerWidth()));
			links_container_container.css('overflow', 'hidden');

			var links_container = pager.find('.a-pager-navigation-links-container');
			links_container.width((nb_pages * pager.find('.a-page-navigation-number').first().outerWidth()));

			var first = pager.find('.a-pager-navigation-first');
			var prev = pager.find('.a-pager-navigation-previous');
			var next = pager.find('.a-pager-navigation-next');
			var last = pager.find('.a-pager-navigation-last')

			function calculateMinAndMax()
			{
				if ((min < 1) && (max > nb_pages))
				{
					min = 1;
					max = nb_pages;
				}
				else if (min < 1)
				{
					var diff = 0;

					if (min < 0)
					{
						diff = 0 - min;
						diff = diff + 1;
					}
					else
					{
						diff = 1
					}
					min = 1;
					max = max + diff;
				}
				else if (max > nb_pages)
				{
					var diff = max - nb_pages;
					max = nb_pages;
					min = min - diff;
				}
			}

			function toggleClasses()
			{
				pager.find('.a-pager-navigation-disabled').removeClass('a-pager-navigation-disabled');
				if (min == 1)
				{
					first.addClass('a-pager-navigation-disabled');
					prev.addClass('a-pager-navigation-disabled');
				}
				else if (min == ((nb_pages - nb_links) + 1))
				{
					next.addClass('a-pager-navigation-disabled');
					last.addClass('a-pager-navigation-disabled');
				}
			}

			function updatePageNumbers()
			{
				pager.find('.a-page-navigation-number').each(function() {
					var current = parseInt($(this).text());

					if ((current >= min) && (current <= max))
					{
						$(this).show();
					}
					else
					{
						$(this).hide();
					}
				});
			}

			function animatePageNumbers() {
				var width = links_container.children('.a-page-navigation-number').first().outerWidth();

				width = (min - 1) * -width;
				links_container.animate({marginLeft: width}, 250, 'swing');
			}

			next.click(function(e) {
				e.preventDefault();

				min = min + nb_links;
				max = max + nb_links;

				calculateMinAndMax();
				toggleClasses();
				animatePageNumbers();

				return false;
			});

			last.click(function(e) {
				e.preventDefault();

				min = nb_pages;
				max = nb_pages + nb_links - 1;

				calculateMinAndMax();
				toggleClasses();
				animatePageNumbers();

				return false;
			});

			prev.click(function(e) {
				e.preventDefault();

				min = min - nb_links;
				max = max - nb_links;

				calculateMinAndMax();
				toggleClasses();
				animatePageNumbers();

				return false;
			});

			first.click(function(e) {
				e.preventDefault();

				min = 1;
				max = nb_links;

				calculateMinAndMax();
				toggleClasses();
				animatePageNumbers();

				return false;
			});

			calculateMinAndMax();
			toggleClasses();
			animatePageNumbers();
		});
	}

		/* Example Mark-up
		<script type="text/javascript">
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
		apostrophe.log('apostrophe.enablePageSettings');
		var form = $('#' + options['id'] + '-form');
		// Why is this necessary?
		$('#' + options['id'] + '-submit').click(function() {
			form.submit();
		});
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

		var joinedtemplate = form.find('[name=settings[joinedtemplate]]');
		joinedtemplate.change(function() {
			updateEngineAndTemplate();
		});

		function updateEngineAndTemplate()
		{
			var url = options['engineUrl'];

	    var engineSettings = form.find('.a-engine-page-settings');
			var val = joinedtemplate.val().split(':')[0];
		  if (val === 'a')
		  {
		    engineSettings.html('');
		  }
		  else
		  {
				// null comes through as a string "null". false comes through as a string "false". 0 comes
				// through as a string "0", but PHP accepts that, fortunately
		    $.get(url, { id: options['pageId'] ? options['pageId'] : 0, engine: val }, function(data) {
					engineSettings.html(data);
		    });
		  }
		}
		updateEngineAndTemplate();
	}

	// A very small set of things that allow us to write CSS and HTML as if they were
	// better than they are. This is called on every page load and AJAX refresh, so resist
	// the temptation to get too crazy here.

	// Specifying a target option can help performance by not searching the rest
	// of the DOM for things that have already been magicked

	// CODE HERE MUST TOLERATE BEING CALLED SEVERAL TIMES. Use namespaced binds and unbinds.
	this.smartCSS = function(options)
	{
		var target = 'body';
		if (options && options['target'])
		{
			target = options['target'];
		};

		// KEEPERS START HERE

		// Anchor elements that act as submit buttons. Unfortunately not suitable
		// for use in AJAX forms because calling submit() on a form doesn't
		// consistently trigger its submit handlers before triggering native submit.

		var actAsSubmit = $(target).find('.a-act-as-submit');
		actAsSubmit.unbind('click.aActAsSubmit');
		actAsSubmit.bind('click.aActAsSubmit', function() {
			var form = $(this).parents('form:first');
			var name = $(this).attr('name');
			// Submit buttons have names used to distinguish them.
			// Fortunately, anchors have names too. There is NO
			// default name - and in particular 'submit' breaks
			// form.submit, so don't use it
			if (name.length)
			{
				var hidden = $('<input type="hidden"></input>');
				hidden.attr('name', name);
				hidden.attr('value', 1);
				form.append(hidden);
				form = $(this).parents('form:first');
			}
			form.submit();
			return false;
		});

		// The contents of this function can be migrated to better homes
		// if it makes sense to move them.
		// Once this function is empty it can be deleted
		// called in partial a/globalJavascripts
		// Variants
		$('a.a-variant-options-toggle').unbind('click.aVariantOptionsToggle').bind('click.aVariantOptionsToggle', function(){
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

		// THINGS WE'D LIKE TO GET RID OF START HERE

		// Apply any classes or additional markup necessary for apostrophe buttons via the .a-btn class
		// called in partial a/globalJavascripts. This is deprecated, we should put the right spans in them to
		// begin with, which is easier now with a_js_button and a_link_button, so we're showing these
		// not-properly-formatted buttons in red in anticipation of killing this code

		var aBtns = $(target).find('.a-btn,.a-submit,.a-cancel');
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
			var j = 0;
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
					var liMarkup = '<li class="a-permission-entry ' + ((j%2) ? 'even':'odd') + ' clearfix"><ul><li class="a-who"></li>';
					if (options['extra'])
					{
						liMarkup += '<li class="a-cascade-option extra"><div class="cascade-checkbox"><input type="checkbox" value="1" /> ' + options['extraLabel'] + '</div></li>';
					}
					if (options['hasSubpages'])
					{
						liMarkup += '<li class="a-cascade-option apply-to-subpages"><div class="cascade-checkbox"><input type="checkbox" value="1" /> ' + options['applyToSubpagesLabel'] + '</div></li>';
					}
					// PLEASE NOTE code is targeting a-close-small, if you change that class you have to change the selector elsewhere
					liMarkup += '<li class="a-actions"><a href="#" class="a-close-small a-btn icon no-label no-bg">' + options['removeLabel'] + '<span class="icon"></span></a></li></ul></li>';
					li = $(liMarkup);
					li.find('.a-who').text(who);
					if (options['extra'])
					{
						li.find('.extra [type=checkbox]').attr('checked', user['extra']);
					}
					li.find('.apply-to-subpages [type=checkbox]').attr('checked', user['applyToSubpages']);
					li.data('id', id);
					if (user['selected'] === 'remove')
					{
						li.addClass('a-removing');
						li.find('.a-extra input').attr('disabled', true);
					}
					list.append(li);
					j++;
				}
			}
			select.val('');
			select.change(function() {
				var id = select.val();
				data[id]['selected'] = true;
				rebuild();
				return false;
			});
			list.find('.a-close-small').click(function() {
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
			list.find('.extra [type=checkbox]').change(function() {
				var id = $(this).parents('.a-permission-entry').data('id');
				data[id]['extra'] = $(this).attr('checked');
				updateHiddenField();
				return true;
			});
			list.find('.apply-to-subpages [type=checkbox]').change(function() {
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

	this.aAdminEnableFilters = function()
	{
		$('#a-admin-filters-open-button').click(function() {
			$('#a-admin-filters-container').slideToggle();
			return false;
		});
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

			var targetArea = "#"+$(this).parent().data('area');								// this finds the associated area that the history browser is displaying
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

	this.enableCloseHistoryButtons = function(options)
	{
		var closeHistoryBtns = $(options['close_history_buttons']);
		closeHistoryBtns.click(function(){
			_closeHistory();
		});
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
						},
						url: aPageSettingsCreateURL
				});
			},
			"afterClosed": function() {
				$('#a-create-page').html('');
			}
		});
	}

	this.enableUserAdmin = function(options)
	{
		// Right now this is also called for groups and permissions admin, account for that if you add anything nutty. -Tom
		$('.a-admin #a-admin-filters-container #a-admin-filters-form .a-form-row .a-admin-filter-field br').replaceWith('<div class="a-spacer"></div>');
		aMultipleSelectAll({ 'choose-one': options['choose-one-label']});
	}

	// Private methods callable only from the above (no this.foo = bar)
	function slotUpdateMoveButtons(id, name, slot, n, slots, updateAction)
	{
		var up = $(slot).find('.a-arrow-up');
		var down = $(slot).find('.a-arrow-down');

		if (n > 0)
		{
			// TODO: this is not sensitive enough to nested areas
			up.parent().removeClass('a-hidden');
			up.unbind('click').click(function() {
				// It would be nice to confirm success here in some way
				$.get(updateAction, { id: id, name: name, permid: $(slot).data('a-permid'), up: 1 });
				apostrophe.swapNodes(slot, slots[n - 1]);
				apostrophe.areaUpdateMoveButtons(updateAction, id, name);
				apostrophe.log('move up');
				return false;
			});
		}
		else
		{
		  up.parent().addClass('a-hidden');
		}
		if (n < (slots.length - 1))
		{
			down.parent().removeClass('a-hidden');
			down.unbind('click').click(function() {
				// It would be nice to confirm success here in some way
				$.get(updateAction, { id: id, name: name, permid: $(slot).data('a-permid'), up: 0 });
				apostrophe.swapNodes(slot, slots[n + 1]);
				apostrophe.areaUpdateMoveButtons(updateAction, id, name);
				apostrophe.log('move down');
				return false;
			});
		}
		else
		{
			down.parent().addClass('a-hidden');
		}
	}

	function slotShowEditViewPreloaded(pageid, name, permid)
	{
		var fullId = pageid + '-' + name + '-' + permid;
 		var editBtn = $('#a-slot-edit-' + fullId);
 		var editSlot = $('#a-slot-' + fullId);

		editBtn.parents('.a-slot, .a-area').addClass('a-editing').removeClass('a-normal'); // Apply a class to the Area and Slot Being Edited
		editSlot.children('.a-slot-content').children('.a-slot-content-container').hide(); // Hide the Content Container
		editSlot.children('.a-slot-content').children('.a-slot-form').fadeIn(); // Fade In the Edit Form
		editSlot.children('.a-control li.variant').hide(); // Hide the Variant Options
	}

	function _browseHistory(area)
	{
		var areaControls = area.find('ul.a-area-controls');
		var areaControlsTop = areaControls.offset().top;
		$('.a-page-overlay').fadeIn();
		// Clear Old History from the Browser
		if (!area.hasClass('browsing-history'))
		{
			$('.a-history-browser .a-history-items').html('<tr class="a-history-item"><td class="date"><img src="\/apostrophePlugin\/images\/a-icon-loader-2.gif"><\/td><td class="editor"><\/td><td class="preview"><\/td><\/tr>');
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
		$('.a-page-overlay').hide();
	}

	function _pageTemplateToggle(aPageTypeSelect, aPageTemplateSelect)
	{
	}

	function _menuToggle(button, menu, classname, overlay, beforeOpen, afterClosed, afterOpen, beforeClosed, focus, debug)
	{

		debug ? apostrophe.log('apostrophe.menuToggle -- debug -- #' + button.attr('id') ) : '';

		// Menu must have an ID.
		// If the menu doesn't have one, we create it by appending 'menu' to the Button ID
		if (menu.attr('id') == '')
		{
			newID = button.attr('id')+'-menu';
			menu.attr('id', newID).addClass('a-options-container');
		}

		// Button Toggle
		button.unbind('click.menuToggle').bind('click.menuToggle', function(event){
			event.preventDefault();
			if (!button.hasClass('aActiveMenu'))
			{
				menu.trigger('toggleOpen');
			}
			else
			{
				menu.trigger('toggleClosed');
			}
		}).addClass('a-options-button');

		if (beforeOpen) { menu.bind('beforeOpen', beforeOpen); }
		if (afterClosed) { menu.bind('afterClosed', afterClosed); }
		if (afterOpen) { menu.bind('afterOpen', afterOpen);	}
		if (beforeClosed) { menu.bind('beforeClosed', beforeClosed); }

		var clickHandler = function(event){
			var target = $(event.target);
			debug ? apostrophe.log('apostrophe.menuToggle -- clickHandler Target') : '';
			debug ? apostrophe.log(target) : '';
			if (target.hasClass('a-page-overlay') || target.hasClass('a-cancel') || (!target.parents().is('#'+menu.attr('id')) && !target.parents().hasClass('ui-widget')))
			{
				menu.trigger('toggleClosed');
			}
		};

		// Open Menu, Create Listener
		menu.unbind('toggleOpen').bind('toggleOpen', function(){
			menu.trigger('beforeOpen');
			button.addClass('aActiveMenu');
			menu.parents().addClass('ie-z-index-fix');
			button.closest('.a-controls').addClass('aActiveMenu');
			menu.addClass(classname);
			if (overlay) { overlay.fadeIn(); }
			$(document).bind('click.menuToggleClickHandler', clickHandler);
			if (focus) { $(focus).focus(); };
			menu.trigger('afterOpen');
		});

		// Close Menu, Destroy Listener
		menu.unbind('toggleClosed').bind('toggleClosed', function(){
			menu.trigger('beforeClosed');
			button.removeClass('aActiveMenu');
			menu.parents().removeClass('ie-z-index-fix');
			button.closest('.a-controls').removeClass('aActiveMenu');
			menu.removeClass(classname);
			if (overlay) { overlay.hide(); };
			$(document).unbind('click.menuToggleClickHandler'); // Clear out click event
			menu.trigger('afterClosed');
		});

		$('#' + menu.attr('id') + ' .a-options-cancel').live('click',function(e){
			e.preventDefault();
			menu.trigger('toggleClosed');
		});
	}

}

window.apostrophe = new aConstructor();
