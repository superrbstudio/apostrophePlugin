<?php
/**
 * A simple inline backtrace that won't crash with an out of
 * memory error if the parameters are big objects (as
 * debug_print_backtrace sometimes does), clutter the
 * browser with huge var_dumps, etc. The trace can be
 * collapsed and expanded with a mouse click. Just call
 * aTrace::printTrace() from anywhere. Or call
 * aTrace::trace() to get the trace HTML code as
 * a string, which may be useful in contexts where you're building
 * something up in a helper.
 * 
 * Plaintext versions are also available and quite useful, call
 * aTrace::traceText() for a plaintext trace as a string and
 * aTrace::printTraceText() to echo it as text. To log it
 * to the Symfony log with 'info' priority, call
 * aTrace::traceLog(). You can then grep for method names. Super useful.
 * 
 * I don't use a javascript toolkit here because this ought to work in sites
 * built with any of them.
 * 
 * 2009-08-10: migrated from apostrophePlugin to reduce plugin count bloat.
 * Shortened class name for convenience. Added traceLog().
 * 
 * 2009-12-03: loads helpers the Symfony 1.3+ way.
 * 
 * tom@punkave.com
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aTrace
{
  static $traceId = 0;

  /**
   * DOCUMENT ME
   * @param mixed $ignoreCount
   * @return mixed
   */
  static public function trace($ignoreCount = 1)
  {
    $result = "";
    self::$traceId++;
    $traceId = "aTrace" . self::$traceId;
    $traceIdShow = $traceId . "Show";
    $traceIdHide = $traceId . "Hide";
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Tag', 'JavascriptBase'));
    $result .= "<div class='aTrace'>Trace " . 
      link_to_function("&gt;&gt;&gt;", 
        "document.getElementById('$traceId').style.display = 'block'; " .
        "document.getElementById('$traceIdShow').style.display = 'none'; " .
        "document.getElementById('$traceIdHide').style.display = 'inline'",
        array("id" => $traceIdShow)) .
      link_to_function("&lt;&lt;&lt;", 
        "document.getElementById('$traceId').style.display = 'none'; " .
        "document.getElementById('$traceIdHide').style.display = 'none'; " .
        "document.getElementById('$traceIdShow').style.display = 'inline'",
        array("id" => $traceIdHide, "style" => 'display: none'));
    $result .= "</div>";
    $result .= "<pre id='$traceId' style='display: none'>\n";
    $result .= self::traceText($ignoreCount + 1);
    $result .= "</pre>\n";
    return $result;
  }

  /**
   * DOCUMENT ME
   */
  static public function printTrace()
  {
    echo(self::trace(2));
  }

  /**
   * Now you can pass in a trace from a getTrace() call on an exception object
   * @param mixed $ignoreCount
   * @param mixed $trace
   * @return mixed
   */
  static public function traceText($ignoreCount = 1, $trace = null, $maxDepth = null)
  {
    if ($trace === null)
    {
      $trace = debug_backtrace();    
    }
    $count = 0;
    $result = "";
    $lastLine = 'NONE';
    foreach ($trace as $element)    
    {
      $count++;
      if ($count > $ignoreCount)
      {
        $result .= "Class: " . (isset($element['class']) ? $element['class'] : 'NONE') . " function: " . $element['function'] . " line: " . $lastLine . " File: " . (isset($element['file']) ? $element['file'] : 'NONE') . "\n";
      }
      if (isset($element['line']))
      {
        $lastLine = $element['line'];
      }
      else
      {
        $lastLine = 'NONE';
      }
      if (isset($maxDepth) && ($count - $ignoreCount >= $maxDepth))
      {
        break;
      }
    }
    return $result;
  }

  /**
   * DOCUMENT ME
   */
  static public function printTraceText()
  {
    echo(self::traceText(2));
  }

  /**
   * DOCUMENT ME
   */
  static public function traceLog()
  {
    sfContext::getInstance()->getLogger()->info(self::traceText());
  }
}
