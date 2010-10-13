<?php

class sfConfig
{
  static public function get($key, $default)
  {
    return $default;
  }
}
require 'aImageConverter.class.php';

// aImageConverter::scaleToFit("testin.jpg", "testoutscaletofit.jpg", 400, 300);
// aImageConverter::scaleByFactor("testin.jpg", "testoutscalebyfactor.jpg", 0.5);
// aImageConverter::scaleToFit("testin.jpg", "testoutscaleoriginalbobbi.jpg", 340, 451);
// aImageConverter::cropOriginal("testin.jpg", "testoutcroporiginaltall.jpg", 100, 300);
// aImageConverter::scaleToNarrowerAxis("testin.jpg", "testoutscaletonarroweraxis.jpg", 300, 200);
// aImageConverter::cropOriginal("testin.jpg", "testoutcroporiginaltall.jpg", 100, 300);
aImageConverter::cropOriginal("testin.jpg", "testoutcroporiginalcorner.jpg", 100, 100, null, 200, 200, 200, 200);
