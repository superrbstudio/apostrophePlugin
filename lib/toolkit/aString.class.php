<?php

/**
 * Tools, utilities and snippets collected and composed...
 */

class aString
{
	/**
	* Limits the number of words in a string.
	*
	* @param string $string
	*
	* @param uint $word_limit
	*   number of words to return
	* 
	* @param optional array
	* 	if $options['append_ellipsis'] is set, append an ellipsis to the end 
  *   of strings that have been truncated
	*
	* @return string
	*   new string containing only words up to the word limit.
	*/
	public static function limitWords($string, $word_limit, $options = array())
	{
	  $words = explode(' ', $string, $word_limit + 1);
    $num_words = count($words);

		# TBB: if there are $word_limit words or less, this check is necessary
    # to prevent the last word from being lost.
		if ($num_words > $word_limit)
		{
      array_pop($words);
    }
	  
		$string = implode(' ', $words);
		
		$append_ellipsis = false;
		if (isset($options['append_ellipsis']))
		{
			$append_ellipsis = $options['append_ellipsis'];
		}
		if ($append_ellipsis == true && $num_words > $word_limit)
		{
			$string .= '...';
		}
		
		return $string;
	}

	/**
	* Limits the number of characters in a string.
	*
	* @param string $string
	*
	* @param uint $character_limit
	*   maximum number of characters to return, inclusive of any added ellipsis
	* 
	* @param optional array
	* 	if $options['append_ellipsis'] is set, append an ellipsis to the end 
  *   of strings that have been truncated
	*
	* @return string
	*   new string containing only characters up to the limit
  * 
  * Suitable when a word count limit is not enough (because words are
  * sometimes unreasonably long).
  *
  * Tries to preserve word boundaries, but not too hard, as very long words can
  * create problems of their own.
	*/
  public static function limitCharacters($s, $length, $options = array())
  {
    $ellipsis = "";
    if (isset($options['append_ellipsis']) && $options['append_ellipsis'])
    {
      $ellipsis = "...";
    }
    if ($length < 12)
    {
      // Not designed to be elegant below this length
      return substr($s, 0, $length);
    }
    if (strlen($s) > $length)
    {
      $s = substr($s, 0, $length - strlen($ellipsis));
      $slength = strlen($s);
      for ($i = 1; ($i <= 10); $i++)
      {
        $c = substr($s, $slength - $i, 1);
        if (($c === ' ') || ($c === '\t') || ($c === '\r') || ($c === '\n'))
        {
          return substr($s, 0, $slength) . $ellipsis;
        }
      }
      return $s . $ellipsis;
    }
    return $s;
  }
	
 	/**
  *
	* Accepts an array of keywords and a text; returns the portion of the
  * text beginning a few words prior to the first keyword encountered,
  * and continuing to the end of the text. If none of the keywords are
  * seen, returns the entire text.
  *
	* @param array $terms keywords
  * @param string $text
	*
	* @return string
  *
	*/
  public static function beginNear($keywords, $text)
  {
    foreach ($keywords as $keyword) {
      # TODO: can we do this without so many calls? I don't want
      # to capture an arbitrary number of words preceding - no more
      # than three - and I don't want to reject cases with fewer
      # than three preceding either. 
      $keyword = addslashes($keyword);
      for ($wordsPreceding = 3; ($wordsPreceding >= 0); $wordsPreceding--) {
        $regexp = "(" . 
          str_repeat("\w+\W+", $wordsPreceding) . ")(" . $keyword . ")" . "(.*)/is";
        if (preg_match("/^" . $regexp, $text, $matches)) {
          return $matches[1] . "<b>" . $matches[2] . "</b>" . $matches[3]; 
        } 
        if (preg_match("/" . $regexp, $text, $matches)) {
          return "... " . $matches[1] . "<b>" . $matches[2] . "</b>" . $matches[3]; 
        } 
      }
    }
    return false;
  }
  
 	/**
  *
	* Accepts two text strings; returns a human-friendly representation of
	* the difference between them. The strategy is to word-wrap the strings
	* at a reasonably short boundary, split at line breaks, and then use
	* array_diff (in both directions) to discover differences. This function
	* returns an array like this:
	*
	* array(
  *   "onlyin1" => 
	*     array("first line unique to 1", "second line unique to 1..."), 
	*   "onlyin2" => 
	*     array("first line unique to 2", "second line unique to 2...")
	* )
	* It is suggested that, at a minimum, the first line of
	* onlyin1 be displayed (with visual cues to indicate that it is gone in 2)
	* and the first line of onlyin2 also be displayed (with visual cues to indicate
	* that is new in 2). 
	*
	* TODO: detect situations in which content has been purely rearranged rather
	* than edited, deleted or added, add preceding and trailing context, etc.
	* These are all going to be a lot less efficient than this simple
	* implementation though.
  *
	* @param string $text1
  * @param string $text2
	*
	* @return array
  *
	*/
  
  public static function diff($text1, $text2)
  {
    $array1 = array_map('trim', explode("\n", wordwrap($text1, 70)));
    $array2 = array_map('trim', explode("\n", wordwrap($text2, 70)));
    $onlyin1 = array_values(array_diff($array1, $array2));
    $onlyin2 = array_values(array_diff($array2, $array1));
    if (count($onlyin1) && count($onlyin2))
    {
      // The first line is critical because history displays
      // so little of a diff. So remove any shared prefix from the
      // first deleted and first added lines unless that means we'd
      // take it all
      $s1 = $onlyin1[0];
      $s2 = $onlyin2[0];
      if (strlen($s1) !== strlen($s2))
      {
        $min = min(strlen($s1), strlen($s2));
        for ($i = 0; ($i < $min); $i++)
        {
          $c1 = substr($s1, $i, 1);
          $c2 = substr($s2, $i, 1);
          if ($c1 !== $c2)
          {
            break;
          }
        }
        $onlyin1[0] = substr($s1, $i);
        $onlyin2[0] = substr($s2, $i);
        if (!strlen($onlyin1[0]))
        {
          array_shift($onlyin1);
        }
        if (!strlen($onlyin2[0]))
        {
          array_shift($onlyin2);
        }
      }
    }
    return array("onlyin1" => array_values($onlyin1), "onlyin2" => array_values($onlyin2));
  }
}

?>
