<?php
/**
 * 
 * HTML related utilities. HTML markup to RSS markup conversion,
 * simplification of HTML to a short list of legal tags and no
 * dangerous attributes, mailto: obfuscation, word count limit
 * that preserves valid HTML markup, and basic text-to-HTML
 * conversion that preserves line breaks and creates links.
 * doc-to-HTML conversion has been removed as it's out of scope for
 * apostrophePlugin which should contain lightweight stuff only.
 * We should consider putting that out as a separate plugin.
 * @author Tom Boutell <tom@punkave.com>
 * @package    apostrophePlugin
 * @subpackage    toolkit
 */
class aHtmlNotHtmlException extends Exception
{
  
}/**
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
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

  /**
   * Right now this just converts obscure HTML entities to
   * simpler stuff that all feed readers will digest.
   * @param mixed $doc
   * @return mixed
   */
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

  /**
   * DOCUMENT ME
   * @param mixed $entity
   * @return mixed
   */
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

  // loadHtml, in its infinite wisdom, insists on giving us br tags
  // without a proper /> at the end. Force a fix by default (thanks to
  // Geoff Hammond). This is done in a simple way that would have problems 
  // if you were allowing script elements, but why would you do such a foolish thing?
  static private $defaultHtmlStrictBr = true;

  /**
   * DOCUMENT ME
   * @param mixed $value
   * @param mixed $allowedTags
   * @param mixed $complete
   * @param mixed $allowedAttributes
   * @param mixed $allowedStyles
   * @param mixed $htmlStrictBr
   * @return mixed
   */
  static public function simplify($value, $allowedTags = false, $complete = false, $allowedAttributes = false, $allowedStyles = false, $htmlStrictBr = false)
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
    if ($htmlStrictBr === false)
    {
      // See above
      $htmlStrictBr = sfConfig::get('app_aToolkit_html_strict_br', self::$defaultHtmlStrictBr);
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

    if ($complete)
    {
      // Don't allow whitespace to balloon
      $result = trim($result);
    }
    else
    {
      $result = self::documentToFragment($result);
    }

    // Browser RTEs insert  <p>&nbsp;</p> at the beginning and
    // <p>&nbsp;</p> at the end to work around bugs in the actual rich text
    // editing component in the browser. Pull this brain damage back out

    $result = preg_replace(array('|^\s*<p>\s*&nbsp;\s*</p>|s', '|<p>\s*&nbsp;\s*</p>\s*$|s'), array('', ''), $result);

    // Browser RTEs love to insert <p>&nbsp;</p> where <br /> is all they really need.
    // There are more elaborate cases we don't mess with because 
    // introducing a <br /> as a replacement for <h4>&nbsp;</h4> would not
    // have the same impact (an h4-sized gap between two h4s). Tested across
    // browsers. Fixes #500
    $result = preg_replace('|<p>\s*&nbsp;\s*</p>|s', '<br />', $result);
    
    if($htmlStrictBr)
    {
      $result = str_replace('<br>', '<br />', $result);
    }

    if ($oldHandler)
    {
      set_error_handler($oldHandler);
    }
    return $result;
  }

  /**
   * DOCUMENT ME
   * @param mixed $s
   * @return mixed
   */
  static public function documentToFragment($s)
  {
    // Added trim call because otherwise size begins to balloon indefinitely
    return trim(preg_replace(array('/^<!DOCTYPE.+?>/', '/<head>.*?<\/head>/i'), '', 
      str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $s)));
  }

  /**
   * DOCUMENT ME
   * @param mixed $errno
   * @param mixed $errstr
   * @param mixed $errfile
   * @param mixed $errline
   * @return mixed
   */
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

  /**
   * DOCUMENT ME
   * @param mixed $node
   * @param mixed $allowedAttributes
   * @param mixed $allowedStyles
   */
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

  /**
   * DOCUMENT ME
   * @param mixed $string
   * @param mixed $word_limit
   * @param mixed $options
   * @return mixed
   */
  public static function limitWords($string, $word_limit, $options = array())
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
    $num_words = count($words);
    
    $shortEnough = true;
    
    foreach ($words as $word) {
      if ($count > $word_limit) {
        $shortEnough = false;
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
  
    if ($shortEnough)
    {
      // Leave it totally untouched if it is short enough.
      // Now you can use !== to see if it changed anything.
      return $string;
    }

    $append_ellipsis = false;
    if (isset($options['append_ellipsis']))
    {
      $append_ellipsis = $options['append_ellipsis'];
    }
    if ($append_ellipsis == true && $num_words > $word_limit)
    {
      $result .= '&hellip;';
    }

    for ($i = count($open) - 1; ($i >= 0); $i--) {
      $result .= "</" . $open[$i] . ">";
    }
    return $result;
  }

  /**
   * This is a quick and dirty implementation based on calling limitWords
   * with an optimistic guess and then backing off a few times if necessary
   * until we get under the byte limit. Note that limitBytes is designed
   * to fit things in buffers, not save screen space, so it does have to
   * make sure the result is not too big
   * @param mixed $string
   * @param mixed $byte_limit
   * @param mixed $options
   * @return mixed
   */
  public static function limitBytes($string, $byte_limit, $options = array())
  {
    $word_limit = (int) ($byte_limit / 8);
    while (true)
    {
      $s = aHtml::limitWords($string, $word_limit, $options);
      if (strlen($s) <= $byte_limit)
      {
        break;
      }
      $word_limit = (int) ($word_limit * 0.75);
    }
    return $s;
  }

  /**
   * DOCUMENT ME
   * @param mixed $html
   * @return mixed
   */
  public static function toText($html)
  {
    # Nothing fancy, we use the text for indexing only anyway.
    # It would be nice to do a prettier job here for future applications
    # that need pretty plaintext representations. That would be useful 
    # as an alt-body in emails. This does not entity-decode. See
    // toPlaintext for that
    $txt = strip_tags($html);
    return $txt;
  }

  /**
   * DOCUMENT ME
   * @param mixed $html
   * @return mixed
   */
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

    return preg_replace_callback("/\<a[^\>]*?href=\"mailto\:(.*?)\@(.*?)\".*?\>(.*?)\<\/a\>/is", 
      array('aHtml', 'obfuscateMailtoInstance'),
      $html);
  }

  /**
   * DOCUMENT ME
   * @param mixed $args
   * @return mixed
   */
  public static function obfuscateMailtoInstance($args)
  {
    list($user, $domain, $label) = array_slice($args, 1);
    // We get some weird escaping problems without the trims
    $user = trim($user);
    $domain = trim($domain);
    // Cripes... crc has to include user, domain *and* label to make it unique.
    // This is worth it to produce cacheable content though
    $class = 'a-email-' . sprintf("%u", crc32($user . '@' . $domain . ':' . $label));
    $href = "mailto:$user@$domain";
    if (sfConfig::get('app_a_inline_obfuscate_mailto'))
    {
      $result = '<a href="#" class="a-obs-email ' . $class . '" data-prefix="' . $user . '" data-suffix="' . $domain . '" data-label="' . $label . '"></a>';
    }
    else
    {
      // This is an acceptable way to stub in a js call for now, since it's the
      // way the helper has to do it too
      $result = "<a href='#' class='$class'></a>";  
      $label = rawurlencode(trim($label));          
      $href = rawurlencode($href);      
      aTools::$jsCalls[] = array('callable' => 'apostrophe.unobfuscateEmail(?, ?, ?)', 'args' => array($class, $href, $label));
    }
    return $result;
  }

  /**
   * This is intentionally obscure for use in mailto: obfuscators.
   * For an efficient way to pass data to javascript, use json_encode
   * @param mixed $str
   * @return mixed
   */
  static public function jsEscape($str)
  {

    $new_str = '';

    for($i = 0; ($i < strlen($str)); $i++) {
      $new_str .= '\\x' . dechex(ord(substr($str, $i, 1)));
    }

    return $new_str;
  }

  /**
   * 
   * Just the basics: escape entities, turn URLs into links, and turn newlines into line breaks.
   * Also turn email addresses into links (we don't obfuscate them here as that makes them
   * harder to manipulate some more, but check out aHtml::obfuscateMailto).
   * This function is now a wrapper around TextHelper, except for the entity escape which is
   * not included in simple_format_text for some reason
   * @param string $text The text you want converted to basic HTML.
   * @param bool $newlines If true, convert newlines to line breaks (call simple_format_text).
   * @return string Text with br tags and anchor tags.
   */
  static public function textToHtml($text, $newlines = true)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Tag', 'Text'));
    $text = aHtml::entities($text);
    if ($newlines)
    {
      $text = simple_format_text($text);
    }
    return auto_link_text($text);
  }
  
  /**
   * Convert anything that looks like a hostname into a proper URL so that
   * textToHtml will pick up on it and turn it into a link if called next.
   * Tom chipped this in from his wejoinem project 10/21/11
   * @param string $text
   * @return string
   */
  static public function lazyUrls($text)
  {
    $text = preg_replace_callback('/([a-z]+:\/\/)?[A-Za-z0-9\-]+\.[A-Za-z0-9\-\.]+(\/\S*)?/', array('aHtml', '_cleanupUrl'), $text);
    return $text;
  }
  
  /**
   * Callback for lazyUrls, not really public. Handles one match, converting a hostname
   * to a URL if it isn't already. Tom chipped this in from his wejoinem project 10/21/11
   */
  static public function _cleanupUrl($matches)
  {
    $url = $matches[0];
    if (!preg_match('/[a-z]+\:/', $url))
    {
      $url = 'http://' . $url;
    }
    return $url;
  }
  
  /**
   * UTF-8 entity escapes the provided text, and nothing else
   */
  static public function entities($text)
  {
    return htmlentities($text, ENT_COMPAT, 'UTF-8');
  }

  /**
   * For any given HTML, returns only the img tags. If
   * format is set to array, the result is returned as an array
   * in which each element is an associative array with, at a
   * minimum, a src attribute and also width, height, alt and title
   * attributes if they were present in the tag. If format
   * is set to html, an array of the original <img> tags
   * is returned without further processing.
   * @param mixed $html
   * @param mixed $format
   * @return mixed
   */
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

  /**
   * Converts input string to true plaintext (BE CAREFUL: use aHtml::entities() if you display
   * it later). Nonbreaking spaces become vanilla spaces, tags re removed and entities are
   * decoded according to UTF-8
   * @param string $html
   * @return string
   */
  static public function toPlaintext($html)
  {
    // Nonbreaking spaces don't work properly
    // in a lot of contexts where plaintext is
    // needed
    return html_entity_decode(str_replace('&nbsp;', ' ', strip_tags($html)), ENT_COMPAT, 'UTF-8');
  }
  
  /**
   * Accepts an embed code from a service like Wufoo or Etsy and attempts to fix it to
   * be loaded safely via AJAX. document.write() is temporarily overridden to append 
   * content to the specified selector rather than blanking the entire page, and any
   * 'script src' tags are converted to jQuery.getScript() calls. This doesn't work 
   * for every possible edge case but it is often effective. 
   *
   * If the embed code makes document.write() calls to dynamically insert more
   * <script src="..."> tags, we're out of luck - it is not possible to dynamically
   * but synchronously load cross-domain JavaScript files (see the jQuery async docs,
   * for example, which explicitly warn the async flag is no help here). So instead
   * a message saying "Refresh the page to see the result" is shown to the user and
   * apostrophe.log is called with information about the JS file the embed code tried
   * to load. You can implement loading of that file by some other means, in which
   * case you'll want to specify $options['ignoreDynamicScriptSrc'] = true so that
   * the 'refresh the page' message stops appearing and the document.write() call 
   * is harmlessly ignored.
   *
   * Returns jQuery code, wrapped in a domready function. Stuff it in a script block
   * or emit it as the success function of a $.getScript() call.
   *
   * @param string $code
   * @param string $appendToSelector
   * @param array $options
   * @return string (the AJAX-friendly embed code)
   */
  static public function ajaxifyEmbedCode($code, $appendToSelector, $options = array())
  {
    // Find any <script src="foo.js"></script> tags, capture their src URL, and
    // remove them from the markup. These don't work as-is in AJAX responses
        
    $count = preg_match_all('|<script.*?src=[\'"]([^\'"]+)[\'"].*?</script>|', $code, $matches); 
    if ($count)
    {
      $srcs = $matches[1];
      $code = preg_replace('|<script.*?src=[\'"]([^\'"]+)[\'"].*?</script>|', '', $code);
    }
    else
    {
      $srcs = array();
    }

    // Build up the response by successively decorating it from the inside out. First the
    // simple append call to add the markup to the selector, then the jQuery.getScript calls
    // to load the required javascripts, and then the logic to override document.write() to
    // also append to the selector
    
    $escapedSelector = json_encode($appendToSelector);
    $escapedCode = json_encode($code);
    
    $result = <<<EOM
$($escapedSelector).append($escapedCode);
$($escapedSelector).append(apostropheDocumentWriteBuffer);
document.write = apostropheSaveDocumentWrite;
EOM
;
    // Reverse the order so that the file that should be loaded first is the outermost
    // getScript() call
    $srcs = array_reverse($srcs);
    
    foreach ($srcs as $src)
    {
      $escapedSrc = json_encode($src);
      $result = <<<EOM
jQuery.getScript($escapedSrc, function() {
  $result
});
EOM
;
    }

    // If they make dynamic document.write calls to generate script src tags,
    // flag that to be special-cased at a higher level. I have some bad news:
    // there is no reliable way to synchronously load js files from another domain
    // (read that whole sentence carefully please) and jQuery doesn't try, even if
    // the async flag is false. So we really can't fix this automagically.

    if ((!isset($options['ignoreDynamicScriptSrc'])) || (!$options['ignoreDynamicScriptSrc']))
    {
      $catchDynamicScriptSrc = <<<EOM
      var scriptSrcRegex = /<script.*?src=[\'"]([^\'"]+)[\'"].*?<\/script>/g;      
      if (matches = scriptSrcRegex.exec(markup))
      {
        var src = matches[1];
        apostrophe.log("ajaxifyEmbedCode cannot synchronously load this cross-domain .js file: " + src);
        $($escapedSelector).append("Refresh the page to see the result.");
        return;
      }
      markup.replace(scriptSrcRegex, '');
EOM
;
    }
    else
    {
      $catchDynamicScriptSrc = '';
    }

    $result = <<<EOM
  $(function() {
    var apostropheSaveDocumentWrite = document.write;
    var apostropheDocumentWriteBuffer = '';
    document.write = function(markup) {
      $catchDynamicScriptSrc
      apostropheDocumentWriteBuffer += markup;
    };
    $result
  });
EOM
;
    return $result;
  }
}
