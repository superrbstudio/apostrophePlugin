<?php
/**
 * 
 * TBB: these may seem trivial but they eliminate a lot of dumb
 * tests and gratuitous loop variables and accidental bugs in templates.
 * I was passing things by reference unnecessarily. That is fixed.
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aArray 
{

  /**
   * 
   * Return first element of array. If there isn't one, or it's
   * not an array, or it's not set, return false.
   * @param mixed $array
   * @return mixed
   */
  public static function first($array)
  {
    if (isset($array) && (is_array($array)) && (count($array) > 0))
    {
      return $array[0];
    }
    else
    {
      return false;
    }
  }

  /**
   * 
   * Return last element of array. If there isn't one, or it's
   * not an array, or it's not set, return false.
   * @param mixed $array
   * @return mixed
   */
  public static function last($array)
  {
    if (isset($array) && (is_array($array)) && (count($array) > 0))
    {
      return $array[count($array) - 1];
    }
    else
    {
      return false;
    }
  }

  /**
   * 
   * "One of these fields is bound to contain something interesting..."
   * Returns the first value in the array that isn't made up entirely
   * of trimmable whitespace.
   * @param mixed $array
   * @return mixed
   */
  public static function firstNontrivial($array)
  {
    foreach ($array as $value)
    {
      if (strlen(trim($value)))
      {
        return $value;
      }
    }
    return false;
  }

  /**
   * 
   * Sort an array by the stringification of each element.
   * Works for objects; would work fine for strings too.
   * Why this is not standard is a mystery to me.
   * @param mixed $array
   * @return mixed
   */
  public static function sort(&$array)
  {
    return usort($array, array('aArray', 'compare'));
  }

  /**
   * 
   * Same idea, case insensitive.
   * @param mixed $array
   * @return mixed
   */
  public static function sortInsensitive(&$array)
  {
    return usort($array, array('aArray', 'compareInsensitive'));
  }

  /**
   * 
   * Like array_search, this method returns the offset of the
   * value within the array, if it is present, false otherwise.
   * However, 'strict' has three possible values, extending its meaning in
   * the standard PHP array functions:
   * false: items are compared with ==
   * true: items are compared with ===
   * 'id': items are compared with the getId() method of the values,
   * which must be objects
   * If you find yourself calling this often in a loop, though, promise me
   * you'll create an associative array instead.
   * @param mixed $array
   * @param mixed $value
   * @param mixed $strict
   * @return mixed
   */
  public static function search($array, $value, $strict)
  {
    if ($strict === 'id')
    {
      $count = count($array);
      if (!$count)
      {
        return false;
      }
      $vid = $value->getId();
      for ($i = 0; ($i < $count); $i++)
      {
        if ($vid == $array[$i]->getId())
        {
          return $i;
        }
      }
      
      return false;
    }
    
    return array_search($array, $value, $strict);
  }

  /**
   * 
   * Search the array, find the item, return the index of the *previous*
   * item. If wrap is specified, a request for the first item
   * returns the last item, otherwise it returns false. If the
   * array is empty this function always returns false. If the item is
   * not in the array this function always returns false. If the
   * array is one element long the index of that element is returned.
   * Uses aArray::search(), so 'id' is an allowed value for $strict.
   * This is great for creating "Previous" links.
   * @param mixed $array
   * @param mixed $value
   * @param mixed $strict
   * @param mixed $wrap
   * @return mixed
   */
  public static function before($array, $value, $strict = false, $wrap = false)
  {
    $index = self::search($array, $value, $strict);
    if ($index === false)
    {
      return false;
    }
    if ($index == 0)
    {
      if ($wrap)
      {
        return count($array) - 1;
      }
      else
      {
        return false;
      }
    }
    
    return $index - 1;
  }

  /**
   * 
   * Search the array, find the item, return the index of the *next*
   * item. If wrap is specified, a request for the last item
   * returns the first item, otherwise it returns false. If the
   * array is empty this function always returns false. If the item is
   * not in the array this function always returns false. If the
   * array is one element long the index of that sole element is returned.
   * Uses aArray::search(), so 'id' is an allowed value for $strict.
   * This is great for creating "Next" links.
   * @param mixed $array
   * @param mixed $value
   * @param mixed $strict
   * @param mixed $wrap
   * @return mixed
   */
  public static function after($array, $value, $strict = false, $wrap = false)
  {
    $index = self::search($array, $value, $strict);
    if ($index === false)
    {
      return false;
    }
    if ($index == (count($array) - 1))
    {
      if ($wrap)
      {
        return 0;
      }
      else
      {
        return false;
      }
    }
    
    return $index + 1;
  }

  /**
   * 
   * Given an array of objects or arrays, return an array of ids
   * obtained by either calling getId() on each object or returning the 'id'
   * value of each child array.
   * @see listToHashById
   * @param mixed $array
   * @return mixed
   */
  public static function getIds($array)
  {
    if (count($array) === 0)
    {
      return array();
    }
  
    // is_object covers stdClass objects. instanceof Object doesn't. Yes this is weird.
    if (is_object($array[0]))
    {
      if (isset($array[0]->id))
      {
        return self::getResultsForProperty($array, 'id');
      }
      else
      {
        return self::getResultsForMethod($array, 'getId');      
      }
    }
    else
    {
      return self::getResultsForKey($array, 'id');      
    }
  }

  /**
   * 
   * Given an array of objects and a property name, return an array consisting
   * of the results obtained by fetching that property on each object
   * @param mixed $array
   * @param mixed $property
   * @return mixed
   */
  public static function getResultsForProperty($array, $property)
  {
    $results = array();
    foreach ($array as $item)
    {
      $results[] = $item->{$property};
    }
    
    return $results;
  }

  /**
   * 
   * Given an array of objects and a method, return an array consisting
   * of the results obtained by calling the method on each object
   * @param mixed $array
   * @param mixed $method
   * @return mixed
   */
  public static function getResultsForMethod($array, $method)
  {
    $results = array();
    foreach ($array as $item)
    {
      $results[] = call_user_func(array($item, $method));
    }
    
    return $results;
  }

  /**
   * 
   * Given an array of objects and a method, return an array consisting
   * of the results obtained by calling the method on each object
   * @param mixed $array
   * @param mixed $key
   * @return mixed
   */
  public static function getResultsForKey($array, $key)
  {
    $results = array();
    foreach ($array as $item)
    {
      $results[] = $item[$key];
    }
    
    return $results;
  }

  /**
   * 
   * Given a flat array of objects, returns an associative
   * array indexed by ids as returned by getId(). You can
   * specify an alternate id-fetching method. If the elements
   * are arrays, the 'id' field is retrieved instead
   * @param mixed $array
   * @param mixed $method
   * @return mixed
   */
  public static function listToHashById($array, $method = 'getId')
  {
    $hash = array();
    foreach ($array as $item)
    {
      if (is_array($item))
      {
        $hash[$item['id']] = $item;
      }
      else
      {
        $hash[$item->$method()] = $item;
      }
    }
    
    return $hash;
  }

  /**
   * Hashes 'id' to 'name', useful in select elements
   * @param mixed $array
   * @return mixed
   */
  public static function getChoices($array)
  {
    $hash = array();
    foreach ($array as $item)
    {
      $hash[$item->getId()] = $item->getName();
    }
    return $hash;
  }

  /**
   * 
   * Given an array of items, rearrange them into subarrays
   * by first letter of their string representation. Useful for directories
   * by first letter. You can specify an alternate callable to be used to fetch
   * the name of the item if conversion to a string doesn't do what you want
   * (for instance, if you're being lazy and using hashes where you really
   * ought to be using an object and defining a __toString() method).
   * @param mixed $array
   * @param mixed $getName
   * @return mixed
   */
  public static function byFirstLetter($array, $getName = null)
  {
    $alphabet = array_map('chr', range(ord('A'), ord('Z')));
    $result = array();
    foreach ($alphabet as $letter)
    {
      $result[$letter] = array();
    }
    foreach ($array as $item)
    {
      if (isset($getName))
      {
        $name = call_user_func($getName, $item);
      } else
      {
        $name = (string) $item;
      }
      $result[strtoupper(substr($name, 0, 1))][] = $item;
    }
    
    return $result;
  }

  /**
   * 
   * Remove the specified value, if present, from a flat array, returning a flat array lacking that element.
   * Not for use with associative arrays
   * @param mixed $a
   * @param mixed $v
   * @return mixed
   */
  public static function removeValue($a, $v)
  {
    $a = array_flip($a);
    unset($a[$v]);
    
    return array_keys($a);
  }

  /**
   * 
   * Filter out null values. Works on both flat and associative arrays
   * @param mixed $a
   * @return mixed
   */
  public static function filterNulls($a)
  {
    $b = array();
    foreach ($a as $key => $val)
    {
      if (is_null($val))
      {
        continue;
      }
      $b[$key] = $val;
    }
    return $b;
  }

  /**
   * 
   * Helpers for the above.
   * Compare two objects as strings via their string conversion methods.
   * @param mixed $a
   * @param mixed $b
   * @return mixed
   */
  public static function compare($a, $b)
  {
    // PHP 5.1.x doesn't apply __toString outside of
    // echo and print statements. Grr
    $s1 = self::toString($a);
    $s2 = self::toString($b);
    
    // If we knew we were on 5.2.x, we could just do this
    // $s1 = "$a";
    // $s2 = "$b";
    if ($s1 == $s2)
    {
      return 0;
    }
    return ($s1 < $s2) ? -1 : 1;
  }

  /**
   * 
   * Should be PHP 5.1.x-safe
   * @param mixed $a
   * @return mixed
   */
  private static function toString($a)
  {
    if (is_object($a) && method_exists($a, '__toString'))
    {
      return $a->__toString();
    } 
    else
    {
      return "$a";
    }
  }

  /**
   * 
   * Case insensitive version of the same thing
   * @param mixed $a
   * @param mixed $b
   * @return mixed
   */
  public static function compareInsensitive($a, $b)
  {
    // PHP 5.1.x doesn't apply __toString outside of
    // echo and print statements. Grr
    $s1 = strtolower(self::toString($a));
    $s2 = strtolower(self::toString($b));
    
    // If we knew we were on 5.2.x, we could just do this
    // $s1 = strtolower("$a");
    // $s2 = strtolower("$b");
    if ($s1 == $s2)
    {
      return 0;
    }
    
    return ($s1 < $s2) ? -1 : 1;
  }

  /**
   * Is this a numerically indexed array without gaps?
   * @param mixed $array
   * @return mixed
   */
  public static function isFlat($array)
  {
    $n = 0;
    foreach ($array as $key => $val)
    {
      if ($key !== $n)
      {
        return false;
      }
      $n++;
    }
    return true;
  }
}


