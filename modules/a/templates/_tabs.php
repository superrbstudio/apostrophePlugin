<ul id="a-tab-navigation" >
<?php // TBB: Let's stop pretending this makes sense in shorthand syntax. ?>
<?php // When there's more logic than HTML, it's time to write real PHP. ?>

<?php
$tabcount = 0;

foreach ($tabs as $tab)
{

	if ($tabcount == 0) {
		$tabclass = "a-tab-nav-item first";
	}
	elseif ($tabcount == count($tabs)-1) {
		$tabclass = "a-tab-nav-item last";
	} 
	else
	{
		$tabclass = "a-tab-nav-item";
	}
	
  $id = $tab['id'];
  echo('<li id="a-tab-nav-item-' . $id . '" ');
  $classes = '';
  if ($page)
  {
    if ($tab['level'] > 0)
    {
      if (aTools::pageIsDescendantOfInfo($page, $tab))
      {
        $classes .= "a-current-page ";
      }
    } 
    if ($page->slug === $tab['slug'])
    {
      $classes .= "a-current-page ";
    }
  }  
  if ($tab['archived'])
  {
    $classes .= "a-archived-page ";
  }
  echo("class='$classes $tabclass'>");
  echo link_to(
    $tab['title'], 
    aTools::urlForPage($tab['slug']),
    array());
  echo("</li>\n");
	$tabcount++;

}
?>
</ul>
<?php if ($draggable): ?>


<script type="text/javascript" charset="utf-8">
	//<![CDATA[
	$(document).ready(
	  function() 
	  {
	    $("#a-tab-navigation").sortable(
	    { 
	      update: function(e, ui) 
	      { 
	        var serial = jQuery("#a-tab-navigation").sortable('serialize', {});
	        var options = {"url":<?php echo json_encode(url_for('a/sortTabs').'?page=' . $page->getId()); ?>,"type":"POST"};
	        options['data'] = serial;
	        $.ajax(options);

					// This makes the tab borders display properly after re-positioning
					$('.a-tab-nav-item').removeClass('last');
					$('.a-tab-nav-item').removeClass('first');
					$('.a-tab-nav-item:first').addClass('first');
					$('.a-tab-nav-item:last').addClass('last');					
	      }
	    });
	  });
	//]]>
	</script>
<?php endif ?>