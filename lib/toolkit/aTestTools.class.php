<?

/**
 * A collection of static methods useful for making tests easier to write.
 */
class aTestTools
{
  public static function randomString($length)
  {
    $string = '';
    for ($i=0;$i<$length;$i++)
    {
      $string = chr(rand(97,122));
    }
  }
}