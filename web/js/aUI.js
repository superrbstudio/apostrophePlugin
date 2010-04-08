function aUI(target, instance)
{

	if (!$.browser.msie) { // I know we're not supposed to use this.
		$('.a-btn, .a-submit, .a-cancel').each(function() { // inject extra markup for link styles
			var backgroundImage = $(this).css('background-image');

				if(!$(this).hasClass('nobg') && !$(this).data('a-gradient'))
				{
					$(this).data('a-gradient', 1); 
					mozBackgroundImage = backgroundImage + ', -moz-linear-gradient(center bottom, rgba(171,171,171,0.1) 0%, rgba(237,237,237,0.6) 100%	)';
					webkitBackgroundImage = backgroundImage + ', -webkit-gradient(linear, left bottom, left top, color-stop(0, rgba(171,171,171,0.1)), color-stop(1, rgba(237,237,237,0.6)))';
					$(this).css('background-image', mozBackgroundImage);
					$(this).css('background-image', webkitBackgroundImage);			
				}
		
	  });
	}

	// Grab Target if Passed Through
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

	// Instance
	if (typeof instance == 'undefined') // If Not Set
	{
		instance = null;
	}
	
	// INSTANCE CONTROLS
	if (instance == 'history-preview') 
	{ // if we are refreshing while using the history browser we need to set some parameters
		$(target + ".a-controls-item").siblings().show();
		$(target + ".a-controls-item").siblings('.slot').hide();
		$(target + ".a-controls-item").siblings('.edit').hide();
	};
	
	if (instance == 'history-revert') 
	{ // after clicking 'save as current revision'
		$('.a-history-browser, .a-history-preview-notice, .a-page-overlay').hide();		
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
		$('.a-history-browser, .a-history-preview-notice, .a-page-overlay').hide();
		$(target).removeClass('browsing-history');
		$(target).removeClass('previewing-history');
		if ($(target).hasClass('singleton')) // remove instances of a-slot-controls for singleton areas
		{
			$(target + " .a-slot-controls").remove();					
		}
	};	
	
	
	// History Button and History Browser Offset
	$('a.a-history-btn').unbind("click").click(function(event){
		event.preventDefault();	
		$('.a-history-browser').hide();
		$('a.a-history-btn').parents('.a-area').removeClass('browsing-history');
		$('a.a-history-btn').parents('.a-area').removeClass('previewing-history');
		$('.a-page-overlay').show();
		if (!$(this).parents('.a-area').hasClass('browsing-history')) 
		{
			//clear history and show the animator
			$('.a-history-browser .a-history-items').html('<tr class="a-history-item"><td class="date"><img src="\/apostrophePlugin\/images\/a-icon-loader.gif"><\/td><td class="editor"><\/td><td class="preview"><\/td><\/tr>');
			//tell the area that we're browsing history
			$(this).parents('.a-area').addClass('browsing-history');
		}
				
		// Positioning the History Browser
		var y1 = .49, y2 = $(this).offset().top;
		if (parseInt(y1 + y2) > parseInt(y2)) { y2 = parseInt(y1 + y2);	} else { y2 = parseInt(y2); } 
		$('.a-history-browser').css('top',(y2+20)+"px"); //21 = height of buttons plus one margin
		$('.a-history-browser').fadeIn();

		$(this).parent().siblings(':not(.cancel)').hide(); //HIDE OTHER OPTION CHILD LINKS
		$(this).parents('.a-controls').find('.cancel').show().addClass('cancel-history'); //SHOW CANCEL BUTTON And Scope it to History
	});
	
	// Cancel Buttons for History
	$('a.a-cancel-area').unbind("click").click(function(event){
		$(this).parents('.a-controls').children().show();
		$(this).parents('.a-controls').find('.a-area-options').hide();		
		$(this).parent().hide(); //hide parent <li>
	
		if ($(this).parent().hasClass('cancel-history')) //history specific events
		{
			$(this).parents('.a-controls').find('.a-history-options').hide();
			$('.a-history-browser, .a-history-preview-notice, .a-page-overlay').hide();		
			$(this).parent().removeClass('cancel-history');
			$(this).parents('.a-area').removeClass('browsing-history');
			$(this).parents('.a-area').removeClass('previewing-history');
			$(this).parents('.a-controls').find('a.a-history-btn').show();
		}
			
		if ($(this).hasClass('event-default')) 
		{
			//allow default event
			$(this).parent().show(); //unhide cancel button
		}
		else
		{
			//prevent default event
			event.preventDefault();
		}
		
	});
	
	
	// Variants
	$('a.a-variant-options-toggle').click(function(){
		$(this).parents('.a-slots').children().css('z-index','699');
		$(this).parents('.a-slot').css('z-index','799');	
	});

	
	// Disabled Buttons
	$('a.a-disabled').unbind("click").click(function(event){
		event.preventDefault();
	}).attr('onclick','');


	// Cross Browser Opacity Settings
	$('.a-navigation .archived').fadeTo(0,.5); // Archived Page Labels


	// // New Slot Box
	// $('div.a-new-slot').remove();
	// $('div.a-slots').prepend('<div class="a-new-slot"><p>+ Add Slot</p></div>');
	// $('ul.a-controls a.a-add-slot').hover(function(){
	// 	var thisArea = $(this).parents('div.a-area');
	// 	thisArea.addClass('over');
	// 	// We could animate this to slide open, or just toggle the visibility using CSS
	// 	// thisArea.find('div.a-new-slot').animate({
	// 	// 		display: 'block',
	// 	//     height: '25px'
	// 	//   }, 325, function() {
	// 	// 	  });
	// },function(){
	// 	var thisArea = $(this).parents('div.a-area');
	// 	thisArea.removeClass('over');
	// 	// thisArea.find('div.a-new-slot').stop();
	// 	// if (!thisArea.hasClass('add-slot-now'))
	// 	// {
	// 	// 	thisArea.find('div.a-new-slot').css({
	// 	// 		height:'1px',
	// 	// 		display:'none',
	// 	// 	});			
	// 	// }
	// })


	//aContext Slot / Area Controls Setup
	$('.a-controls li:last-child').addClass('last'); //add 'last' class to last option
	$('.a-area-controls .a-controls-item').siblings(':not(.cancel)').css('display', 'block');
	$('.a-area-controls .a-controls-item').children('.a-btn').css('display', 'block');
	$('.a-controls').css('visibility','visible'); //show them after everything is loaded

	
	// Flagging Buttons
	var flagBtn = $('.flag');
	var flagLabel = $('span.flag-label');
	flagLabel.children().insertBefore(flagLabel).remove(); // Clear out flag labels before adding flag labels (for Ajax)
	flagBtn.wrapInner('<span class="flag-label"></span>');
	
	flagBtn.hover(function () {
		$(this).addClass('expanded');
	},function () {
		$(this).removeClass('expanded');
	});	
	
	
	// You can define this function in your site.js to execute code whenenever apostrophe calls aUI();
	// We use this for refreshing progressive enhancements such as Cufon following an Ajax requests.
	if (typeof aOverrides =="function")
	{ 
		aOverrides(); 	
	}

}

$(document).ready(function(){
	aUI();
});