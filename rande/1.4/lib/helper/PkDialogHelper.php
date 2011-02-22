<?php

sfContext::getInstance()->getConfiguration()->loadHelpers(array('jQuery', 'I18N'));

// Opens a modal dialog using the CMS rolldown-from-top styles. 
// The dialog content is loaded via AJAX to avoid the performance
// impact of loading it all the time.

// The required id option is the DOM id of the dialog, but it is also
// the prefix for all other classes and IDs associated with it and referenced
// in the generated HTML/CSS/JavaScript, such as: .$id-button

// The title option is also required. It appears in title attributes
// and default button link text, following "View" or "Close".

// The action option, also required, is the Symfony URL to be loaded
// into the dialog element via AJAX.

// If the chadFrom option is set, the chad is positioned based on the
// location of the element matching that selector. The chad is 
// identified by the a-chad class, found within the dialog's id.

// For examples seee the page settings form and the user profile settings form.

function a_remote_dialog_toggle($options)
{
  if (!isset($options['id']))
  {
    throw new sfException("Required id option not passed to a_dialog_toggle");
  }
  if (!isset($options['label']))
  {
    throw new sfException("Required label option not passed to a_dialog_toggle");
  }
  if (!isset($options['action']))
  {
    throw new sfException("Required action option not passed to a_dialog_toggle");
  }

  $id = $options['id'];
  $action = $options['action'];
	$label = $options['label'];

  if (isset($options['chadFrom']))
  {
    $chadFrom = $options['chadFrom'];
  }
  if (isset($options['loading']))
  {
    $loading = $options['loading'];
  }

	if (isset($options['hideToggle']) && $options['hideToggle'] == true)
	{
		$before =	" $('.$id-loading').show();";
	} else
	{
		$before =	"$('.$id-button.open').hide(); $('.$id-loading').show();";		
	}


  $s = '';
  $s .= jq_link_to_remote(__($label, null, 'apostrophe'), 
    array(
      "url" => $action,
      "update" => $id,
      "script" => true,
  		"before" => $before, 
      "complete" => "$('#$id').fadeIn();
  									 $('.$id-loading').hide();
										 	$('.$id-button.open').hide();
  									 $('#$id-button-close').show();" .
  									 (isset($chadFrom) ?
      							   "var arrowPosition = parseInt($('$chadFrom').offset().left);
      								 $('#$id .a-chad').css('left',arrowPosition+'px'); " : "") . "
  									 aUI('#$id');
  									$('.a-page-overlay').show();",
    ), array(
  		'class' => "$id-button open", 
  		'id' => "$id-button-open"));
  $s .= jq_link_to_function(__($label, null, 'apostrophe'), 
		"$('#$id-button-close').hide(); 
		 $('#$id-button-open').show(); 
		 $('#$id').hide();
		 $('.a-page-overlay').hide();", 
		 array(
			'class' => "$id-button close", 
			'id' => "$id-button-close",
			'style' => 'display:none;', ));
	if (isset($loading))
	{
  	$s .= image_tag($loading,
  	  array('class' => "$id-loading", 'style' => 'display:none;',  ));
  }
  return $s;
}
