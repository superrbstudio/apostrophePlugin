<?php
/**
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aDimensions
{

  /**
   * DOCUMENT ME
   * @param mixed $originalWidth
   * @param mixed $originalHeight
   * @param mixed $originalFormat
   * @param mixed $options
   * @return mixed
   */
  public static function constrain($originalWidth, $originalHeight, $originalFormat, $options)
  {
    if (!isset($options['width']))
    {
      throw new sfException("No width parameter in options (specify false for flexWidth)");
    }
    if (!isset($options['height']))
    {
      throw new sfException("No height parameter in options (specify false for flexHeight)");
    }
    $width = $options['width'];
    $height = $options['height'];
    if (($width === false) && ($height === false))
    {
      throw new sfException("Width and height can't both be false");
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
    
    $eWidth = $width;
    $eHeight = $height;
    if (isset($cropWidth))
    {
      $eWidth = $cropWidth;
      $eHeight = $cropHeight;
    }

    if ($width === false)
    {
      $width = ceil(($eHeight * $originalWidth) / $originalHeight);
    }
    if ($height === false)
    {
      $height = ceil(($eWidth * $originalHeight) / $originalWidth);
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