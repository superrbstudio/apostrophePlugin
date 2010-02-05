function aUI(target, instance)
{

	//
	// GLOBAL CONTROLS
	//

	// Basic Button Setup
	$('.a-i').remove(); //Clear out to prevent duplicates
	$.each($('.a-btn'), function() { // inject extra markup for link styles
		txt = $(this).text();
		$(this).html("<span class='a-i'></span><span class='a-b'>"+txt+"</span>");
   });
	
	// Submit Buttons
	$('.a-submit').before("<span class='a-i'></span>"); // Input's cannot contain a-i, a-i goes before it with a wrapper contain input. Input is position absolute on top of a-i

	// Super Cool Flagging Buttons
	var flagBtn = $('.a-flag-btn');
	flagBtn.prepend('<div class="a-flag-btn-label"><span class="a-i"></span><span class="a-l"></span></div>');
	
	flagBtn.children(".a-flag-btn-label").each(function(){
		flagText = $(this).parent().children('.a-b').text();
		$(this).parent().children('.a-b, .a-l').text('');	
		$(this).children('.a-l').text(flagText);
	});
	
	flagBtn.hover(
    function () {
      $(this).addClass('expanded');
    }, 
    function () {
      $(this).removeClass('expanded');
    }
  );

	// TARGETING THING I'M TRYING OUT
	// this is super rough, but the idea is being able to scope the UI initialization for Ajax calls etc.

	if (typeof target == 'undefined') // If Not Set
	{
		target = '';
	}
	else if (typeof target == 'object') // If jQuery object get id
	{
		target = "#"+$(target).attr('id')+" ";
	}
	else // probably a string
	{
		target = target+" ";
	}

	if (typeof instance == 'undefined') // If Not Set
	{
		instance = null;
	}
	
	//
	// TARGETTED CONTROLS
	//

	var addSlotButton = $(target+'ul.a-area-controls a.a-add.slot');
	
	if (addSlotButton.hasClass('addslot-now')) // init_a_controls was resetting add slot buttons in some scenarios when we didn't want it to	
	{ 
		addSlotButton.prev().css('display','none');
	} 
	else
	{
		addSlotButton.siblings('.a-area-options').css('display','none');
	}
	
		//
	// INSTANCE CONTROLS
	//

	if (instance == 'history-preview') 
	{ // if we are refreshing while using the history browser we need to set some parameters
		$(target + ".a-controls-item").siblings().show();
		$(target + ".a-controls-item").siblings('.slot').hide();
		$(target + ".a-controls-item").siblings('.edit').hide();
	};
	
	if (instance == 'history-revert') 
	{ // after clicking 'save as current revision'
		$('.a-history-browser, .a-history-preview-notice, .a-page-overlay').css('display','none');		
		$(target + ".a-controls-item").siblings().show();
		$(target + ".a-controls-item").siblings('.cancel').hide();
		$(target).removeClass('browsing-history');
		$(target).removeClass('previewing-history');
		if ($(target).hasClass('singleton')) // remove instances of a-slot-controls for singleton areas
		{
			$(target + " .a-slot-controls").remove();					
		}
	};
	
	if (instance == 'history-cancel') 
	{ // clicking cancel after previewing history item
		$('.a-history-browser, .a-history-preview-notice, .a-page-overlay').css('display','none');				
		$(target).removeClass('browsing-history');
		$(target).removeClass('previewing-history');
		if ($(target).hasClass('singleton')) // remove instances of a-slot-controls for singleton areas
		{
			$(target + " .a-slot-controls").remove();					
		}
	};

	if (instance == 'add-slot')
	{
		$(target + '.cancel-addslot').hide().removeClass('cancel-addslot');
	};

	//
	// PK-CONTROLS BUTTON EVENTS
	//

	$('a.a-add.slot').unbind("click").click(function(event){
		event.preventDefault();
		$(this).hide(); //HIDE SELF
		$(this).prev('.a-i').hide(); //HIDE SELF BG
		$(this).siblings('.a-area-options.slot').fadeIn(); //SHOW AREA OPTIONS FOR SLOTS
		$(this).parent().siblings(':not(.cancel)').hide(); //HIDE OTHER OPTION CHILD LINKS
		$(this).parent().addClass('addslot-now');
		$(this).parent().siblings('.a-controls-item.cancel').show().addClass('cancel-addslot'); //SHOW CANCEL BUTTON
	});
	
	$('a.a-history').unbind("click").click(function(event){

		event.preventDefault();	
			
		$('.a-history-browser').hide();
		$('a.a-history').parents('.a-area').removeClass('browsing-history');
		$('a.a-history').parents('.a-area').removeClass('previewing-history');
		$('.a-page-overlay').show();
		
		if (!$(this).parents('.a-area').hasClass('browsing-history')) 
		{
			//clear history and show the animator
			$('.a-history-browser .a-history-items').html('<tr class="a-history-item"><td class="date"><img src="\/apostrophePlugin\/images\/a-icon-loader-ani.gif"><\/td><td class="editor"><\/td><td class="preview"><\/td><\/tr>');
			//tell the area that we're browsing history
			$(this).parents('.a-area').addClass('browsing-history');
		}
				
		var y1 = .49, y2 = $(this).offset().top;

		if (parseInt(y1 + y2) > parseInt(y2)) { y2 = parseInt(y1 + y2);	} else { y2 = parseInt(y2); } 

		$('.a-history-browser').css('top',(y2+20)+"px"); //21 = height of buttons plus one margin
		$('.a-history-browser').fadeIn();

		$(this).parent().siblings(':not(.cancel)').hide(); //HIDE OTHER OPTION CHILD LINKS
		$(this).parents('.a-controls').find('.cancel').show().addClass('cancel-history'); //SHOW CANCEL BUTTON And Scope it to History

	});
	
	$('a.a-cancel').unbind("click").click(function(event){

		$(this).parents('.a-controls').children().show();
		$(this).parents('.a-controls').find('.a-area-options').hide();		
		$(this).parent().hide(); //hide parent <li>

		if ($(this).parent().hasClass('cancel-history')) //history specific events
		{
			$(this).parents('.a-controls').find('.a-history-options').hide();
			$(this).parents('.a-controls').find('a.a-history').show();
			$(this).parents('.a-controls').find('a.a-history').prev('.a-i').show();
			$(this).parent().removeClass('cancel-history');
			$('.a-history-browser, .a-history-preview-notice, .a-page-overlay').css('display','none');		
			$(this).parents('.a-area').removeClass('browsing-history');
			$(this).parents('.a-area').removeClass('previewing-history');
		}
		
		if ($(this).parent().hasClass('cancel-addslot')) //add slot specific events
		{
			$(this).parents('.a-controls').find('a.a-add.slot').show();
			$(this).parents('.a-controls').find('a.a-add.slot').prev('.a-i').show();
			$('.addslot-now').removeClass('addslot-now');
			$(this).parent().removeClass('cancel-addslot');			
		}

		if ($(this).hasClass('event-default')) 
		{ //allow default event
			
			$(this).parent().show(); //unhide cancel button
			
		}
		else
		{
			//prevent default event
			event.preventDefault();
		}
		
	});

	
	// Disabled Buttons
	$('a.a-disabled').unbind("click").click(function(event){
		event.preventDefault();
	}).attr('onclick','');

	//
	// Cross Browser Opacity Settings
	//
	aUIOpacity()
	$('.a-page-overlay').fadeTo(0,.85).hide(); // Modal Box Overlay
	$('.a-archived-page').fadeTo(0,.5); // Archived Page Labels
	//
	//
	//

	//
	//aContext Slot / Area Controls Setup
	//
	$('.a-controls li:last-child').addClass('last'); //add 'last' class to last option
	$('.a-area-controls .a-controls-item').siblings(':not(.cancel)').css('display', 'block');
	$('.a-area-controls .a-controls-item').children('.a-btn').css('display', 'block');
	$('.a-controls').css('visibility','visible'); //show them after everything is loaded
	//
	//
	//
	
	aOverrides();
}

function aUIUnButton()
{
	$.each($('.a-btn'), function() { // inject extra markup for link styles
		txt = $(this).children('span.a-b').text();
		$(this).html(txt);
   });
}

function aUIOpacity(uiOpacity)
{
	if (typeof uiOpacity == 'undefined') // If Not Set, use Default Value
	{
		uiOpacity = .65;
	}
	//Crossbrowser opacity
	$('.a-i, #the-apostrophe').fadeTo(0,uiOpacity); //Button Background Color
}

function aOverrides()
{
	// Override this function in site.js to execute code when a calls aUI();
}

$(document).ready(function(){
	aUI();
});