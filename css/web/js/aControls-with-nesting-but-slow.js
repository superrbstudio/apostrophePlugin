// Copyright 2009 P'unk Avenue LLC. Released under the MIT license.
// See http://www.symfony-project.org/plugins/apostrophePlugin and
// http://www.punkave.com/ for more information.

// aMultipleSelect transforms multiple selection elements into
// attractive, user-friendly replacements. 

// aRadioSelect transforms single-select elements into a set of pill 
// buttons (you probably won't always want this, it's handy when used 
// selectively). 

// aSelectToList transforms single-select elements into
// <ul> lists in which the list items set the underlying, hidden
// select element and then immediately submit the form. There is also
// special support for lists of tags with a count in parentheses
// following the tag name in the label, like so: 
//
// <option value="foo">foo (5)</option>

// Now accepts options. Valuable since you probably want to set the
// choose-one option to something like "Select to Add" in a
// progressive-enhancement scenario where you haven't manually provided
// a first option that is actually a label

function aMultipleSelectAll(options)
{
	if (!options)
	{
		options = {};
	}
  $(document).ready(
    function() {
      aMultipleSelect('body', options);
    }
  );
}

// Transforms multiple select elements into a much more attractive
// and user-friendly control with a pulldown on the left and links
// to remove already-selected choices on the right

function aMultipleSelect(target, options)
{
  $(target + ' select[multiple]').each(
    function(i) {
      var name = $(this).attr('name');
      var id = $(this).attr('id');
      var values = [];
      var labels = [];
      var selected = [];
      var j;

			// By default the first option is assumed to be a "choose one" label and cannot actually
			// be chosen. If you are upgrading multiple select elements that weren't designed expressly
			// for this purpose, this is not great. However, if you specify an explicit 'choose-one'
			// text, a custom first option will be inserted with that text. Recommended for 
			// true progressive enhancement scenarios.
			if (options['choose-one'])
			{
				values.push('');
				labels.push(options['choose-one']);
				selected.push(false);
			}
			
      for (j = 0; (j < this.options.length); j++)
      {
        var option = this.options[j];
        values.push(option.value);
        labels.push(option.innerHTML);
        // Firefox is a little cranky about this,
        // try it both ways
        selected.push(option.getAttribute('selected') || option.selected);
      }
      if (id === '')
      {
        // Hopefully unique
        id = name;
      }
      var html = "<div class='a-multiple-select' id='" + id + "'>";
      html += "<select name='select-" + name + "'>";
      html += "</select>\n";
      for (j = 0; (j < values.length); j++)
      {
        html += "<input type='checkbox' name='" + name + "'";
        if (selected[j])
        {
          html += " checked";
        }
        html += " value=\"" + aHtmlEscape(values[j]) + 
          "\" style='display: none'/>";
      }
      html += "<ul class='a-multiple-select-list'>";
      if (!options['remove'])
      {
        options['remove'] = ' <span>Remove</span>';
      }
      for (j = 0; (j < values.length); j++)
      {
        html += "<li style='display: none'><a href='#' title='Remove this item' class='a-multiple-select-remove'>" + 
          labels[j] + 
          options['remove'] + "</a></li>\n";
      }
      html += "</ul>\n";
      // Handy for clearing floats
      html += "<div class='a-multiple-select-after'></div>\n";
      html += "</div>\n";
      $(this).replaceWith(html);
      var select = $("#" + id + " select");
      var k;
      var items = $('#' + id + ' ul li');
      for (k = 0; (k < values.length); k++)
      {
        $(items[k]).data("boxid", values[k]);
        $(items[k]).click(function() { update($(this).data("boxid")); return false; });
      }
      function update(remove)
      {
        var ul = $("#" + id + " ul");
        var select = $("#" + id + " select")[0];
        var index = select.selectedIndex;
        var value = false;
        if (index > 0)
        {
          value = select.options[index].value;
        }
        var boxes = $('#' + id + " input[type=checkbox]");
        for (k = 1; (k < values.length); k++)
        {
          if (boxes[k].value === remove)
          {
            boxes[k].checked = false;
          }
          if (boxes[k].value === value)
          {
            boxes[k].checked = true;
          }
        }
        var items = $('#' + id + ' ul li');
        var k;
        var html;
        for (k = 0; (k < values.length); k++)
        {
          if (boxes[k].checked)
          {
            $(items[k]).show();
          }
          else
          {
            $(items[k]).hide();
            html += "<option ";
            if (k == 0)
            {
              // First option is "pick one" message
              html += " selected ";
            }
            html += "value=\"" + aHtmlEscape(values[k]) + "\">" +
              labels[k] + "</option>";
          }
        }
        // Necessary in IE
        $(select).replaceWith("<select name='select-" + name + "'>" + html + "</select>");
        $("#" + id + " select").change(function() { update(false); });
      }
      function aHtmlEscape(html)
      {
        html = html.replace('&', '&amp;'); 
        html = html.replace('<', '&lt;'); 
        html = html.replace('>', '&gt;'); 
        html = html.replace('"', '&quot;'); 
        return html;
      }  
      update(false);
    }
  );
}

// Transforms select elements matching the specified selector.  
// You won't want to do this to every select element in your form,
// so give them a class like .a-radio-select and use a class selector like 
// .aRadioSelect (but not .a-radio-select-container, which we use for
// the span that encloses our toggle buttons). Make sure your selector is 
// specific enough not to match other elements as well.
//
// We set the a-radio-option-selected class on the currently selected link
// element.
//
// If the autoSubmit option is true, changing the selection immediately
// submits the form. There are no guarantees that will work with
// wacky AJAX forms. It works fine with normal forms.
//
// Note the getOption calls that allow the use of custom templates.

function aRadioSelect(target, options)
{
  $(target).each(
    function(i) {
			// Don't do it twice to the same element
			if ($(this).data('a-radio-select-applied'))
			{
				return;
			}
      $(this).hide();
			$(this).data('a-radio-select-applied', 1);
      var html = "";
      var links = "";
      var j;
			var total = this.options.length;
      linkTemplate = getOption("linkTemplate",
        "<a href='#'>_LABEL_</a>");
      spanTemplate = getOption("spanTemplate",
        "<span class='a-radio-select-container'>_LINKS_</span>");
      betweenLinks = getOption("betweenLinks", " ");
      autoSubmit = getOption("autoSubmit", false);
      for (j = 0; (j < this.options.length); j++)
      {
        if (j > 0)
        {
          links += betweenLinks;
        }
        links += 
          linkTemplate.replace("_LABEL_", $(this.options[j]).html());
      }
      span = $(spanTemplate.replace("_LINKS_", links));
      var select = this;
      links = span.find('a');
      $(links[select.selectedIndex]).addClass('a-radio-option-selected');
      links.each(
        function (j)
        {
          $(this).data("aIndex", j);
					$(this).addClass('option-'+j);
					
					if (j == 0)
					{
						$(this).addClass('first');
					}
					
					if (j == total-1)
					{
						$(this).addClass('last');						
					}
          $(this).click(
            function (e)
            {
              select.selectedIndex = $(this).data("aIndex");
              var parent = ($(this).parent());
              parent.find('a').removeClass('a-radio-option-selected'); 
              $(this).addClass('a-radio-option-selected'); 
              if (autoSubmit)
              {
                select.form.submit();
              }
              return false;
            }
          );
        }
      );
      $(this).after(span);
      function getOption(name, def)
      {
        if (name in options)
        {
          return options[name];
        }
        else
        {
          return def;
        }
      }
    }
  );
}

// Simple usage example
// aSelectToList('#a-media-type', {} );

// Usage example where the labels are tags with usage counts, with the
// both alphabetical and popular tag lists present and custom labels:

// aSelectToList('#a-media-tag', 
//   { 
//     tags: true,
//     // MUST contain an anchor tag so our code can bind the click 
//     currentTemplate: "<h5>_LABEL_ <a href='#'><font color='red'><i>x</i></font></a></h5>",
//     popularLabel: "<h4>Popular Tags</h4>",
//     popular: <?php echo aMediaTools::getOption('popular_tags') ?>,
//     alpha: true,
//     // If this contains an 'a' tag it gets turned into a toggle 
//     allLabel: "<h4><a href='#'>All Tags</a></h4>",
//     itemTemplate: "_LABEL_ <span>(_COUNT_)",
//     allVisible: false,
//     all: true
//   });

function aSelectToList(selector, options)
{
  $(selector).each(
    function(i) {
      $(this).hide();
      var total = this.options.length;
      var html = "<ul>";
      var selectElement = this;
      var tags = options['tags'];
      var popular = false;
      var alpha = false;
      var all = true;
      var itemTemplate = options['itemTemplate'];
      if (!itemTemplate)
      {
        if (tags)
        {
          itemTemplate = "_LABEL_ <span>(_COUNT_)";
        }
        else
        {
          itemTemplate = "_LABEL_";
        }
      }
      var currentTemplate;
      if (tags)
      {
        popular = options['popular'];
        all = options['all'];
        alpha = options['alpha'];
      }
      if (options['currentTemplate'])
      {
        currentTemplate = options['currentTemplate'];
      }
      else
      {
        currentTemplate = "<h5>_LABEL_ <a href='#'><font color='red'><i>x</i></font></a></h5>";
      }
      var data = [];
      var re = /^(.*)?\s+\((\d+)\)\s*$/;
      index = -1;
      for (i = 0; (i < total); i++)
      {
        var html = this.options[i].innerHTML;
        if (tags)
        {
          var result = re.exec(html);
          if (result)
          {
            data.push({ 
              label: result[1], 
              count: result[2], 
              value: this.options[i].value
            });
          }
          else
          {
            continue;
          }
        } 
        else
        {
          // Test... carefully... for a non-empty string
          if ((this.options[i].value + '') !== '')
          {
            data.push({
              label: html,
              value: this.options[i].value
            });
          }
          else
          {
            continue;
          }
        }
        if (selectElement.selectedIndex == i)
        {
          // Don't let skipped valueless entries throw off the index
          index = data.length - 1;
        }
      }
      // Make our after() calls in the reverse order so we get
      // the correct final order
      if (all)
      {
        var sorted = data.slice();
        if (alpha)
        {
          sorted = sorted.sort(sortItemsAlpha);
        }
				var lclass = options['listAllClass'];
        var allList = appendList(sorted, lclass);
        if (!options['allVisible'])
        {
          allList.hide();
        }
        if (options['allLabel'])
        {
          var allLabel = $(options['allLabel']);
          if (allLabel)
          {
            var a = allLabel.find('a');
            if (a)
            {
              a.click(function() 
              {
                allList.toggle("slow");
                return false;
              });
            }
          }
          $(selectElement).after(allLabel);
        }
      }
      if (popular)
      {
        var sorted = data.slice();
        sorted = sorted.sort(sortItemsPopular);
        sorted = sorted.slice(0, popular);
        appendList(sorted, options['listPopularClass']);
        if (options['popularLabel'])
        {
          $(selectElement).after($(options['popularLabel']));
        }
      }
      if (index >= 0)
      {
        var current = currentTemplate;
        current = current.replace("_LABEL_", data[index].label);
        current = current.replace("_COUNT_", data[index].count);
        current = $(current);
        var a = current.find('a');
        a.click(function()
        {
          selectElement.selectedIndex = 0;
          $(selectElement.form).submit();
          return false;
        });
        $(selectElement).after(current);
      }
      function appendList(data, c)
      {
        var list = $('<ul></ul>');
        if (c)
        {
          list.addClass(c);
        }
        for (i = 0; (i < data.length); i++)
        {
          var item = itemTemplate;
          if (tags)
          {
            item = item.replace("_COUNT_", data[i].count);
          }
          item = item.replace("_LABEL_", data[i].label);
          var liHtml = "<li><a href='#'>" + item + "</a></li>";
          var li = $(liHtml);
          var a = li.find('a');
          a.data('label', data[i].label);
          a.data('value', data[i].value);
          a.click(function() {
            $(selectElement).val($(this).data('value'));
            $(selectElement.form).submit();
            return false;
          });
          list.append(li);
        }
        $(selectElement).after(list); 
        return list;
      }
    }
  );
  function sortItemsAlpha(a, b)
  {
    x = a.label.toLowerCase();
    y = b.label.toLowerCase();
    // JavaScript has no <> operator 
    return x > y ? 1 :x < y ? -1 : 0;
  }
  function sortItemsPopular(a, b)
  {
    // Most popular should appear first
    return b.count - a.count;
  }
}

// Labeling input elements compactly using their value attribute (i.e. search fields)

// You have an input element. You want it to say 'Search' or a similar label, 
// provided its initial value is empty. On focus, if the label is present, it should clear.
// If they defocus it and it's empty, you want the label to come back. Here you go.

function aInputSelfLabel(selector, label)
{
	var aInput = $(selector);
	
	aInput.each(function() {
		setLabelIfNeeded(this);
	});

	aInput.focus(function() {
		clearLabelIfNeeded(this);
	});

	aInput.blur(function() {
		setLabelIfNeeded(this);
	});
	
	function setLabelIfNeeded(e)
	{
		var v = $(e).val();
		if (v === '')
		{
			$(e).val(label).addClass('a-default-value');				
		}
	}
	function clearLabelIfNeeded(e)
	{
		var v = $(e).val();
		if (v === label)
		{
			$(e).val('').removeClass('a-default-value');
		}
	}
}

// Got a checkbox and a set of related controls that should only be enabled
// when the checkbox is checked? Here's your answer

// The optional hideItemsSelector contains items that should be hidden rather than disabled.
// You can pass undefined for itemsSelector if you are only interested in hiding items. This
// combination is useful if you want to fully disable a datepicker with an input field
// (disable-able) and a calendar icon (not disable-able, but hide-able).

// Both selector arguments are optional. To skip itemsSelector, pass undefined (not null)
// for that argument.

// ACHTUNG: don't forget about hidden form elements you might be disabling (Symfony adds them to the last row
// in a form). Write your selectors carefully, check for over-generous selectors when forms seem broken.

// Nesting is permitted. If an outer checkbox would enable a child of an inner checkbox, it first checks
// a nesting counter to ensure it is not also disabled due to the inner checkbox.

// aCheckboxDisables does the opposite, disabling the indicated items when the
// checkbox is checked.

function aCheckboxEnables(boxSelector, itemsSelector, hideItemsSelector)
{
	aCheckboxEnablesBody(boxSelector, itemsSelector, hideItemsSelector, true);
}

function aCheckboxDisables(boxSelector, itemsSelector, hideItemsSelector)
{
	aCheckboxEnablesBody(boxSelector, itemsSelector, hideItemsSelector, false);
}

function aCheckboxEnablesBody(boxSelector, itemsSelector, hideItemsSelector, enable)
{
	$(boxSelector).data('aCheckboxEnablesItemsSelector', itemsSelector);
	$(boxSelector).data('aCheckboxEnablesHideItemsSelector', hideItemsSelector);
	$(boxSelector).data('aCheckboxEnablesEnable', enable);
	$(boxSelector).click(function() 
	{
		update(this);
	});

	function bumpEnabled(selector)
	{
		var enabled = [];
		$(selector).each(function() { 
			var counter = $(this).data('aCheckboxEnablesEnableCounter');
			if (counter < 0)
			{
				counter++;
				$(this).data('aCheckboxEnablesEnableCounter', counter);
			}
			if (counter >= 0)
			{
				enabled.push(this);
			}
		});
		return enabled;
	}

	function bumpDisabled(selector)
	{
		$(selector).each(function() { 
			var counter = $(this).data('aCheckboxEnablesEnableCounter');
			if (counter === undefined)
			{
				counter = 0;
			}	
			counter--;
			$(this).data('aCheckboxEnablesEnableCounter', counter);
		});
	}
	
	function update(checkbox)
	{
		var itemsSelector = $(checkbox).data('aCheckboxEnablesItemsSelector');
		var hideItemsSelector = $(checkbox).data('aCheckboxEnablesHideItemsSelector');
		var enable = $(checkbox).data('aCheckboxEnablesEnable');
		var checked = $(checkbox).attr('checked');
		var state = checked;
		if (!enable)
		{
			state = !checked;
		}
		if (state)
		{
			if (itemsSelector !== undefined)
			{
				var enabled = bumpEnabled(itemsSelector);
				for (var i in enabled)
				{
					var item = enabled[i];
					$(item).removeAttr('disabled');
				}
			}
			if (hideItemsSelector !== undefined)
			{
				var shown = bumpEnabled(hideItemsSelector);
				for (var i in shown)
				{
					var item = shown[i];
					$(item).show();
				}
			}
		}
		else
		{
			if (itemsSelector !== undefined)
			{
				bumpDisabled(itemsSelector);
				$(itemsSelector).attr('disabled', 'disabled');
			}
			if (hideItemsSelector !== undefined)
			{
				bumpDisabled(hideItemsSelector);
				$(hideItemsSelector).hide();
			}
		} 
	}
	// At DOMready so we can affect controls created by js widgets in the form
	$(function() {
		$(boxSelector).each(function() { update(this) });
	});
}

// Similar to the above, but for select options. itemsSelectors is a hash
// of option values pointing to item selectors. On change all of the items
// selectors for the other options get disabled, then the items selector for
// the selected option (if any) gets enabled. Great for enabling a text field
// when "Other" is chosen from an "Institution Type" menu.

// If desired a second hash of option values pointing to item selectors that
// should be shown/hidden rather than enabled/disabled can also be passed.

// Both selector arguments are optional. To skip itemsSelectors, pass undefined (not null)
// for that argument.

function aSelectEnables(selectSelector, itemsSelectors, hideItemsSelectors)
{
	$(selectSelector).data('aSelectEnablesItemsSelectors', itemsSelectors);
	$(selectSelector).data('aSelectEnablesHideItemsSelectors', hideItemsSelectors);
	$(selectSelector).change(function() {
		update(this);
	});

	function update(select)
	{
		var itemsSelectors = $(select).data('aSelectEnablesItemsSelectors');
		var hideItemsSelectors = $(select).data('aSelectEnablesHideItemsSelectors');
		if (itemsSelectors !== undefined)
		{
			for (var option in itemsSelectors)
			{
				$(itemsSelectors[option]).attr('disabled', 'disabled');
			}
			var option = select.value;
			if (itemsSelectors[option])
			{
				$(itemsSelectors[option]).removeAttr('disabled');
			}
		}
		if (hideItemsSelectors !== undefined)
		{
			for (var option in hideItemsSelectors)
			{
				$(hideItemsSelectors[option]).hide();
			}
			var option = select.value;
			if (hideItemsSelectors[option])
			{
				$(hideItemsSelectors[option]).show();
			}
		}
	}
	$(function() {
		$(selectSelector).each(function() { update(this) });
	});
}


function aBusy(selector)
{
	$(selector).each(function() {
		$(this).data('a-busy-html', $(this).html());
		$(this).html("<img src=\"/apostrophePlugin/images/a-icon-loader.gif\"/>");
	});
}

function aReady(selector)
{
	$(selector).each(function() {
		$(this).html($(this).data('a-busy-html'));
	});
}

// Select elements with only one preselected <option> are better presented as static content.
// This gives prettier results with a generic echo $form for things like RSVP forms that
// don't always have more than one possible new state.

// Usage: aSelectToStatic('body') or something less promiscuous

function aSelectToStatic(selector)
{
	$(selector).find('select').each(function() {
		if ((this.options.length == 1) && (this.options[0].selected))
		{
			$(this).after('<span class="a-static-select">' + this.options[0].innerHTML + '</span>');
			$(this).hide();
		}
	});
}

