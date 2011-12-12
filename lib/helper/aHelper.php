<?php

// Loading of the a CSS, JavaScript and helpers is now triggered here 
// to ensure that there is a straightforward way to obtain all of the necessary
// components from any partial, even if it is invoked at the layout level (provided
// that the layout does use_helper('a'). 

function _a_required_assets()
{
  $response = sfContext::getInstance()->getResponse();
  $user = sfContext::getInstance()->getUser();

  sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url', 'I18N'));

  // Do not load redundant CSS and JS in an AJAX context. 
  // These are already loaded on the page in which the AJAX action
  // is operating. Please don't change this as it breaks or at least
  // greatly slows updates
  if (sfContext::getInstance()->getRequest()->isXmlHttpRequest())
  {
    return;
  }

	aTools::addStylesheetsIfDesired();

  aTools::addJavascriptsIfDesired();
}

_a_required_assets();

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
  if ($controller->componentExists($moduleName, "slot"))
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
  $s = "<ul>\r\n";
  foreach ($children as $info)
  {
    $s .= '<li>' . link_to($info['title'], aTools::urlForPage($info['slug']));
    if (isset($info['children']))
    {
      $s .= a_navtree_body($info['children']);
    }
    $s .= "</li>\r\n";
  }
  $s .= "</ul>\r\n";
  return $s;
}

function a_navaccordion()
{
  $page = aTools::getCurrentPage();
  $children = $page->getAccordionInfo(true);
  return a_navtree_body($children);
}

function a_get_stylesheets()
{
  $urlMap = array();
  $newStylesheets = array();
  $response = sfContext::getInstance()->getResponse();
  foreach ($response->getStylesheets() as $file => $options)
  {	
    if (preg_match('/\.less$/', $file))
    {
      $absolute = false;
      if (isset($options['absolute']) && $options['absolute'])
      {
        unset($options['absolute']);
        $absolute = true;
      }
      // Note: you can't use the raw_name option with a less file
      $file = a_stylesheet_path($file, $absolute, array('filesystem' => true));
      $name = aAssets::getLessBasename($file);
      
      $compiled = aFiles::getUploadFolder(array('asset-cache')) . '/' . $name;
      aAssets::compileLessIfNeeded($file, $compiled, array('cacheOnly' => aAssets::canMinify($file, $options)));
      $url = sfConfig::get('app_a_static_url', sfContext::getInstance()->getRequest()->getRelativeUrlRoot()) . '/uploads/asset-cache/' . $name;
      $newStylesheets[$compiled] = $options;
      $urlMap[$compiled] = $url;
    }
    else
    {
      $newStylesheets[$file] = $options;
    }
  }
  return _a_get_assets_body('stylesheets', $newStylesheets, $urlMap);
}

function a_get_javascripts()
{
  $response = sfContext::getInstance()->getResponse();
  return _a_get_assets_body('javascripts', $response->getJavascripts());
}

function _a_get_assets_body($type, $assets, $urlMap = array())
{
  $gzip = sfConfig::get('app_a_minify_gzip', false);
  sfConfig::set('symfony.asset.' . $type . '_included', true);

  $html = '';

  $sets = array();
  
  $unminified = '';
  foreach ($assets as $file => $options)
  {
		if (!aAssets::canMinify($file, $options))
		{
			// Nonlocal URL or minify was explicitly shut off. 
			// Don't get cute with it, otherwise things
			// like Addthis and ckeditor don't work
			if ($type === 'stylesheets')
			{
      	$unminified .= a_stylesheet_tag(isset($urlMap[$file]) ? $urlMap[$file] : $file, $options);
			}
			else
			{
      	$unminified .= a_javascript_include_tag(isset($urlMap[$file]) ? $urlMap[$file] : $file, $options);
			}
			continue;
		}
		
    $absolute = false;
    if (isset($options['absolute']) && $options['absolute'])
    {
      unset($options['absolute']);
      $absolute = true;
    }

    if ($type === 'stylesheets')
    {
      $url = a_stylesheet_path($file, $absolute);
      $file = a_stylesheet_path($file, false, array('filesystem' => true));
    }
    else
    {
      $url = a_javascript_path($file, $absolute);
      $file = a_javascript_path($file, false, array('filesystem' => true));
    }

    if (is_null($options))
    {
      $options = array();
    }
    if ($type === 'stylesheets')
    {
      $options = array_merge(array('rel' => 'stylesheet', 'type' => 'text/css', 'media' => 'screen'), $options);
    }
    else
    {
      $options = array_merge(array('type' => 'text/javascript'), $options);
    }
    $optionGroupKey = json_encode($options);
    $sets[$optionGroupKey][] = $file;
    // echo($file);
    // $html .= "<style>\r\n";
    // $html .= file_get_contents(sfConfig::get('sf_web_dir') . '/' . $file);
    // $html .= "</style>\r\n";
  }
  
  // CSS files with the same options grouped together to be loaded together

  foreach ($sets as $optionsJson => $files)
  {
    $groupFilename = aAssets::getGroupFilename($files);
    $groupFilename .= (($type === 'stylesheets') ? '.css' : '.js');
    if ($gzip)
    {
      $groupFilename .= 'gz';
    }
    $dir = aFiles::getUploadFolder(array('asset-cache'));
    $groupPathname = $dir . '/' . $groupFilename;
    $assetStatCache = aCacheTools::get('assetStat');
    if (!$assetStatCache->get($groupPathname))
    {
      if (!file_exists($groupPathname))
      {
        $content  = '';
        foreach ($files as $file)
        {
          $path = $file;
          // For minified LESS-compiled CSS we get the CSS from the cache. This sidesteps
          // issues with storing it on slow remote filesystems as an intermediate step 
          $fileContent = null;
          if ($type === 'stylesheets')
          {
            $info = aAssets::getCached($file);
            if ($info)
            {
              $fileContent = $info['compiled'];
            }
          }
          if (is_null($fileContent))
          {
            $fileContent = file_get_contents($file);
          }
          if ($type === 'stylesheets')
          {
            $options = array();
            if (!is_null($path))
            {
              // Rewrite relative URLs in CSS files.
              // This trick is available only when we don't insist on
              // pulling our CSS files via http rather than the filesystem
            
              // dirname would resolve symbolic links, we don't want that
              $fdir = preg_replace('/\/[^\/]*$/', '', $path);
              $options['currentDir'] = $fdir;
              $options['docRoot'] = sfConfig::get('sf_web_dir');
            }
            if (sfConfig::get('app_a_minify', false))
            {
              $fileContent = Minify_CSS::minify($fileContent, $options);
              $relativeUrlRoot = sfContext::getInstance()->getRequest()->getRelativeUrlRoot();
              if (strlen($relativeUrlRoot))
              {
                $fileContent = Minify_CSS_UriRewriter::prepend($fileContent, $relativeUrlRoot, array('prependToRootRelative' => true));
              }
            }
          }
          else
          {
            // Trailing carriage return makes behavior more consistent with
            // JavaScript's behavior when loading separate files. For instance,
            // a missing trailing semicolon should be tolerated to the same
            // degree it would be with separate files. The minifier is not
            // a lint tool and should not surprise you with breakage
            $fileContent = JSMin::minify($fileContent) . "\r\n";
          }
          $content .= $fileContent;
        }
        if (sfConfig::get('app_a_copy_assets_then_rename', true))
        {
          if ($gzip)
          {
            _gz_file_put_contents($groupPathname . '.tmp', $content);
          }
          else
          {
            file_put_contents($groupPathname . '.tmp', $content);
          }
          @rename($groupPathname . '.tmp', $groupPathname);
        }
        else
        {
          if ($gzip)
          {
            _gz_file_put_contents($groupPathname, $content);
          }
          else
          {
            file_put_contents($groupPathname, $content);
          }
        }
      }
      // Remember that this file now exists so that we can
      // quickly blow past this even when the filesystem is an
      // expensive S3 call away
      $assetStatCache->set($groupPathname, 1, 86400 * 365);
    }
    $options = json_decode($optionsJson, true);
    // Use stylesheet_path and javascript_path so we can respect relative_root_dir
    if ($type === 'stylesheets')
    {
      $options['href'] = a_stylesheet_path(aAssets::getAssetCacheUrl() . '/' . $groupFilename);
      $html .= tag('link', $options);
			$html .= "\r\n\t";
    }
    else
    {
      $options['src'] = a_javascript_path(aAssets::getAssetCacheUrl() . '/' . $groupFilename);
      $html .= content_tag('script', '', $options); 
			$html .= "\r\n\t";
    }
  }
  // Unminified stuff goes last, after key Apostrophe things have been loaded
  $html .= $unminified;
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

// a_js_call('object.property[?].method(?, ?)', 5, 'name', 'bob')

// That is, use ?'s to insert correctly json-encoded arguments into your JS call.

// Another, less-contrived example:

// a_js_call('apostrophe.slideshowSlot(?)', array('id' => 'et-cetera', ...))

// Notice that arguments can be strings, numbers, or arrays - JSON can handle all of them.

// All calls made in this way are accumulated into a jQuery domready block which
// appears at the end of the body element in our standard layout.php via a_include_js_calls.
// We also insert these at the end when adding or updating a slot via AJAX. You can invoke it
// yourself in other layouts etc.

function a_js_call($callable /* , $arg1, $arg2, ... */ )
{
  $args = array_slice(func_get_args(), 1);
  a_js_call_array($callable, $args);
}

function a_js_call_array($callable, $args)
{
  aTools::$jsCalls[] = array('callable' => $callable, 'args' => $args);
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
    $html .= '<script type="text/javascript" id="a-js-calls">' . "\r\n";
    $html .= '$(function() {' . "\r\n";
    foreach (aTools::$jsCalls as $call)
    {
      $html .= _a_js_call($call['callable'], $call['args']);
    }
    $html .= '});' . "\r\n";
    $html .= '</script>' . "\r\n";
  }
  return $html;
}

function _a_js_call($callable, $args)
{
  $clauses = preg_split('/(\?)/', $callable, null, PREG_SPLIT_DELIM_CAPTURE);
  $code = '';
  $n = 0;
  $q = 0;
  foreach ($clauses as $clause)
  {
    if ($clause === '?')
    {
      $code .= json_encode($args[$n++]);
    }
    else
    {
      $code .= $clause;
    }
  }
  if ($n !== count($args))
  {
    throw new sfException('Number of arguments does not match number of ? placeholders in js call');
  }
  return $code . ";\r\n";
}

// i18n with less effort. Also more flexibility for the future in how we choose to do it  
function a_($s, $params = null)
{
  return __($s, $params, 'apostrophe');
}

// One consistent encoding is needed for non-HTML output in our templates, since we do not assume
// that Symfony is in escaping mode, and the correct statement is so verbose

function a_entities($s)
{
  return htmlentities($s, ENT_COMPAT, 'UTF-8');
}

function a_link_button($label, $symfonyUrl, $options = array(), $classes = array(), $id = null)
{
  return a_button($label, url_for($symfonyUrl, $options), $classes, $id);
}

function a_button($label, $url, $classes = array(), $id = null, $name = null, $title = null)
{
  $hasIcon = in_array('icon', $classes);
	$aLink = in_array('a-link', $classes);
	$arrowBtn = in_array('a-arrow-btn', $classes);
	
	// if it's an a-events button, grab the date and append it as a class
	$aEvents = in_array('a-events', $classes);
	if ($aEvents) {
		$classes[] = 'day-'.date('j');
	}
	
  $s = '<a ';
  if (!is_null($name))
  {
    $s .= 'name="' . a_entities($name) . '" ';
  }
  if (!is_null($title))
  {
    $s .= 'title="' . a_entities($title) . '" ';
  }
  $s .= 'href="' . a_entities($url) . '" ';
  if (!is_null($id))
  {
    $s .= 'id="' . a_entities($id) . '" ';
  }

	if (!$aLink && !$arrowBtn) {
	  $s .= 'class="a-btn ' . implode(' ', $classes) . '">';
	}
	else
	{
		// a-link shares similar physical characteristic to a-btn
		// but they avoid the aeshetic styling of a-btn entirely
  	$s .= 'class="' . implode(' ', $classes) . '">';
	}

  if ($hasIcon)
  {
    $s .= '<span class="icon"></span>';
  }
  // Unfortunately we can't get fancy and wrap a span here because jquery does not
  // bubble click events by default and this is very confusing for devs
  $s .= a_($label) . '</a>';
  return $s;
}

// For a button that will have an icon, specify the icon class.

// Common cases to be aware of: 

// For a cancel button use the a-cancel class (if you also specify the icon class you get an x)

// Do not use for submit buttons. Due to longstanding problems with JS submit() 
// calls not being able to invoke both JavaScript handlers and the native submit 
// behavior in the correct way it is usually eventually necessary to use a real 
// submit button. Use a_submit_button to get one of those styled in the standard 
// Apostrophe way.

function a_js_button($label, $classes = array(), $id = null)
{
  return a_button($label, '#', $classes, $id);
}

// Even more convenient way to do a cancel button based on the above
function a_js_cancel_button($label = null, $classes = array(), $id = null)
{
  if (is_null($label))
  {
    $label = a_('Cancel');
  }
  $classes[] = 'a-cancel';
  return a_js_button($label, $classes, $id);
}

// A real submit button, styled for Apostrophe.
// Should not need an id - we style these things by
// class so there can be more than one on a page, right?

function a_submit_button($label, $classes = array(), $name = null)
{
  $s = '<input type="submit" value="' . a_entities($label) . '" class="a-btn a-submit ' . implode(' ', $classes) . '" ';
  if (!is_null($name))
  {
    $s .= 'name="' . a_entities($name) . '" ';
  }
  $s .= '/>';
  return $s;
}

// TODO: having the options here be the reverse of the options to
// a_button is absurd and we need an options array for both of them.
// For now this is more backward compatible

// An anchor tag 'submit button', styled for Apostrophe
// and configured behind the scenes to autosubmit the form when clicked 
// like a real submit button would. However, this should
// NOT be used in AJAX forms, because there is no consistent
// way to avoid triggering the native submit behavior of
// the form. For AJAX forms use real submit buttons
// or attach the desired submit behavior directly to the button

// A submit button should never need an id because you style them
// by class - on the other hand it often needs a name so it can
// be distinguished from other submit buttons when the form submission
// is received, just like a normal submit button

// You will often want to add the a-submit class, but not always as it's
// not always the visual impact you want

function a_anchor_submit_button($label, $classes = array(), $name = null, $id = null)
{
  // a-btn would be redundant here, a_button does that
  $classes[] = 'a-act-as-submit';
  return a_button($label, '#', $classes, $id, $name);
}

// A button that removes a filter (parameter) from the given URL.
// Uses the "label followed by an x" style. $parameter can be an array of
// several parameter names. Calls link_to on the URL. This means you can pass an easily manipulated 
// Symfony URL with &-separated params but get a user friendly routed URL as final output.
// This ought to call a_button but I'm wrestling with the incompatibility of inline
// content and a_button's CSS. Notice that it's playing out rather well in the blog engine. -Tom

function a_remove_filter_button($label, $url, $parameter)
{
  if (!is_array($parameter))
  {
    $parameter = array($parameter);
  }
  $remove = array();
  foreach ($parameter as $p)
  {
    // aUrl::addParams removes when the value is blank
    $remove[$p] = '';
  }
  $url = aUrl::addParams($url, $remove);
  return link_to($label.'<span class="icon"></span>', url_for($url), array('class' => 'a-remove-filter-button', 'title' => 'Remove Filter: ' . $label));
}

function a_url($module, $action, $getParams = array(), $absolute = false)
{
  $params = array('action' => $action);
  if ($module == 'aMedia')
  {
    $route = 'a_media_other';
  }
  else
  {
    $route = sfConfig::get('app_a_default_route', 'default');
    $params['module'] = $module;
  }
 return url_for($route, $params, $absolute) . ($getParams ? '?' . http_build_query($getParams) : '');
}

function a_link_to($label, $module, $action, $options = array())
{
 if (isset($options['query_string']) && is_array($options['query_string']))
 {
  $options['query_string'] = http_build_query($options['query_string']);
 }
  return link_to($label, sfConfig::get('app_a_default_route', 'default'),
  array('module' => $module, 'action' => $action), array('query_string' => http_build_query($getParams)));
}

/**
 * Returns the web path to a JavaScript asset. NOT a filename. app_a_static_url takes precedence over sf_relative_url.
 * Borrowed from the standard Symfony AssetHelper, which can't be subclassed due to the use of functions rather
 * than classes.
 *
 * <b>Example:</b>
 * <code>
 *  echo a_javascript_path('myscript');
 *    => /js/myscript.js
 * </code>
 *
 * <b>Note:</b> The asset name can be supplied as a...
 * - full path, like "/my_js/myscript.css"
 * - file name, like "myscript.js", that gets expanded to "/js/myscript.js"
 * - file name without extension, like "myscript", that gets expanded to "/js/myscript.js"
 *
 * @param string $source   asset name
 * @param bool   $absolute return absolute path ?
 * @param array  $options  'filesystem' => true means return a filesystem path rather than a web path
 * @return string file path to the JavaScript file
 * @see    a_javascript_include_tag
 */
function a_javascript_path($source, $absolute = false, $options = array())
{
  return a_compute_public_path($source, sfConfig::get('sf_web_js_dir_name', 'js'), 'js', $absolute, $options);
}

/**
 * Returns a <script> include tag per source given as argument.
 *
 * <b>Examples:</b>
 * <code>
 *  echo javascript_include_tag('xmlhr');
 *    => <script language="JavaScript" type="text/javascript" src="/js/xmlhr.js"></script>
 *  echo javascript_include_tag('common.javascript', '/elsewhere/cools');
 *    => <script language="JavaScript" type="text/javascript" src="/js/common.javascript"></script>
 *       <script language="JavaScript" type="text/javascript" src="/elsewhere/cools.js"></script>
 * </code>
 *
 * @param string asset names
 * @param array additional HTML compliant <link> tag parameters
 *
 * @return string XHTML compliant <script> tag(s)
 * @see    javascript_path
 */
function a_javascript_include_tag()
{
  $sources = func_get_args();
  $sourceOptions = (func_num_args() > 1 && is_array($sources[func_num_args() - 1])) ? array_pop($sources) : array();

  $html = '';
  foreach ($sources as $source)
  {
    $absolute = false;
    if (isset($sourceOptions['absolute']))
    {
      unset($sourceOptions['absolute']);
      $absolute = true;
    }

    $condition = null;
    if (isset($sourceOptions['condition']))
    {
      $condition = $sourceOptions['condition'];
      unset($sourceOptions['condition']);
    }

    if (!isset($sourceOptions['raw_name']))
    {
      $source = a_javascript_path($source, $absolute);
    }
    else
    {
      unset($sourceOptions['raw_name']);
    }

    $options = array_merge(array('type' => 'text/javascript', 'src' => $source), $sourceOptions);
    $tag = content_tag('script', '', $options);

    if (null !== $condition)
    {
      $tag = comment_as_conditional($condition, $tag);
    }

    $html .= $tag."\r\n\t";
  }

  return $html;
}

/**
 * Returns the path to a stylesheet asset. Respects app_a_static_url.
 *
 * <b>Example:</b>
 * <code>
 *  echo a_stylesheet_path('style');
 *    => /css/style.css
 * </code>
 *
 * <b>Note:</b> The asset name can be supplied as a...
 * - full path, like "/my_css/style.css"
 * - file name, like "style.css", that gets expanded to "/css/style.css"
 * - file name without extension, like "style", that gets expanded to "/css/style.css"
 *
 * @param string $source   asset name
 * @param bool   $absolute return absolute path
 * @param array  $options 'filesystem' => true returns filesystem paths rather than URL paths, ignores $absolute
 * @return string file path to the stylesheet file
 * @see    stylesheet_tag
 */
function a_stylesheet_path($source, $absolute = false, $options = array())
{
  return a_compute_public_path($source, sfConfig::get('sf_web_css_dir_name', 'css'), 'css', $absolute, $options);
}

/**
 * Returns a css <link> tag per source given as argument,
 * to be included in the <head> section of a HTML document. Respects app_a_static_url.
 *
 * <b>Options:</b>
 * - rel - defaults to 'stylesheet'
 * - type - defaults to 'text/css'
 * - media - defaults to 'screen'
 *
 * <b>Examples:</b>
 * <code>
 *  echo stylesheet_tag('style');
 *    => <link href="/stylesheets/style.css" media="screen" rel="stylesheet" type="text/css" />
 *  echo stylesheet_tag('style', array('media' => 'all'));
 *    => <link href="/stylesheets/style.css" media="all" rel="stylesheet" type="text/css" />
 *  echo stylesheet_tag('style', array('raw_name' => true));
 *    => <link href="style" media="all" rel="stylesheet" type="text/css" />
 *  echo stylesheet_tag('random.styles', '/css/stylish');
 *    => <link href="/stylesheets/random.styles" media="screen" rel="stylesheet" type="text/css" />
 *       <link href="/css/stylish.css" media="screen" rel="stylesheet" type="text/css" />
 * </code>
 *
 * @param string asset names
 * @param array  additional HTML compliant <link> tag parameters
 *
 * @return string XHTML compliant <link> tag(s)
 * @see    stylesheet_path
 */
function a_stylesheet_tag()
{
  $sources = func_get_args();
  $sourceOptions = (func_num_args() > 1 && is_array($sources[func_num_args() - 1])) ? array_pop($sources) : array();

  $html = '';
  foreach ($sources as $source)
  {
    $absolute = false;
    if (isset($sourceOptions['absolute']))
    {
      unset($sourceOptions['absolute']);
      $absolute = true;
    }

    $condition = null;
    if (isset($sourceOptions['condition']))
    {
      $condition = $sourceOptions['condition'];
      unset($sourceOptions['condition']);
    }

    if (!isset($sourceOptions['raw_name']))
    {
      $source = a_stylesheet_path($source, $absolute);
    }
    else
    {
      unset($sourceOptions['raw_name']);
    }

    $options = array_merge(array('rel' => 'stylesheet', 'type' => 'text/css', 'media' => 'screen', 'href' => $source), $sourceOptions);
    $tag = tag('link', $options);

    if (null !== $condition)
    {
      $tag = comment_as_conditional($condition, $tag);
    }

    $html .= $tag."\r\n";
  }

  return $html;
}

/**
 * Returns the path to an image asset.
 *
 * <b>Example:</b>
 * <code>
 *  echo image_path('foobar');
 *    => /images/foobar.png
 * </code>
 *
 * <b>Note:</b> The asset name can be supplied as a...
 * - full path, like "/my_images/image.gif"
 * - file name, like "rss.gif", that gets expanded to "/images/rss.gif"
 * - file name without extension, like "logo", that gets expanded to "/images/logo.png"
 *
 * @param string $source   asset name
 * @param bool   $absolute return absolute path ?
 * @param array  $options  'filesystem' => true returns a filesystem path rather than a URL, ignores $absolute
 * @return string file path to the image file
 * @see    image_tag
 */
function a_image_path($source, $absolute = false, $options = array())
{
  return a_compute_public_path($source, sfConfig::get('sf_web_images_dir_name', 'images'), 'png', $absolute, $options);
}

/**
 * Returns an <img> image tag for the asset given as argument. Respects app_a_static_url.
 *
 * <b>Options:</b>
 * - 'absolute' - to output absolute file paths, useful for embedded images in emails
 * - 'alt'  - defaults to the file name part of the asset (capitalized and without the extension)
 * - 'size' - Supplied as "XxY", so "30x45" becomes width="30" and height="45"
 *
 * <b>Examples:</b>
 * <code>
 *  echo image_tag('foobar');
 *    => <img src="images/foobar.png" alt="Foobar" />
 *  echo image_tag('/my_images/image.gif', array('alt' => 'Alternative text', 'size' => '100x200'));
 *    => <img src="/my_images/image.gif" alt="Alternative text" width="100" height="200" />
 * </code>
 *
 * @param string $source  image asset name
 * @param array  $options additional HTML compliant <img> tag parameters
 *
 * @return string XHTML compliant <img> tag
 * @see    image_path
 */
function a_image_tag($source, $options = array())
{
  if (!$source)
  {
    return '';
  }

  $options = _parse_attributes($options);

  $absolute = false;
  if (isset($options['absolute']))
  {
    unset($options['absolute']);
    $absolute = true;
  }

  if (!isset($options['raw_name']))
  {
    $options['src'] = a_image_path($source, $absolute);
  }
  else
  {
    $options['src'] = $source;
    unset($options['raw_name']);
  }

  if (isset($options['alt_title']))
  {
    // set as alt and title but do not overwrite explicitly set
    if (!isset($options['alt']))
    {
      $options['alt'] = $options['alt_title'];
    }
    if (!isset($options['title']))
    {
      $options['title'] = $options['alt_title'];
    }
    unset($options['alt_title']);
  }

  if (isset($options['size']))
  {
    list($options['width'], $options['height']) = explode('x', $options['size'], 2);
    unset($options['size']);
  }

  return tag('img', $options);
}

/**
 * Extended by punkave to support $options['filesystem'] = true. If this is set a
 * filesystem path is returned, respecting app_a_static_path if set and built in the
 * usual way if not. If filesystem is not set, the web URL returned 
 * respects app_a_static_url. This combination allows us to push all of the 
 * non-dynamic URLs of the site onto another site (such as S3)
 * without causing problems for the actual PHP URLs (as sf_relative_url would).
 */
 
function a_compute_public_path($source, $dir, $ext, $absolute = false, $options = array())
{
  if (strpos($source, '://'))
  {
    return $source;
  }

  $request = sfContext::getInstance()->getRequest();
  
  $filesystem = isset($options['filesystem']) && $options['filesystem'];
  
  if ($filesystem)
  {
    $stem = sfConfig::get('app_a_static_path', sfConfig::get('sf_web_dir'));
  }
  else
  {
    $stem = sfConfig::get('app_a_static_url', $request->getRelativeUrlRoot());
  }
  
  if (0 !== strpos($source, '/'))
  {
    $source = $stem.'/'.$dir.'/'.$source;
  }

  $query_string = '';
  if (false !== $pos = strpos($source, '?'))
  {
    $query_string = substr($source, $pos);
    $source = substr($source, 0, $pos);
  }

  if (false === strpos(basename($source), '.'))
  {
    $source .= '.'.$ext;
  }

  if (strlen($stem) && 0 !== strpos($source, $stem))
  {
    $source = $stem.$source;
  }

  if ($absolute)
  {
    // We may already have an absolute URL at this point due to the use of app_a_static_url
    if (!preg_match('/^https?:/', $source))
    {
      $source = 'http'.($request->isSecure() ? 's' : '').'://'.$request->getHost().$source;
    }
  }

  return $source.$query_string;
}

