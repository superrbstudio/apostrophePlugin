<?php

class aDimensions
{
  public static function constrain($originalWidth, $originalHeight, $originalFormat, $options)
  {
    if (!isset($options['width']))
    {
      throw new sfException("No width parameter in options");
    }
    $width = $options['width'];
    if (!isset($options['height']))
    {
      throw new sfException("No height parameter in options (specify false for flexHeight)");
    }
    $height = $options['height'];
    if ($height === false)
    {
      $height = ceil(($width * $originalHeight) / $originalWidth);
    }
    if (!isset($options['resizeType']))
    {
      throw new sfException("No resizeType parameter in options");
    }
    $resizeType = $options['resizeType'];
    $cropLeft = isset($options['cropLeft']) ? $options['cropLeft'] : null;
    $cropTop = isset($options['cropTop']) ? $options['cropTop'] : null;
    $cropWidth = isset($options['cropWidth']) ? $options['cropWidth'] : null;
    $cropHeight = isset($options['cropHeight']) ? $options['cropHeight'] : null;
    
    if (isset($options['scaleWidth']) && isset($options['scaleHeight']) && !is_null($cropLeft) && !is_null($cropTop) && !is_null($cropWidth) && !is_null($cropHeight))
    {
      $scalingFactor =  $originalWidth / $options['scaleWidth'];
            
      $cropLeft = floor($scalingFactor * $cropLeft);
      $cropTop = floor($scalingFactor * $cropTop);
      $cropWidth = floor($scalingFactor * $cropWidth);
      $cropHeight = floor($scalingFactor * $cropHeight);
    }
    
    if (!(isset($options['forceScale']) && $options['forceScale']))
    {
      // Never exceed original size, but don't exceed requested size on the other axis
      // as a consequence either
      if ($originalWidth < $width)
      {
        $height = ceil($height * ($originalWidth / $width));
        $width = $originalWidth;
      }
      if ($originalHeight < $height)
      {
        $width = ceil($width * ($originalHeight / $height));
        $height = $originalHeight;
      }
    }
    if (isset($options['format']))
    {
      $format = $options['format'];
    }
    else
    {
      $format = $originalFormat;
    }
    if ($format === 'pdf')
    {
      // aImageConverter can't render PDF as output anyway, so we know we will always
      // be converting pdf to something else
      $format = 'jpg';
    }
    
    return array("width" => $width, "height" => $height, "format" => $format, "resizeType" => $resizeType,
      "cropLeft" => $cropLeft, "cropTop" => $cropTop, "cropWidth" => $cropWidth, "cropHeight" => $cropHeight);
  }
}