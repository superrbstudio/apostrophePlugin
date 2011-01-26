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

	// Utility: console.log wrapper prevents JS errors 
	// if we leave an apostrophe.log call hanging out in our code someplace
	this.log = function(output)
	{ 
		if (window.console && console.log) {
			console.log(output);
		};
	}

	// Utility: Click an element once and convert it to a span
	// Useful for turning an <a> into a <span>
	this.aClickOnce = function(selector)
	{
		var selector = $(selector);
		selector.unbind('click.aClickOnce').bind('click.aClickOnce', function(){   
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
}

window.apostrophe = new aConstructor();


