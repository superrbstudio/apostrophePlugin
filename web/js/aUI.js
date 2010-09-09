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

	var aBtns = $(target+' .a-btn, ' + target + ' .a-submit, ' + target + ' .a-cancel');
	aBtns.each(function() {
		var aBtn = $(this);

		// Setup Icons
		if (aBtn.is('a') && aBtn.hasClass('icon') && !aBtn.children('.icon').length) 
		{
			aBtn.prepend('<span class="icon"></span>');						
		};
		
		// Setup Flagging Buttons
		if(aBtn.hasClass('flag'))
		{
			if (!aBtn.children('.flag-label').length)
			{
				aBtn.attr('title','').wrapInner('<span class="flag-label"></span>');		
			}

			aBtn.hover(function () {
				aBtn.addClass('expanded');
			},function () {
				aBtn.removeClass('expanded');
			});	
		}
	
  });
	
	// Variants
	$('a.a-variant-options-toggle').click(function(){
		$(this).parents('.a-slots').children().css('z-index','699');
		$(this).parents('.a-slot').css('z-index','799');	
	});
	
	// Disabled Buttons
	$('.a-disabled').unbind('click').unbind('hover').click(function(event){
		event.preventDefault();
	}).attr('onclick','');

	// Cross Browser Opacity Settings
	$('.a-nav .a-archived-page').fadeTo(0,.5); // Archived Page Labels

	// Apply clearfix on controls and options
	$('.a-controls, .a-options').addClass('clearfix');

	// Add 'last' Class To Last Option
	$('.a-controls li:last-child').addClass('last'); 
	
	// You can define this function in your site.js to execute code whenenever apostrophe calls aUI();
	// We use this for refreshing progressive enhancements such as Cufon following an Ajax request.
	if (typeof(aOverrides) =="function")
	{ 
		aOverrides(); 	
	}
}

$(document).ready(function(){
	aUI();	
});