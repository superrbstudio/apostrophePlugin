<?php

/**
 * HTML related utilities. HTML markup to RSS markup conversion,
 * simplification of HTML to a short list of legal tags and no 
 * dangerous attributes, mailto: obfuscation, word count limit
 * that preserves valid HTML markup, and basic text-to-HTML
 * conversion that preserves line breaks and creates links.
 *
 * doc-to-HTML conversion has been removed as it's out of scope for
 * apostrophePlugin which should contain lightweight stuff only.
 * We should consider putting that out as a separate plugin.
 *
 * @author Tom Boutell <tom@punkave.com>
 */

class aHtmlNotHtmlException extends Exception
{
  
}

class aHtml
{
  static private $badPunctuation = array('“', '”', '®', '‘', '’');
  static private $badPunctuationReplacements = array('&lquot;', '&rquot;', '&reg;', '&lsquo;', '&rsquo;');

  static private $rssEntityMap = 
    array('&lquot;' => '\"',
      '&rquot;' => '\"',
      '&reg;' => '(Reg TM)', 
      '&lsquo;' => '\'',
      '&rsquo;' => '\'',
      '&bull' => '*',
      '&amp;' => '&amp;',
      '&lt;' => '&lt;',
      '&gt;' => '&gt;'
    );

  // Right now this just converts obscure HTML entities to 
  // simpler stuff that all feed readers will digest.
  public static function htmlToRss($doc)
  {
    // Eval stuff like this is not the quickest. There 
    // must be a better way. We should be saving a
    // pre-RSSified version of posts, for one thing.
    return preg_replace(
      '/(&\w+;)/e', 
      "aHtml::entityToRss('$1')",
      $doc);
  }
  
  public static function entityToRss($entity)
  {
    if (isset(self::$rssEntityMap[$entity]))
    {
      return self::$rssEntityMap[$entity];
    } 
    else
    {
      return '';
    }
  }

  // The default list of allowed tags for aHtml::simplify().
  // These work well for user-generated content made with FCK.
  // You can now alter this list by passing a similar list as the second
  // argument to aHtml::simplify(). An array of tag names without braces is also allowed.
  
  // Reserving h1 and h2 for the site layout's use is generally a good idea
  
  static private $defaultAllowedTags =
    '<h3><h4><h5><h6><blockquote><p><a><ul><ol><nl><li><b><i><strong><em><strike><code><hr><br><div><table><thead><caption><tbody><tr><th><td><pre>';

  // The default list of allowed attributes for aHtml::simplify().
  // You can now alter this list by passing a similar array as the fourth
  // argument to aHtml::simplify().

  static private $defaultAllowedAttributes = array(
    "a" => array("href", "name", "target"),
    "img" => array("src")
  );
  
  // Subtle control of the style attribute is possible, but we don't allow
  // any styles by default. See the allowedStyles argument to simplify()
  
  static private $defaultAllowedStyles = array();

  // allowedTags can be an array of tag names, without < and > delimiters, 
  // or a continuous string of tag names bracketed by < and > (as strip_tags 
  // expects). 
  
  // By default, if the 'a' tag is in allowedTags, then we allow the href attribute on 
  // that (but not JavaScript links). If the 'img' tag is in allowedTags, 
  // then we allow the src attribute on that (but no JavaScript there either).
  // You can alter this by passing a different array of allowed attributes.

  // If $complete is true, the returned string will be a complete
  // HTML 4.x document with a doctype and html and body elements.
  // otherwise, it will be a fragment without those things
  // (which is what you almost certainly want).
  
  // If $allowedAttributes is not false, it should contain an array in which the
  // keys are tag names and the values are arrays of attribute names to be permitted.
  // Note that javascript: is forbidden at the start of any attribute, so attributes
  // that act as URLs should be safe to permit (we now check for leading space and
  // mixed case variations of javascript: as well).
  
  // If $allowedStyles is not false, it should contain an array in which the keys
  // are tag names and the values are arrays of CSS style property names to be permitted.
  // This is a much better idea than just allowing the style attribute, which is one
  // of the best ways to kill the layout of an entire page.
  //
  // An example:
  //
  // array("table" => array("width", "height"),
  //   "td" => array("width", "height"),
  //   "th" => array("width", "height"))
  //
  // Note that rich text editors vary in how they handle table width and height; 
  // Safari sets the width and height attributes of the tags rather than going
  // the CSS route. The simplest workaround is to allow that too.

  static public function simplify($value, $allowedTags = false, $complete = false, $allowedAttributes = false, $allowedStyles = false)
  {
    if ($allowedTags === false)
    {
      // Not using Symfony? Replace the entire sfConfig::get call with self::$defaultAllowedTags
      $allowedTags = sfConfig::get('app_aToolkit_allowed_tags', self::$defaultAllowedTags);
    }
    if ($allowedAttributes === false)
    {
      // See above
      $allowedAttributes = sfConfig::get('app_aToolkit_allowed_attributes', self::$defaultAllowedAttributes);
    }
    if ($allowedStyles === false)
    {
      // See above
      $allowedStyles = sfConfig::get('app_aToolkit_allowed_styles', self::$defaultAllowedStyles);
    }
    $value = trim($value);
    if (!strlen($value))
    {
      // An empty string is NOT something to panic
      // and generate warnings about
      return '';
    }
    if (is_array($allowedTags))
    {
      $tags = "";
      foreach ($allowedTags as $tag)
      {
        $tags .= "<$tag>";
      }
      $allowedTags = $tags;
    }
    $value = strip_tags($value, $allowedTags);

    // Now we use DOMDocument to strip attributes. In principle of course
    // we could do the whole job with DOMDocument. But in practice it is quite
    // awkward to hoist subtags correctly when a parent tag is not on the
    // allowed list with DOMDocument, and strip_tags takes care of that
    // task just fine.

    // At first I used matt@lvi.org's function from the strip_tags 
    // documentation wiki. Unfortunately preg_replace tends to return null
    // on some of his regexps for nontrivial documents which is pretty
    // disastrous. He seems to have some greedy regexps where he should
    // have ungreedy regexps. Let's do it right rather than trying to
    // make regular expressions do what they shouldn't.

    // We also get rid of javascript: links here, a good idea from 
    // Matt's script.
    
    $oldHandler = set_error_handler("aHtml::warningsHandler", E_WARNING);
    
    // If we do not have a properly formed <html><head></head><body></body></html> document then
    // UTF-8 encoded content will be trashed. This is important because we support fragments
    // of HTML containing UTF-8 as part of a
    if (!preg_match("/<head>/i", $value))
    {
      $value = '
      <html>
      <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
      </head>
      <body>
      ' . $value . '
      </body>
      </html>
      ';
    }
    try 
    {
      // Specify UTF-8 or UTF-8 encoded stuff passed in will turn into sushi.
      $doc = new DOMDocument('1.0', 'UTF-8');
      $doc->strictErrorChecking = true;
      $doc->loadHTML($value);
      self::stripAttributesNode($doc, $allowedAttributes, $allowedStyles);
      // Per user contributed notes at 
      // http://us2.php.net/manual/en/domdocument.savehtml.php
      // saveHTML forces a doctype and container tags on us; get
      // rid of those as we only want a fragment here
      $result = $doc->saveHTML();
    } catch (aHtmlNotHtmlException $e)
    {
      // The user thought they were entering text and used & accordingly (as they so often do)
      $result = htmlspecialchars($value);
    }

    if ($oldHandler)
    {
      set_error_handler($oldHandler);
    }
      
    if ($complete)
    {
      return $result;
    }

    $result = self::documentToFragment($result);
		return $result;
  }

  static public function documentToFragment($s)
  {
    return preg_replace(array('/^<!DOCTYPE.+?>/', '/<head>.*?<\/head>/i'), '', 
      str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $s));
  }
  
  static public function warningsHandler($errno, $errstr, $errfile, $errline) 
  {
    // Most warnings should be ignored as DOMDocument cleans up the HTML in exactly
    // the way we want. However "no name in entity" usually means the user thought they
    // were entering plaintext, so we should throw an exception signaling that
    
    if (strstr("no name in Entity", $errstr))
    {
      throw new aHtmlNotHtmlException();
    }
    return;
  }
  
  static private function stripAttributesNode($node, $allowedAttributes, $allowedStyles)
  {
    if ($node->hasChildNodes())
    {
      foreach ($node->childNodes as $child)
      {
        self::stripAttributesNode($child, $allowedAttributes, $allowedStyles);
      }
    }
    if ($node->hasAttributes())
    {
      $removeList = array();
      foreach ($node->attributes as $index => $attr)
      {
        $good = false;
        if ($attr->name === 'style')
        {
          if (isset($allowedStyles[$node->nodeName]))
          {
            // There is no handy function in core PHP to parse CSS rules, so we'll do it ourselves
            
            // First chop it into raw tokens as follows: /* ... */, \', \", ;, :, ', " and anything else
            $styles = array();
            $rawTokens = preg_split('/(\/\*.*?\*\/|\\\'|\\\"|;|:|\'|")/', $attr->value, null, PREG_SPLIT_DELIM_CAPTURE);
            // Now assemble quoted strings into single tokens, inclusive of escaped quotes, ;, :, etc. so that
            // we don't get tripped up by them later
            $realTokens = array();
            $single = false;
            $double = false;
            $s = '';
            foreach ($rawTokens as $rawToken)
            {
              if ($rawToken === "'")
              {
                if ($single)
                {
                  $single = false;
                  $realTokens[] = "'" . $s . "'";
                }
                else
                {
                  $single = true;
                  $s = '';
                }
              }
              elseif ($rawToken === '"')
              {
                if ($double)
                {
                  $double = false;
                  $realTokens[] = '"' . $s . '"';
                }
                else
                {
                  $double = true;
                  $s = '';
                }
              }
              else
              {
                if ($single || $double)
                {
                  $s .= $rawToken;
                }
                else
                {
                  $realTokens[] = $rawToken;
                }
              }
            }
            // Now we can just scan for semicolons and colons and make pretty rules
            $styles = array();
            $state = 'property';
            $p = '';
            $v = '';
						if (end($realTokens) !== ';')
						{
							$realTokens[] = ';';
						}
            foreach ($realTokens as $token)
            {
              if ($state === 'property')
              {
                if ($token === ':')
                {
                  $state = 'value';
                }
                else
                {
                  // We dump comments. Seems like a good idea in a tool used to clean up
                  // rich text editor output. If we didn't do this, we'd need a way to
                  // preserve them while still comparing names correctly
                  if (substr($token, 0, 2) !== '/*')
                  {
                    $p .= $token;
                  }
                }
              }
              elseif ($state === 'value')
              {
                if ($token === ';')
                {
                  // TODO: unescape quotes and unicode escapes in property names so
                  // we can compare them to the allowed properties, then reescape them
                  // when assembling the final rules. 
                  // 
                  // Not that hard given the tokenizing we've already done,
                  // but rich text editors don't generally introduce that nonsense
                  // into style attributes
                  $p = trim($p);
                  $styles[$p] = $v;
                  $p = '';
                  $v = '';
                  $state = 'property';
                }
                else
                {
                  // We dump comments. Seems like a good idea in a tool used to clean up
                  // rich text editor output
                  if (substr($token, 0, 2) !== '/*')
                  {
                    $v .= $token;
                  }
                }
              }
              else
              {
                throw new sfException('Unknown state in CSS parser in stripAttributesNode: ' . $state);
              }
            }
            $allowed = array_flip($allowedStyles[$node->nodeName]);
            $newStyles = array();
            foreach ($styles as $p => $v)
            {
              if (isset($allowed[$p]))
              {
                $newStyles[$p] = $v;
              }
            }
            $good = true;
            $rules = array();
            foreach ($newStyles as $p => $v)
            {
              $rules[] = "$p: $v;";
            }
            $attr->value = implode(' ', $rules);
          }
        }
        if (!$good)
        {
          if (isset($allowedAttributes[$node->nodeName]))
          {
            foreach ($allowedAttributes[$node->nodeName] as $attrName)
            {
              // Be more careful about this: leading space is tolerated by the browser,
              // so is mixed case in the protocol name (at least in Firefox and Safari, 
              // which is plenty bad enough)
              if (($attr->name === $attrName) && (!preg_match('/^\s*javascript:/i', $attr->value)))
              {
                // We keep this one
                $good = true;
              }
            }
          }
        }
        if (!$good)
        {
          // Off with its head
          $removeList[] = $attr->name; 
        }
      }
      foreach ($removeList as $name)
      {
        $node->removeAttribute($name);
      }
    }
  }

  // TODO: limitWords currently might not do a great job on typical
  // "gross" HTML without closing </p> tags and the like.

  static private $nonContainerTags = array(
    "br" => true,
    "img" => true,
    "input" => true
  );

	public static function limitWords($string, $word_limit)
	{
    # TBB: tag-aware, doesn't split in the middle of tags 
    # (we will probably use fancier tags with attributes later,
    # so this is important). Tags must be valid XHTML unless
    # all allowed tags 
	  $words = preg_split("/(\<.*?\>|\s+)/", $string, -1, 
      PREG_SPLIT_DELIM_CAPTURE);
    $wordCount = 0;
    # Balance tags that need balancing. We don't have strict XHTML
    # coming from OpenOffice (oh, if only) so we'll have to keep a
    # list of the tags that are containers.
    $open = array();
    $result = "";
    $count = 0;
    foreach ($words as $word) {
      if ($count >= $word_limit) {
        break;
      } elseif (preg_match("/\<.*?\/\>/", $word)) {
        # XHTML non-container tag, we don't have to guess
        $result .= $word;
        continue;
      } elseif (preg_match("/\<(\w+)/s", $word, $matches)) {
        $tag = $matches[1];
        $result .= $word;
        if (isset(aHtml::$nonContainerTags[$tag])) {
          continue;
        }
        $open[] = $tag;
      } elseif (preg_match("/\<\/(\w+)/s", $word, $matches)) {
        $tag = $matches[1];
        if (!count($open)) {
          # Groan, extra close tag, ignore
          continue;
        }
        $last = array_pop($open);    
        if ($last !== $tag) {
          # They closed the wrong tag. Again, ignore for now, but 
          # we might want to work on a better solution
          continue;
        }
        $result .= $word;
      } elseif (preg_match("/^\s+$/s", $word)) {
        $result .= $word;
      } else {
        if (strlen($word)) {
          $count++;
          $result .= $word;
        }
      }
    }
    for ($i = count($open) - 1; ($i >= 0); $i--) {
      $result .= "</" . $open[$i] . ">";
    }
    return $result;
  }

  public static function toText($html)
  {
    # Nothing fancy, we use the text for indexing only anyway.
    # It would be nice to do a prettier job here for future applications
    # that need pretty plaintext representations. That would be useful 
    # as an alt-body in emails
    $txt = strip_tags($html);
    return $txt;
  }

  public static function obfuscateMailto($html)
  {
    # Obfuscates any mailto: links found in $html. Good if you already
    # have nice HTML from FCK or what have you. 
   
    # Note that this updated version is AJAX-friendly
    # (it does not use document.write). Also, it preserves
    # the innerHTML of the original link rather than forcing it
    # to be the address found in the href.

    # ACHTUNG: mailto links will become simply
    # <a href="mailto:foo@bar.com">whatever-was-inside</a> (in the final
    # presentation to the user, after obfuscation via javascript). 
    # If there are other attributes on the <a> tag they will get tossed out.
    # This is usually not a problem for code that
    # comes from FCK etc. If it is a problem for you, make
    # this method smarter. Also consider just wrapping the link in
    # a span or div, which will not lose its class, id, etc. TBB

    return preg_replace("/\<a[^\>]*?href=\"mailto\:(.*?)\@(.*?)\".*?\>(.*?)\<\/a\>/ise", 
      "aHtml::obfuscateMailtoInstance(\"$1\", \"$2\", \"$3\")",
      $html);
  }
  
  public static function obfuscateMailtoInstance($user, $domain, $label)
  {
      // We get some weird escaping problems without the trims
      $user = trim($user);
      $domain = trim($domain);
      $guid = aGuid::generate();
      $href = self::jsEscape("mailto:$user@$domain");
      $label = self::jsEscape(trim($label));
      // ACHTUNG: this is carefully crafted to avoid introducing extra whitespace
      return "<a href='#' id='$guid'></a><script type='text/javascript' charset='utf-8'>
    	  var e = document.getElementById('$guid');
        e.setAttribute('href', '$href');
        e.innerHTML = '$label';
        </script>";
  }

  // This is intentionally obscure for use in mailto: obfuscators.
  // For an efficient way to pass data to javascript, use json_encode
  static public function jsEscape($str)
  {

    $new_str = '';

    for($i = 0; ($i < strlen($str)); $i++) {
      $new_str .= '\\x' . dechex(ord(substr($str, $i, 1)));
    }

    return $new_str;
  }

  /**
   * Just the basics: escape entities, turn URLs into links, and turn newlines into line breaks.
   * Also turn email addresses into links (we don't obfuscate them here as that makes them
   * harder to manipulate some more, but check out aHtml::obfuscateMailto). 
   *
   * This function is now a wrapper around TextHelper, except for the entity escape which is
   * not included in simple_format_text for some reason 
   *
   * @param string $text The text you want converted to basic HTML.
   * @return string Text with br tags and anchor tags.
   */
  static public function textToHtml($text)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Tag', 'Text'));
    return auto_link_text(simple_format_text(htmlentities($text, ENT_COMPAT, 'UTF-8')));
  }

  // For any given HTML, returns only the img tags. If 
  // format is set to array, the result is returned as an array
  // in which each element is an associative array with, at a
  // minimum, a src attribute and also width, height, alt and title
  // attributes if they were present in the tag. If format
  // is set to html, an array of the original <img> tags
  // is returned without further processing.
  static public function getImages($html, $format = 'array')
  {
    $allowed = array_flip(array("src", "width", "height", "title", "alt"));
    if (!preg_match_all("/\<img\s.*?\/?\>/i", $html, $matches, PREG_PATTERN_ORDER))
    {
      return array();
    }
    $images = $matches[0];
    if (empty($images))
    {
      return array();
    }
    
    if ($format == 'array')
    {
      $images_info = array();
      foreach ($images as $image)
      {
        // Use a backreference to make sure we match the same
        // type of quote beginning and ending
        preg_match_all('/(\w+)\s*=\s*(["\'])(.*?)\2/', 
          $image, 
          $matches, 
          PREG_SET_ORDER);
        $attributes = array();
        foreach ($matches as $attributeRaw)
        {
          $name = strtolower($attributeRaw[1]);
          $value = $attributeRaw[3];
          if (!isset($allowed[$name]))
          {
            continue;
          }
          $attributes[$name] = $value;
        }
        if (!isset($attributes['src']))
        {
          continue;
        }
        $images_info[] = $attributes;
      }
      
      return $images_info;
    }

    return $images;
  }
}
