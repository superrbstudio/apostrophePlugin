<?php

/**
 * We are gradually refactoring the moving parts of LESS and Minify support into
 * this class from aHelper where they are too hard to reuse in different contexts
 */
 
class BaseaAssets
{
  /**
   * Compiles the less source file $path to the CSS file $compiled unless it already exists.
   * If the minifier is not turned on, or the checkIfModified option is passed, also check 
   * whether the destination is older than the source. 
   */
  static public function compileLessIfNeeded($path, $compiled, $options = array())
  {
    $checkIfModified = isset($options['checkIfModified']) && $options['checkIfModified'];
    if (!sfConfig::get('app_a_minify'))
    {
      $checkIfModified = true;
    }
    if ((!file_exists($compiled)) || ($checkIfModified && (filemtime($compiled) < filemtime($path))))
    {
      if (!isset($lessc))
      {
        // We do it like factories.yml does it, defaulting to the built in lessc.
        // this is a nice injection point because it's common to subclass lessc
        // The regular lessc class constructor doesn't take useful constructor parameters
        // as far as we're concerned, it doesn't even use its second argument '$opts'.
        // But you can write subclasses that take a useful options array as their
        // first argument
        $factory = sfConfig::get('app_a_lessc', array('class' => 'lessc', 'param' => null));
        $class = $factory['class'];
        $param = $factory['param'];
        $lessc = new $class($param);
      }
      $lessc->importDir = dirname($path) . '/';
      file_put_contents($compiled, $lessc->parse(file_get_contents($path)));
    }
  }

  
}
