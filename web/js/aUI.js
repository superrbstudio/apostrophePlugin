function aUI(target)
{
	
	// Grab Target if Passed Through
	if (typeof(target) == 'undefined') // If Not Set
	{
		target = '';
	}
	else if (typeof(target) == 'object') // If jQuery object get id
	{
		target = "#"+ target.attr('id') +" ";
	}
	else // probably a string
	{
		target = target+" ";
	}

	if (!$.browser.msie) { // I know we're not supposed to use this.

		var aBtn = $(target+' .a-btn, ' + target + ' .a-submit, ' + target + ' .a-cancel');
		aBtn.each(function() {

			var backgroundImage = $(this).css('background-image');

			// Setup Button Gradient Backgrounds
			// We have to do it this way to preserve the icons as background images
			if(!$(this).hasClass('nobg') && !$(this).data('a-gradient'))
			{
				$(this).data('a-gradient', 1); 
				mozBackgroundImage = backgroundImage + ', -moz-linear-gradient(center bottom, rgba(171,171,171,0.1) 0%, rgba(237,237,237,0.6) 100%	)';
				webkitBackgroundImage = backgroundImage + ', -webkit-gradient(linear, left bottom, left top, color-stop(0, rgba(171,171,171,0.1)), color-stop(1, rgba(237,237,237,0.6)))';
				$(this).css('background-image', mozBackgroundImage);
				$(this).css('background-image', webkitBackgroundImage);			
			}
		
			// Setup Flag Buttons
			if($(this).hasClass('flag'))
			{
				if (!$(this).children('span').size())
				{
					$(this).attr('title','').wrapInner('<span class="flag-label"></span>');		
				}

				$(this).hover(function () {
					$(this).addClass('expanded');
				},function () {
					$(this).removeClass('expanded');
				});	
			}
		
	  });
	}

	// Area History Buttons
	$('a.a-history-btn').unbind("click").click(function(event){
		event.preventDefault();			
		aCloseHistory();
		aBrowseHistory($(this).parents('div.a-area'));
	});
	
	// Close History Browser
	$('#a-history-close-button, #a-history-heading-button').click(function(){
		aCloseHistory();
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
	$('.a-nav .a-archived-page').fadeTo(0,.5); // Archived Page Labels

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
	$('.a-controls li:last-child').addClass('last'); // Add 'last' Class To Last Option
	$('.a-controls').css('visibility','visible'); // Display Controls After They Have Been Styled
	
	// You can define this function in your site.js to execute code whenenever apostrophe calls aUI();
	// We use this for refreshing progressive enhancements such as Cufon following an Ajax request.
	if (typeof(aOverrides) =="function")
	{ 
		aOverrides(); 	
	}
}

function aIE6(authenticated, message)
{
	// This is called within a conditional comment for IE6 in Apostrophe's layout.php
	if (authenticated)
	{
		$(document.body).addClass('ie6').prepend('<div id="ie6-warning"><h2>' + message + '</h2></div>');	
	}

	// Misc IE6 enhancements we want to happen
	$('input[type="checkbox"]').addClass('checkbox');
	$('input[type="radio"]').addClass('checkbox');
}

function aBrowseHistory(area)
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
		aCloseHistory();
		$(this).unbind('click');
	});
}

function aCloseHistory()
{
	$('a.a-history-btn').parents('.a-area').removeClass('browsing-history');
	$('a.a-history-btn').parents('.a-area').removeClass('previewing-history');
	$('.a-history-browser, .a-history-preview-notice').hide();
  $('body').removeClass('history-preview');	
	$('.a-page-overlay').fadeOut();
}

$(document).ready(function(){
	aUI();
});