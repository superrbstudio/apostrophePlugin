function timepicker2(selector, options_array)
{	
	$(selector).each(function()
	{
		var optionClass = options_array['time-class'];
		if (typeof(optionClass) == 'undefined')
		{
			optionClass = 'time-item';
		}

		var minutesIncrement = options_array['minutes-increment'];
		if (typeof(minutesIncrement) == 'undefined')
		{
			minutesIncrement = 1;
		}

		var hoursIncrement = options_array['hours-increment'];
		if (typeof(hoursIncrement) == 'undefined')
		{
			hoursIncrement = 1;
		}

		var twentyFourHour = options_array['twenty-four-hour'];
		if (typeof(twentyFourHour) == 'undefined')
		{
			twentyFourHour = true;
		}


		var timeinput = $(this);
		var picker = $('<input />');
		var options;
		var id = 'timepicker-' + selector + '-' + (Math.floor(Math.random() * 9999));
		var optionsId = 'options-' + (Math.floor(Math.random() * 9999));

		// progressively E-N-H-A-N-C-E
		if (!timeinput.hasClass(optionClass + '-enabled'))
		{
			replaceInput();
		}
	
		function replaceInput()
		{
			picker.attr({'id': id, 'autocomplete': 'off'});
			picker.val(getTimeFromForm());
			
			options = $('<div />');
			options.attr('id', optionsId);
			options.hide();
			options.addClass('time-items');
			$('body').append(options);
		
			for (var hour = 0; (hour < 24); hour += hoursIncrement)
			{
				for (var min = 0; (min < 60); min += minutesIncrement)
				{
					var option = $('<div />');
					var timeStr = prettyTime(hour, min);
					option.addClass(optionClass);
					if (timeStr == picker.val())
					{
						option.addClass(optionClass + '-selected');
					}
					option.text(timeStr);
					option.click(function()
					{
						picker.val($(this).text());
						picker.change();
						options.hide();
					});
					options.append(option);
				}
			}
	
			picker.click(function() {
				var offset = picker.offset();
				options.css({
					'position': 'absolute',
					left: offset.left,
					top: (offset.top + picker.outerHeight()),
					height: 100 + 'px',
					overflow: 'auto'
					});
				options.show();
				
				time = parseTime(picker.val());
				time = prettyTime(time.hours, time.minutes);
				if (time)
				{
					var scroll = false;
					options.children().each(function() {
					 	if (closestTime($(this).html(), time))
							scroll = $(this);
					});
					options.scrollTop(0);
					if (scroll)
						options.scrollTop(scroll.position().top);
				}
			});
			
			picker.change(function()
			{
				commitToForm(picker.val());
			});
			picker.blur(function()
			{
				picker.change();
			});
			
			$(this).mousedown(_checkExternalClick);

			function _checkExternalClick(event) {
	 			var target = $(event.target);
	 			if ((target.attr('id') != options.attr('id')) && !(target.hasClass(optionClass)))
	    		{
		    		options.hide();
		    	}
			}

			function defaultSelection()
			{
				var selection = options.find('.' + optionClass + '-selected');
				if (selection.length == 0)
				{
					var first = $(options.children()[0]);
					first.addClass(optionClass + '-selected');
				}
				
				return false;
			}
			
			function commitSelection()
			{
				var selection = options.find('.' + optionClass + '-selected');
			
				picker.val(selection.text());
				picker.change();
				
				options.scrollTop(0);
				options.scrollTop(selection.position().top);
			}
			
			function nextSelection()
			{
				if (!defaultSelection())
				{
					var selected = options.find('.' + optionClass + '-selected');
					
					if (selected.next().length != 0)
					{
						selected.removeClass(optionClass + '-selected');
						var next = selected.next();
						next.addClass(optionClass + '-selected');
					}
				}
				commitSelection();
			}
			
			function previousSelection()
			{
				if (!defaultSelection())
				{
					var selected = options.find('.' + optionClass + '-selected');
					
					if (selected.prev().length != 0)
					{
						selected.removeClass(optionClass + '-selected');
						var prev = selected.prev();
						prev.addClass(optionClass + '-selected');
					}
				}
				commitSelection();
			}

			picker.keydown(function(event){    	  		
    	  		switch(event.keyCode)
    	  		{
    	  			case 40:
    	  				event.preventDefault();
    	  				nextSelection();
    	  				break;
    	  			case 38:
    	  				event.preventDefault();
    	  				previousSelection();
    	  				break;
    	  			default:
    	  				options.hide();
    	  		}
    	  		
    	  		event.stopPropagation();
    		});
	
			// insert the new picker and show it
			timeinput.addClass(optionClass + '-enabled');
			timeinput.wrapInner('<div class="a-hidden"></div>');
			timeinput.prepend(picker);
		}
				
		function commitToForm(text)
		{
			var time = parseTime(text);
			
			if (time)
			{				
				var inputs = timeinput.find('select');
				$(inputs[0]).val(time.hours);
				$(inputs[1]).val(time.minutes);
				$(inputs[0]).change();
				$(inputs[1]).change();
			}
		}
		
		function getTimeFromForm()
		{
			var inputs = timeinput.find('select');
			
			// "I haven't picked a time yet" is a real thing
			if ($(inputs[0]).val() == '')
			{
				return '';
			}
			if ($(inputs[1]).val() == '')
			{
				return '';
			}
			
			return prettyTime($(inputs[0]).val(), $(inputs[1]).val());
		}
		
		function closestTime(optionValue, inputValue)
		{
			optionValue = parseTime(optionValue);
			inputValue = parseTime(inputValue);
			
			if (!inputValue)
				return false;
			
			if (optionValue.hours == inputValue.hours)
			{
				if (optionValue.minutes == inputValue.minutes)
					return true;
				
				var diff = optionValue.minutes - inputValue.minutes;
				return ((diff > 0) && (diff < minutesIncrement));
			}
			else if ((optionValue.hours - inputValue.hours) == 1)
			{
				optionValue.minutes = optionValue.minutes + 60;
				
				var diff = optionValue.minutes - inputValue.minutes;
				
				return ((diff > 0) && (diff < minutesIncrement));
			}
			return false;
		}
		
		function parseTime(text, hand)
		{
			retVal = validateTimeString(text);
			
			if (!retVal) {
				return false;
			}
			
			if (hand == 'hours')
			{
				return retVal.hours;
			}
			
			if (hand == 'minutes')
			{
				return retVal.minutes;
			}

			return retVal;
		}
	
		function validateTimeString(time)
		{
			var components = time.match(/(\d\d?)(:\d\d?)?\s*(am|pm)?/i);
			if (components != null)
			{	
				timeArray = new Array();
				timeArray.push(Math.floor(components[1]));
				if (typeof(components[2]) == 'undefined')
				{
					components[2] = '0';
				}
				timeArray.push(Math.floor(components[2].replace(/^:/, '')));
				if (typeof(components[3]) == 'undefined')
				{
					timeArray.push('AM');
				}
				else
				{
					timeArray.push(components[3]);
				}
				
				if (timeArray[2].match(/pm/i) != null)
				{
					if (timeArray[0] != 12)
					{
						timeArray[0] = timeArray[0] + 12;
					}
				}
				else if (timeArray[0] == 12)
				{
					timeArray[0] = 0;
				}

				
				if ((timeArray[0] < 0) || (timeArray[0] > 23))
				{
					return false;
				}
				
				if ((timeArray[1] < 0) || (timeArray[1] > 59))
				{
					return false;
				}
				
				var retVal = {};
				retVal['hours'] = timeArray[0];
				retVal['minutes'] = timeArray[1];
				
				return retVal;
			}
			
			return false;
		}
	
		function prettyTime(hour, min)
		{
		  var timeStr = '';
		  
		  if (!twentyFourHour)
		  {
		  	var suffix = 'AM';
		  	if (hour > 11)
		  	{
		  		suffix = 'PM';
		  	}
		  	if (hour > 12)
		  	{
		  		hour = hour - 12;
		  	}
		  	if (hour == 0)
		  	{
		  		hour = 12;
		  	}
		  	timeStr = ' ' + suffix;
		  }
		  
		  if (hour < 10)
		  {
		  	hour = '0' + hour;
		  }
		  if (min < 10)
		  {
		  	min = '0' + min;
		  }
		  
		  return hour + ':' + min + timeStr;
		}
	});
}
