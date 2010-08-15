<?php

// Loading of the a CSS, JavaScript and helpers is now triggered here 
// to ensure that there is a straightforward way to obtain all of the necessary
// components from any partial, even if it is invoked at the layout level (provided
// that the layout does use_helper('a'). 

function _a_required_assets()
{
  // Do not load redundant CSS and JS in an AJAX context. 
  // These are already loaded on the page in which the AJAX action
  // is operating. Please don't change this as it breaks or at least
  // greatly slows updates
  if (sfContext::getInstance()->getRequest()->isXmlHttpRequest())
  {
    return;
  }
  $response = sfContext::getInstance()->getResponse();
  $user = sfContext::getInstance()->getUser();

  sfContext::getInstance()->getConfiguration()->loadHelpers(
    array("Url", "jQuery", "I18N", 'PkDialog'));

  jq_add_plugins_by_name(array("ui"));

  if (sfConfig::get('app_a_use_bundled_stylesheet', true))
  {
	
		// This could be used as a way to manage what styles are included when logged in / out.	
		// But it really seems like we use pieces of every one of these when logged in and out.
 		aTools::addStylesheetsIfDesired(array('reset', 'utility', 'forms', 'buttons', 'navigation', 'components', 'area-slots', 'engines', 'admin', 'colors'));
  }

  $response->addJavascript('/apostrophePlugin/js/aUI.js');
  $response->addJavascript('/apostrophePlugin/js/aControls.js');
  $response->addJavascript('/apostrophePlugin/js/plugins/jquery.autogrow.js'); // Autogrowing Textareas
	$response->addJavascript('/apostrophePlugin/js/plugins/jquery.keycodes-0.2.js'); // keycodes
	$response->addJavascript('/apostrophePlugin/js/plugins/jquery.timer-1.2.js');	
  $webDir = sfConfig::get('sf_a_web_dir', '/apostrophePlugin');
  $response->addJavascript("$webDir/js/a.js");

}

_a_required_assets();

// Too many jquery problems
//sfContext::getInstance()->getResponse()->addJavascript(
// sfConfig::get('sf_a_web_dir', '/apostrophePlugin') . 
// '/js/aSubmitButton.js');
//<script type="text/javascript" charset="utf-8">
//aSubmitButtonAll();
//</script>
function a_slot($name, $type, $options = false)
{
  $options = a_slot_get_options($options);
  $options['type'] = $type;
	$options['singleton'] = true;
  aTools::globalSetup($options);
  include_component("a", "area", 
    array("name" => $name, "options" => $options)); 
  aTools::globalShutdown();
}

function a_area($name, $options = false)
{
  $options = a_slot_get_options($options);
  $options['infinite'] = true; 
  aTools::globalSetup($options);
  include_component("a", "area", 
    array("name" => $name, "options" => $options)); 
  aTools::globalShutdown();
}

function a_slot_get_options($options)
{
  if (!is_array($options))
  {
    if ($options === false)
    {
      $options = array();
    }
    else
    {
      $options = aTools::getSlotOptionsGroup($options);
    }
  }
  return $options;
}

function a_slot_body($name, $type, $permid, $options, $validationData, $editorOpen, $updating = false)
{
  $page = aTools::getCurrentPage();
  $slot = $page->getSlot($name);
  $parameters = array("options" => $options);
  $parameters['name'] = $name;
  $parameters['type'] = $type;
  $parameters['permid'] = $permid;
  $parameters['validationData'] = $validationData;
  $parameters['showEditor'] = $editorOpen;
  $parameters['updating'] = $updating;
  $user = sfContext::getInstance()->getUser();
  $controller = sfContext::getInstance()->getController();
  $moduleName = $type . 'Slot';
  if ($controller->componentExists($moduleName, "executeSlot"))
  {
    include_component($moduleName, "slot", $parameters);
  }
  else
  {
    include_component("a", "slot", $parameters);
  }
}

// Frequently convenient when you want to check an option in a template.
// Doing the isset() ? foo : bar dance over and over is bug-prone and confusing

function a_get_option($array, $key, $default = false)
{
  if (isset($array[$key]))
  {
    return $array[$key];
  }
  else
  {
    return $default;
  }
}

// THESE ARE DEPRECATED, use the aNavigationComponent instead

function a_navtree($depth = null)
{
  $page = aTools::getCurrentPage();
  $children = $page->getTreeInfo(true, $depth);
  return a_navtree_body($children);
}

function a_navtree_body($children)
{
  $s = "<ul>\n";
  foreach ($children as $info)
  {
    $s .= '<li>' . link_to($info['title'], aTools::urlForPage($info['slug']));
    if (isset($info['children']))
    {
      $s .= a_navtree_body($info['children']);
    }
    $s .= "</li>\n";
  }
  $s .= "</ul>\n";
  return $s;
}

function a_navaccordion()
{
  $page = aTools::getCurrentPage();
  $children = $page->getAccordionInfo(true);
  return a_navtree_body($children);
}

// Keeping this functionality in a helper is very questionable.
// It should probably be a component.

// ... Sure enough, it's now called by a component in preparation to migrate
// the logic there as well.

function a_navcolumn()
{
  $page = aTools::getCurrentPage();
  return _a_navcolumn_body($page);
}

function _a_navcolumn_body($page)
{
  $sortHandle = "";
  $sf_user = sfContext::getInstance()->getUser();
  $admin = $page->userHasPrivilege('edit');
  if ($admin)
  {
    $sortHandle = "<div class='a-btn icon a-drag'></div>";
  }
  $result = "";
  // Inclusion of archived pages should be a bit generous to allow for tricky situations
  // in which those who can edit a subpage might not be able to find it otherwise.
  // We don't want the performance hit of checking for the right to edit each archived
  // subpage, so just allow those with potential-editor privs to see that archived pages
  // exist, whether or not they are allowed to actually edit them
  if (aTools::isPotentialEditor() && 
    $sf_user->getAttribute('show-archived', true, 'apostrophe'))
  {
    $livingOnly = false;
  }
  else
  {
    $livingOnly = true;
  }
  $result = '<ul id="a-navcolumn" class="a-navcolumn">';
  $childrenInfo = $page->getChildrenInfo($livingOnly);
  if (!count($childrenInfo))
  {
    $childrenInfo = $page->getPeerInfo($livingOnly);
  }
	$n = 1;
  foreach ($childrenInfo as $childInfo)
  {
    $class = "peer_item";

    if ($childInfo['id'] == $page->id)
    {
      $class = "self_item";
    }

		if ($n == 1)
		{
			$class .= ' first';
		}

		if ($n == count($childrenInfo))
		{
			$class .= ' last';
		}

    // Specific format to please jQuery.sortable
    $result .= "<li id=\"a-navcolumn-item-" . $childInfo['id'] . "\" class=\"a-navcolumn-item $class\">\n";
    $title = $childInfo['title'];
    if ($childInfo['archived'])
    {
      $title = '<span class="a-archived-page" title="&quot;'.$title.'&quot; is Unpublished">'.$title.'</span>';
    }
    $result .= $sortHandle.link_to($title, aTools::urlForPage($childInfo['slug']));
    $result .= "</li>\n";
		$n++;
  }
  $result .= "</ul>\n";
  if ($admin)
  {
    $result .= jq_sortable_element('#a-navcolumn', array('url' => 'a/sort?page=' . $page->getId()));    
  }
  return $result;
}

function a_get_stylesheets()
{
  if (sfConfig::get('app_a_minify', false))
  {
    $response = sfContext::getInstance()->getResponse();
    return _a_get_assets_body('stylesheets', $response->getStylesheets());
  }
  else
  {
    return get_stylesheets();
  }
}

function a_get_javascripts()
{
  if (sfConfig::get('app_a_minify', false))
  {
    $response = sfContext::getInstance()->getResponse();
    return _a_get_assets_body('javascripts', $response->getJavascripts());
  }
  else
  {
    return get_javascripts();
  }
}

function _a_get_assets_body($type, $assets)
{
  $gzip = sfConfig::get('app_a_minify_gzip', false);
  sfConfig::set('symfony.asset.' . $type . '_included', true);

  $html = '';
  $sets = array();
  $combinedFilename = '';
  foreach ($assets as $file => $options)
  {
    /*
     *
     * Guts borrowed from stylesheet_tag and javascript_tag. We still do a tag if it's
     * a conditional stylesheet
     *
     */

    $absolute = false;
    if (isset($options['absolute']))
    {
      unset($options['absolute']);
      $absolute = true;
    }

    $condition = null;
    if (isset($options['condition']))
    {
      $condition = $options['condition'];
      unset($options['condition']);
    }

    if (!isset($options['raw_name']))
    {
      if ($type === 'stylesheets')
      {
        $file = stylesheet_path($file, $absolute);
      }
      else
      {
        $file = javascript_path($file, $absolute);
      }
    }
    else
    {
      unset($options['raw_name']);
    }

    if ($type === 'stylesheets')
    {
      $options = array_merge(array('rel' => 'stylesheet', 'type' => 'text/css', 'media' => 'screen', 'href' => $file), $options);
    }
    else
    {
      $options = array_merge(array('type' => 'text/javascript', 'src' => $file), $options);
    }
    
    if (null !== $condition)
    {
      $tag = tag('link', $options);
      $tag = comment_as_conditional($condition, $tag);
      $html .= $tag . "\n";
    }
    else
    {
      unset($options['href'], $options['src']);
      $optionGroupKey = json_encode($options);
      $set[$optionGroupKey][] = $file;
    }
    // echo($file);
    // $html .= "<style>\n";
    // $html .= file_get_contents(sfConfig::get('sf_web_dir') . '/' . $file);
    // $html .= "</style>\n";
  }
  
  // CSS files with the same options grouped together to be loaded together

  foreach ($set as $optionsJson => $files)
  {
    $groupFilename = '';
    foreach ($files as $file)
    {
      $groupFilename .= $file;
      // If your CSS files depend on clever aliases that won't work
      // through the filesystem, we can get them by http. We're caching
      // so that's not terrible, but it's usually simpler faster and less
      // buggy to grab the file content.
    }
    // TODO: learn more about the safety/danger of using the md5 as an id all by itself.
    // Otherwise we have to gzip or something, is that a bigger performance hit?
    // I tried just using $groupFilename as is (after stripping dangerous stuff) 
    // but it's too long for the OS if you include enough to make it unique
    $groupFilename = md5($groupFilename);
    $groupFilename .= (($type === 'stylesheets') ? '.css' : '.js');
    if ($gzip)
    {
      $groupFilename .= 'gz';
    }
    $dir = aFiles::getUploadFolder(array('asset-cache'));
    if (!file_exists($dir . '/' . $groupFilename))
    {
      $content  = '';
      foreach ($files as $file)
      {
        if (sfConfig::get('app_a_stylesheet_cache_http', false))
        {
          $url = sfContext::getRequest()->getUriPrefix() . $file;
          $content .= file_get_contents($url);
        }
        else
        {
          $content .= file_get_contents(sfConfig::get('sf_web_dir') . $file);
        }
      }
      if ($type === 'stylesheets')
      {
        $content = Minify_CSS::minify($content);
      }
      else
      {
        $content = JSMin::minify($content);
      }
      if ($gzip)
      {
        _gz_file_put_contents($dir . '/' . $groupFilename . '.tmp', $content);
      }
      else
      {
        file_put_contents($dir . '/' . $groupFilename . '.tmp', $content);
      }
      @rename($dir . '/' . $groupFilename . '.tmp', $dir . '/' . $groupFilename);
    }
    $options = json_decode($optionsJson, true);
    $options[($type === 'stylesheets') ? 'href' : 'src'] = '/uploads/asset-cache/' . $groupFilename;
    if ($type === 'stylesheets')
    {
      $html .= tag('link', $options);
    }
    else
    {
      $html .= content_tag('script', '', $options); 
    }
  }
  return $html;
}

function a_include_stylesheets()
{
  echo(a_get_stylesheets());
}

function a_include_javascripts()
{
  echo(a_get_javascripts());
}

function _gz_file_put_contents($file, $contents)
{
  $fp = gzopen($file, 'wb');
  gzwrite($fp, $contents);
  gzclose($fp);
}

// Call like this:

// a_js_call('apostrophe.slideshow', array('id' => 'et-cetera', ...)

// All calls made in this way are accumulated into a jQuery domready block which
// appears at the end of the body element in our standard layout.php via a_include_js_calls.
// We also insert these at the end when adding or updating a slot via AJAX. You can invoke it
// yourself in other layouts etc.

function a_js_call($callable, $args = null, $options = array())
{
  if (a_get_option($options, 'now'))
  {
    $html .= '<script type="text/javascript" charset="utf-8">' . "\n";
    $html .= a_js_call($callable, $args);
  }
  else
  {
    aTools::$jsCalls[] = array('callable' => $callable, 'args' => $args, 'options' => $options);
  }
}

function a_include_js_calls()
{
  echo(a_get_js_calls());
}

function a_get_js_calls()
{
  $html = '';
  if (count(aTools::$jsCalls))
  {
    $html .= '<script type="text/javascript" charset="utf-8">' . "\n";
    $html .= '$(function() {' . "\n";
    foreach (aTools::$jsCalls as $call)
    {
      $html .= _a_js_call($call['callable'], $call['args']);
    }
    $html .= '});' . "\n";
    $html .= '</script>' . "\n";
  }
  return $html;
}

function _a_js_call($callable, $args)
{
  return $callable . '(' . (is_null($args) ? '' : json_encode($args)) . ');' . "\n";
}

// i18n with less effort. Also more flexibility for the future in how we choose to do it  
function a_($s, $params = null)
{
  return __($s, $params, 'apostrophe');
}
