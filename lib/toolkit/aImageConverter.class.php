<?php
/**
 * 
 * Efficient image conversions using netpbm or (if netpbm is not available) gd.
 * For more information see the README file.
 * 
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aImageConverter 
{

  /**
   * Produces images suitable for intentional cropping by CSS.
   * Either the width or the height will match the request; the other
   * will EXCEED the request. Looks nicer than letterboxing in cases
   * where keeping the entire picture is not essential.
   * @param mixed $fileIn
   * @param mixed $fileOut
   * @param mixed $width
   * @param mixed $height
   * @param mixed $quality
   * @return mixed
   */
  static public function scaleToNarrowerAxis($fileIn, $fileOut, $width, $height, $quality = 75)
  {
    $width = ceil($width);
    $height = ceil($height);
    $quality = ceil($quality);
    list($iwidth, $iheight) = @getimagesize($fileIn); 
    if (!$iwidth) {
      return false;
    }
    $iratio = $iwidth / $iheight;
    $ratio = $width / $height;
    if ($iratio > $ratio) {
      $width = false;
    } else {
      $height = false;
    }
    return self::scaleToFit($fileIn, $fileOut, $width, $height, $quality);
  }

  /**
   * DOCUMENT ME
   * @param mixed $fileIn
   * @param mixed $fileOut
   * @param mixed $width
   * @param mixed $height
   * @param mixed $quality
   * @return mixed
   */
  static public function scaleToFit($fileIn, $fileOut, $width, $height, $quality = 75)
  {
    if ($width === false) {
      $scaleParameters = array('ysize' => $height + 0);
    } elseif ($height === false) {
      $scaleParameters = array('xsize' => $width + 0);
    } else {
      $scaleParameters = array('xysize' => array($width + 0, $height + 0));
    }
    $result = self::scaleBody($fileIn, $fileOut, $scaleParameters, array(), $quality);
    return $result;
  }

  /**
   * DOCUMENT ME
   * @param mixed $fileIn
   * @param mixed $fileOut
   * @param mixed $factor
   * @param mixed $quality
   * @return mixed
   */
  static public function scaleByFactor($fileIn, $fileOut, $factor, 
    $quality = 75)
  {
    $quality = ceil($quality);
    $scaleParameters = array('scale' => $factor + 0);  
    return self::scaleBody($fileIn, $fileOut, $scaleParameters, array(), $quality);
  }

  /**
   * $width and $height are the dimensions of the final rendered image. $quality is the JPEG quality setting (where needed).
   * The $crop parameters, when not null (all four must be null or not null), are used to crop the original before scaling/distorting
   * to the specified width and height and are always in the original image's coordinates.
   * If cropping coordinates are not specified, the largest possible portion of the center of the original image is scaled to fit into the
   * destination image without distortion
   * @param mixed $fileIn
   * @param mixed $fileOut
   * @param mixed $width
   * @param mixed $height
   * @param mixed $quality
   * @param mixed $cropLeft
   * @param mixed $cropTop
   * @param mixed $cropWidth
   * @param mixed $cropHeight
   * @return mixed
   */
  static public function cropOriginal($fileIn, $fileOut, $width, $height, $quality = 75, $cropLeft = null, $cropTop = null, $cropWidth = null,  $cropHeight = null)
  {
    $args = func_get_args();
    // Allow skipping of parameters
    if (is_null($quality))
    {
      $quality = 75;
    }
    $width = ceil($width);
    $height = ceil($height);
    $quality = ceil($quality);
    // Make sure we use a method that understands about JPEG orientation
    $info = aImageConverter::getInfo($fileIn); 
    if (!$info)
    {
      return false;
    }
    $iwidth = $info['width'];
    $iheight = $info['height'];
    
    $iratio = $iwidth / $iheight;
    $ratio = $width / $height;

     // Spike's contribution: arbitrary cropping
     if (!is_null($cropWidth) && !is_null($cropHeight) && !is_null($cropLeft) && !is_null($cropTop))
     {
       $cropTop = ceil($cropTop + 0);
       $cropLeft = ceil($cropLeft + 0);
       $cropWidth = ceil($cropWidth + 0);
       $cropHeight = ceil($cropHeight + 0);
       
       $scale = array('xysize' => array($width + 0, $height + 0));
       $crop = array('left' => $cropLeft, 'top' => $cropTop, 'width' => $cropWidth, 'height' => $cropHeight);
       return self::scaleBody($fileIn, $fileOut, $scale, $crop, $quality);
     }

    $scale = array('xysize' => array($width + 0, $height + 0));
    if ($iratio < $ratio)
    {
      $cropHeight = floor($iwidth * ($height / $width));
      $cropTop = floor(($iheight - $cropHeight) / 2);
      $cropLeft = 0;
      $cropWidth = $iwidth;
    }
    else
    {
      $cropWidth = floor($iheight * $ratio);
      $cropLeft = floor(($iwidth - $cropWidth) / 2);
      $cropTop = 0;
      $cropHeight = $iheight;
    }
    $scale = array('xysize' => array($width + 0, $height + 0));
    $crop = array('left' => $cropLeft, 'top' => $cropTop, 'width' => $cropWidth, 'height' => $cropHeight);
    return self::scaleBody($fileIn, $fileOut, $scale, $crop, $quality);
  }

  /**
   * Change the format without cropping or scaling
   * @param mixed $fileIn
   * @param mixed $fileOut
   * @param mixed $quality
   * @return mixed
   */
  static public function convertFormat($fileIn, $fileOut, $quality = 75)
  {
    $quality = ceil($quality);
    return self::scaleBody($fileIn, $fileOut, false, false, $quality);
  }

  /**
   * When you know that a local file is a copy of a path in S3 or some other slow storage
   * for at least the duration of the current request, you can set $localPathCache['slow remote path'] = 'fast local path'
   * and that will be used in place of 'slow remote path' when it shows up in the $fileIn parameter
   * of scaleBody
   */
  static public $localPathCache = array();
  
  /**
   * Scale and crop $fileIn to $fileOut, potentially converting the format as well
   */
  static private function scaleBody($fileIn, $fileOut, $scaleParameters = array(), $cropParameters = array(), $quality = 75) 
  {    
    if (!isset(aImageConverter::$localPathCache[$fileIn]))
    {
      if (preg_match('/^\w+:\/\//', $fileIn))
      {
        // For paths with a protocol, copy to fast local temporary storage first. In addition to
        // enabling netpbm use this also speeds up the generation of several images during
        // a single request
        $tmp = aFiles::getTemporaryFilename();
        aImageConverter::$localPathCache[$fileIn] = aFiles::getTemporaryFilename();
        copy($fileIn, aImageConverter::$localPathCache[$fileIn]);
        $fileIn = aImageConverter::$localPathCache[$fileIn];
      }
      else
      {
        // Middle case: original is a local file, don't cache anything
      }
    }
    else
    {
      $fileIn = aImageConverter::$localPathCache[$fileIn];
    }
    if (sfConfig::get('app_aimageconverter_netpbm', true))
    {
      // Auto fallback to gd, but only if it's not a small image gd can handle better (1.4). This means we get
      // full alpha channel for manageably-sized PNGs and good performance for huge PNGs
      $info = @getimagesize($fileIn);
      $mapTypes = array(IMAGETYPE_GIF => IMG_GIF, IMAGETYPE_PNG => IMG_PNG, IMAGETYPE_JPEG => IMG_JPG);
      // Usually the 1024x768 rule is better, but this is useful for testing
      if (sfConfig::get('app_aimageconverter_netpbm', true) === 'always')
      {
        return self::scaleNetpbm($fileIn, $fileOut, $scaleParameters, $cropParameters, $quality);
      }
      // Defaulting to gd when the image exceeds 1024 pixels wide or 768 pixels tall excludes a lot of
      // non-square images that actually have reasonable memory requirements when unpacked. Let's say what
      // we mean here and base the limitation on bytes required, period.
      if ($info !== false)
      {
        $bytes = $info[0] * $info[1] * 4;
      }
      // If we got valid image info, the image requires less than 4MB to fully unpack in RAM (load in gd), , gd is enabled, 
      // and gd supports the image type... *then* we skip to gd.
      if (($info !== false) && ($bytes <= 4 * 1024 * 1024) && function_exists('imagetypes') && isset($mapTypes[$info[2]]) && (imagetypes() & $mapTypes[$info[2]]))
      {
        return self::scaleGd($fileIn, $fileOut, $scaleParameters, $cropParameters, $quality);
      }
      $result = self::scaleNetpbm($fileIn, $fileOut, $scaleParameters, $cropParameters, $quality);
      if (!$result)
      {
        return self::scaleGd($fileIn, $fileOut, $scaleParameters, $cropParameters, $quality);
      }
    }
    else
    {
      return self::scaleGd($fileIn, $fileOut, $scaleParameters, $cropParameters, $quality);
    }
  }

  /**
   * Get the JPEG EXIF rotation. Always returns 1 (no rotation) for other formats.
   * Other values:
   * case 2:  horizontal flip
   * case 3:  180 rotate left
   * case 4:  vertical flip
   * case 5:  vertical flip + 90 rotate right
   * case 6:  90 rotate right
   * case 7:  horizontal flip + 90 rotate right
   * case 8:     90 rotate left
   * @param mixed $file
   * @param mixed $getimagesize
   * @return mixed
   */
  static public function getRotation($file, $getimagesize = null)
  {
    if (is_null($getimagesize))
    {
      $getimagesize = getimagesize($file);
    }
    if ($getimagesize[2] !== IMAGETYPE_JPEG)
    {
      return 1;
    }
    if (!extension_loaded("exif"))
    {
      // We can't tell
      return 1;
    }
    // exif_read_data is noisy if it encounters Adobe XMP instead of EXIF in the app0 marker
    $exif = exif_read_data($file);
    if (!$exif)
    {
      return 1;
    }
    if (isset($exif['IFD0']['Orientation']))
    {
      // Code I'm seeing does this
      $ort = $exif['IFD0']['Orientation'];
    } elseif (isset($exif['Orientation']))
    {
      // Files I'm seeing do this
      $ort = $exif['Orientation'];
    }
    else
    {
      $ort = 1;
    }
    return $ort;
  }

  /**
   * DOCUMENT ME
   * @param mixed $fileIn
   * @param mixed $fileOut
   * @param mixed $scaleParameters
   * @param mixed $cropParameters
   * @param mixed $quality
   * @return mixed
   */
  static private function scaleNetpbm($fileIn, $fileOut, $scaleParameters = array(), $cropParameters = array(), $quality = 75)
  {
    $outputFilters = array(
      "jpg" => "pnmtojpeg --quality %d",
      "jpeg" => "pnmtojpeg --quality %d",
      "ppm" => "cat",
      "pbm" => "cat",
      "pgm" => "cat",
      "tiff" => "pnmtotiff",
      "png" => "pnmtopng",
      "gif" => "ppmquant 256 | ppmtogif",
      "bmp" => "ppmtobmp"
    );
    if (preg_match("/\.(\w+)$/", $fileOut, $matches)) {
      $extension = $matches[1];
      $extension = strtolower($extension);
      if (!isset($outputFilters[$extension])) {
        return false;
      }
      $filter = sprintf($outputFilters[$extension], $quality);
    } else {
      return false;
    }
    $path = sfConfig::get("app_aimageconverter_path", "");
    if (strlen($path)) {
      if (!preg_match("/\/$/", $path)) {
        $path .= "/";
      }
    }
        
    // AUGH: some versions of anytopnm don't have
    // the brains to look at the file signature. We need
    // to be compatible with this brain damage, so pick
    // the right filter based on the results of getimagesize()
    // and punt to anytopnm only if we can't figure it out.
    
    // While we're at it: detect PDF by magic number too,
    // not by extension, that's tacky

    $input = 'anytopnm';
    
    $in = fopen($fileIn, 'r');
    $bytes = fread($in, 4);
    if ($bytes === '%PDF')
    {
      $input = 'gs -sDEVICE=ppm -sOutputFile=- ' .
        ' -dNOPAUSE -dFirstPage=1 -dLastPage=1 -r100 -q -';
    }
    fclose($in);
    
    $info = @getimagesize($fileIn);
    if ($info !== false)
    {
      $type = $info[2];
      if ($type === IMAGETYPE_GIF)
      {
        $input = 'giftopnm';
      } 
      elseif ($type === IMAGETYPE_PNG)
      {
        $input = 'pngtopnm';
      }
      elseif ($type === IMAGETYPE_JPEG)
      {
        $input = 'jpegtopnm';
      }
    }
    
    $rotate = '';
    
    $rotation = aImageConverter::getRotation($fileIn, $info);
    switch ($rotation)
    {
        case 1: // nothing
        $rotate = '';
        break;

        case 2: // horizontal flip
        $rotate = '| pnmflip -leftright ';
        break;

        case 3: // 180 rotate left
        $rotate = '| pnmflip -rotate180 ';
        break;

        case 4: // vertical flip
        $rotate = '| pnmflip -topbottom ';
        break;

        case 5: // vertical flip + 90 rotate right
        $rotate = '| pnmflip -topbottom | pnmflip -cw ';
        break;

        case 6: // 90 rotate right
        $rotate = '| pnmflip -cw ';
        break;

        case 7: // horizontal flip + 90 rotate right
        $rotate = '| pnmflip -leftright | pnmflip -cw ';
        break;

        case 8:    // 90 rotate left
        $rotate = '| pnmflip -ccw ';
        break;
    }
    
  
    $scaleString = '';
    $extraInputFilters = '';
    foreach ($scaleParameters as $key => $values)
    {
      $scaleString .= " -$key ";
      if (is_array($values))
      {
        foreach ($values as $value)
        {
          $value = ceil($value);
          $scaleString .= " $value";
        }
      }
      else
      {
        $values = ceil($values);
        $scaleString .= " $values";
      }
    }
    if (count($cropParameters))
    {
      $extraInputFilters = 'pnmcut ';
      foreach ($cropParameters as $ckey => $cvalue)
      {
        $cvalue = ceil($cvalue);
        $extraInputFilters .= " -$ckey $cvalue";
      }
    }
    
    $cmd = "(PATH=$path:\$PATH; export PATH; $input < " . escapeshellarg($fileIn) . ' ' . $rotate . ' ' . ($extraInputFilters ? "| $extraInputFilters" : "") . " " . ($scaleParameters ? "| pnmscale $scaleString " : "") . "| $filter " .
      "> " . escapeshellarg($fileOut) . " " .
      ") 2> /dev/null";
    // sfContext::getInstance()->getLogger()->info("$cmd");
    system($cmd, $result);
    if ($result != 0) 
    {
      return false;
    }
    return true;
  }

  /**
   * DOCUMENT ME
   * @param mixed $fileIn
   * @param mixed $fileOut
   * @param mixed $scaleParameters
   * @param mixed $cropParameters
   * @param mixed $quality
   * @return mixed
   */
  static private function scaleGd($fileIn, $fileOut, $scaleParameters = array(), $cropParameters = array(), $quality = 75)
  {
    
    // gd version for those who can't install netpbm, poor buggers
    // "handles" PDF by rendering a blank white image. We already superimpose a PDF icon,
    // so this should work well 
    
    // (if you can install ghostview, you can install netpbm too, so there's no middle case)
    
    // Special case to emit the original. This preserves transparency in GIFs and is faster for everything. (PNGs can always preserve 
    // alpha channel in anything under 1024x768 or when gd is the only backend enabled.) WARNING: keep this up to date if new
    // capabilities are added - we need to make sure they are not active etc. before using this trick. TODO: check for this in
    // netpbm land too, right now in a typical configuration it's not checked over 1024x768    
    
    // Default to normal orientation
    $orientation = 1;
    
    $imageInfo = @getimagesize($fileIn);
    // Don't panic on a PDF, fall through to the fake handler for that.
    if ($imageInfo)
    {
      $width = $imageInfo[0];
      $height = $imageInfo[1];
      $orientation = aImageConverter::getRotation($fileIn, $imageInfo);
      if ($imageInfo[2] === IMAGETYPE_JPEG)
      {
        // Some EXIF orientations swap width and height
        switch ($orientation)
        {
          case 5: // vertical flip + 90 rotate right
          case 6: // 90 rotate right
          case 7: // horizontal flip + 90 rotate right
          case 8:    // 90 rotate left
          $tmp = $width;
          $width = $height;
          $height = $tmp;
          break;
        }
      }
      
      $infoIn = pathinfo($fileIn);    
      $infoOut = pathinfo($fileOut);    
      
      // Try not to do any work if we are not changing anything
      if ($orientation == 1)
      {
        if (((!count($scaleParameters)) || (isset($scaleParameters['xysize']) && $scaleParameters['xysize'][0] == $width && $scaleParameters['xysize'][1] == $height)) && (strtolower($infoIn['extension']) === strtolower($infoOut['extension'])) && (!count($cropParameters)))
        {
          copy($fileIn, $fileOut);
          return true;
        }
      }
    }
    
    if (preg_match('/\.pdf$/i', $fileIn))
    {
      $in = self::createTrueColorAlpha(100, 100);
      imagefilledrectangle($in, 0, 0, 100, 100, imagecolorallocate($in, 255, 255, 255));
    } 
    else
    {
      $in = self::imagecreatefromany($fileIn);
      if ($orientation != 1)
      {
        // Note that gd rotation is CCL
        
        switch ($orientation)
        {
          case 2: // horizontal flip
          aImageConverter::horizontalFlip($in);
          break;

          case 3: // 180 rotate left
          $in2 = imagerotate($in, 180, imagecolorallocate($in, 255, 255, 255));
          imagedestroy($in);
          $in = $in2;
          break;

          case 4: // vertical flip
          aImageConverter::verticalFlip($in);
          break;

          case 5: // vertical flip + 90 rotate right
          aImageConverter::verticalFlip($in);
          $in2 = imagerotate($in, 270, imagecolorallocate($in, 255, 255, 255));
          imagedestroy($in);
          $in = $in2;
          break;

          case 6: // 90 rotate right
          $in2 = imagerotate($in, 270, imagecolorallocate($in, 255, 255, 255));
          imagedestroy($in);
          $in = $in2;
          break;

          case 7: // horizontal flip + 90 rotate right
          aImageConverter::horizontalFlip($in);
          $in2 = imagerotate($in, 270, imagecolorallocate($in, 255, 255, 255));
          imagedestroy($in);
          $in = $in2;
          break;

          case 8:    // 90 rotate left
          $in2 = imagerotate($in, 90, imagecolorallocate($in, 255, 255, 255));
          imagedestroy($in);
          $in = $in2;
          break;
        }
      }
    }
    
    if (!$in)
    {
      return false;
    }
    
    if (preg_match("/\.(\w+)$/i", $fileOut, $matches))
    {
      $extension = $matches[1];
      $extension = strtolower($extension);
    }
    else
    {
      imagedestroy($in);
      return false;
    }
    
    $top = 0;
    $left = 0;
    $width = imagesx($in);
    $height = imagesy($in);
    if (count($cropParameters))
    {
      if (isset($cropParameters['top']))
      {
        $top = $cropParameters['top'];
      }
      if (isset($cropParameters['left']))
      {
        $left = $cropParameters['left'];
      }
      if (isset($cropParameters['width']))
      {
        $width = $cropParameters['width'];
      }
      if (isset($cropParameters['height']))
      {
        $height = $cropParameters['height'];
      }
      $cropped = self::createTrueColorAlpha($width, $height);
      imagealphablending($cropped, false);
      imagesavealpha($cropped, true);
      imagecopy($cropped, $in, 0, 0, $left, $top, $width, $height);
      imagedestroy($in);
      $in = null;
    }
    else
    {
      // No cropping, so don't waste time and memory
      $cropped = $in;
      $in = null;
    }
  
    if (count($scaleParameters))
    {
      $width = imagesx($cropped);
      $height = imagesy($cropped);
      $swidth = $width;
      $sheight = $height;
      if (isset($scaleParameters['xsize']))
      {
        $height = $scaleParameters['xsize'] * imagesy($cropped) / imagesx($cropped);
        $width = $scaleParameters['xsize'];
        $out = self::createTrueColorAlpha($width, $height);
        imagecopyresampled($out, $cropped, 0, 0, 0, 0, $width, $height, imagesx($cropped), imagesy($cropped));
        imagedestroy($cropped);
        $cropped = null;
      }
      elseif (isset($scaleParameters['ysize']))
      {
        $width = $scaleParameters['ysize'] * imagesx($cropped) / imagesy($cropped);
        $height = $scaleParameters['ysize'];
        $out = self::createTrueColorAlpha($width, $height);
        imagecopyresampled($out, $cropped, 0, 0, 0, 0, $width, $height, imagesx($cropped), imagesy($cropped));
        imagedestroy($cropped);
        $cropped = null;
      }
      elseif (isset($scaleParameters['scale']))
      {
        $width = imagesx($cropped) * $scaleParameters['scale'];
        $height = imagesy($cropped)* $scaleParameters['scale'];
        $out = self::createTrueColorAlpha($width, $height);
        imagecopyresampled($out, $cropped, 0, 0, 0, 0, $width, $height, imagesx($cropped), imagesy($cropped));
        imagedestroy($cropped);
        $cropped = null;
      }
      elseif (isset($scaleParameters['xysize']))
      {
        $width = $scaleParameters['xysize'][0];
        $height = $scaleParameters['xysize'][1];
        // This was backwards until 05/31/2010, making things bigger rather than smaller if their
        // aspect ratios differed from the original. Be consistent with netpbm which makes things
        // smaller not bigger
        if (($width / $height) > ($swidth / $sheight))
        {
          // Wider than the original. So it will be narrower than requested
          $width = ceil($height * ($swidth / $sheight));
        }
        else
        {
          // Taller than the original. So it will be shorter than requested
          $height = ceil($width * ($sheight / $swidth));
        }
        $out = self::createTrueColorAlpha($width, $height);
        imagecopyresampled($out, $cropped, 0, 0, 0, 0, $width, $height, $swidth, $sheight);
        imagedestroy($cropped);
        $cropped = null;
      }
    }
    else
    {
      // No scaling, don't waste time and memory
      $out = $cropped;
      $cropped = null;
    }
    
    $extension = strtolower($infoOut['extension']);
    if ($extension === 'gif')
    {
      aImageConverter::imagegif($out, $fileOut);
    }
    elseif (($extension === 'jpg') || ($extension === 'jpeg'))
    {
      aImageConverter::imagejpeg($out, $fileOut, $quality);
    }
    elseif ($extension === 'png')
    {
      aImageConverter::imagepng($out, $fileOut);
    }
    else
    {
      return false;
    }
      
    imagedestroy($out);
    $out = null;
    return true;
  }

  /**
   * Stream wrapper safe versions
   */
  static protected function imagegif($im, $file)
  {
    if (preg_match('/^[\w]+:\/\//', $file))
    {
      ob_start();
      imagegif($im);
      file_put_contents($file, ob_get_clean());
    }
    else
    {
      imagegif($im, $file);
    }
  }

  /**
   * Stream wrapper safe versions
   */
  static protected function imagejpeg($im, $file, $quality)
  {
    if (preg_match('/^[\w]+:\/\//', $file))
    {
      ob_start();
      imagejpeg($im, null, $quality);
      file_put_contents($file, ob_get_clean());
    }
    else
    {
      imagejpeg($im, $file, $quality);
    }
  }
  
  /**
   * Stream wrapper safe versions
   */
  static protected function imagepng($im, $file)
  {
    if (preg_match('/^[\w]+:\/\//', $file))
    {
      ob_start();
      imagepng($im);
      file_put_contents($file, ob_get_clean());
    }
    else
    {
      imagepng($im, $file);
    }
  }
  
  /**
   * Flips the image in place
   * @param mixed $in
   */
  static protected function horizontalFlip($in)
  {
    $tmp = self::imageCreateTrueColor(1, $height);
    for ($x = 0; ($x < ($width >> 1)); $x++)
    {
      imagecopy($tmp, $in, 0, 0, $x, 0, 1, $height);
      imagecopy($in, $in, $x, 0, ($width - $x) - 1, 0, 1, $height);
      imagecopy($in, $tmp, ($width - $x) - 1, 0, 0, 0, 1, $height);
    }
    imagedestroy($tmp);
  }

  /**
   * Flips the image in place
   * @param mixed $in
   */
  static protected function verticalFlip($in)
  {
    $tmp = self::imageCreateTrueColor($width, 1);
    for ($y = 0; ($y < ($height >> 1)); $y++)
    {
      imagecopy($tmp, $in, 0, 0, 0, $y, $width, 1);
      imagecopy($in, $in, 0, $y, 0, ($height - $y) - 1, $width, 1);
      imagecopy($in, $tmp, 0, ($height - $y) - 1, 0, 0, $width, 1);
    }
    imagedestroy($tmp);
  }

  /**
   * Make sure the new image is capable of being saved with intact alpha channel;
   * don't composite alpha channel in gd. If a designer uploads an alpha channel image
   * they must have a reason for doing so
   * @param mixed $width
   * @param mixed $height
   * @return mixed
   */
  static public function createTrueColorAlpha($width, $height)
  {
    $im = imagecreatetruecolor($width, $height);
    imagealphablending($im, false);
    imagesavealpha($im, true);
    return $im;
  }

  /**
   * Retrieves what you really want to know about an image file, PDFs included,
   * before making calls such as the above based on good information.
   * Returns as follows:
   * array('format' => 'file extension: gif, jpg, png or pdf', 'width' => width in pixels, 'height' => height in pixels);
   * $format is the recommended file extension based on the actual file type, not the user's (possibly totally false or absent)
   * claimed file extension.
   * If the file does not have a valid header identifying it as one of these types, false is returned.
   * If the 'format-only' option is true, only the format field is returned. This is much faster if the
   * file is a PDF.
   * @param mixed $file
   * @param mixed $options
   * @return mixed
   */
  static public function getInfo($file, $options = array())
  {
    $formatOnly = (isset($options['format-only']) && $options['format-only']);
    $noPdfSize = (isset($options['no-pdf-size']) && $options['no-pdf-size']);
    $result = array();
    $in = fopen($file, "rb");
    $data = fread($in, 4);
    fclose($in);
    
    
    if ($data === '%PDF')
    {
      // format-only 
      if ($formatOnly || (!aImageConverter::supportsInput('pdf')) || ($noPdfSize))
      {
        // All we can do is confirm the format and allow
        // download of the original (which, for PDF, is
        // usually fine)
        return array('format' => 'pdf');
      }
      $result['format'] = 'pdf';
      $path = sfConfig::get("app_aimageconverter_path", "");
      if (strlen($path)) {
        if (!preg_match("/\/$/", $path)) {
          $path .= "/";
        }
      }
      // Bounding box goes to stderr, not stdout! Charming
      // 5 second timeout for reading dimensions. Keeps us from getting stuck on
      // PDFs that just barely work in Adobe but are noncompliant and hang ghostscript.
      // Read the output one line at a time so we can catch the happy
      // bounding box message without hanging
      
      // Problem: this doesn't work. We regain control but the process won't die for some reason. It helps
      // with import but for now go with the simpler standard invocation and hope they fix gs

      // $cmd = "(PATH=$path:\$PATH; export PATH; gs -sDEVICE=bbox -dNOPAUSE -dFirstPage=1 -dLastPage=1 -r100 -q " . escapeshellarg($file) . " -c quit ) 2>&1";
      
      $cmd = "( PATH=$path:\$PATH; export PATH; gs -sDEVICE=bbox -dNOPAUSE -dFirstPage=1 -dLastPage=1 -r100 -q " . escapeshellarg($file) . " -c quit & GS=$!; ( sleep 5; kill \$GS ) & TIMEOUT=\$!; wait \$GS; kill \$TIMEOUT ) 2>&1";

      // For some reason system() does not get the same result when killing subshells as I get when executing
      // $cmd directly. I don't know why this is this the case but it's easily reproduced
      
      $script = aFiles::getTemporaryFilename() . '.sh';
      file_put_contents($script, $cmd);
      $cmd = "/bin/sh " . escapeshellarg($script);
      $in = popen($cmd, "r");
      $data = stream_get_contents($in);
      pclose($in);
      // Actual nonfatal errors in the bbox output mean it's not safe to just
      // read this naively with fscanf, look for the good part
      if (preg_match("/%%BoundingBox: \d+ \d+ (\d+) (\d+)/", $data, $matches))
      {
        $result['width'] = $matches[1];
        $result['height'] = $matches[2];
      }
      if (!isset($result['width']))
      {
        // Bad PDF
        return false;
      }
      return $result;
    }
    else
    {
      $formats = array(
        IMAGETYPE_JPEG => "jpg",
        IMAGETYPE_PNG => "png",
        IMAGETYPE_GIF => "gif"
      );
      $data = @getimagesize($file);
      if (count($data) < 3)
      {
        return false;
      }
      if (!isset($formats[$data[2]]))
      {
        return false;
      }
      $format = $formats[$data[2]];
      $result['format'] = $format;
      if ($formatOnly)
      {
        return $result;
      }
      $result['width'] = $data[0];
      $result['height'] = $data[1];
      if ($format === 'jpg')
      {
        // Some EXIF orientations swap width and height
        switch (aImageConverter::getRotation($file, $data))
        {
          case 5: // vertical flip + 90 rotate right
          case 6: // 90 rotate right
          case 7: // horizontal flip + 90 rotate right
          case 8:    // 90 rotate left
          $result['width'] = $data[1];
          $result['height'] = $data[0];
          break;
        }
      }
      return $result;
    }
  }

  /**
   * Odds and ends missing from gd
   * @param string $filename
   * @return gdImage resource
   */
  static private function imagecreatefromany($filename) 
  {
    // For decent performance, determine the type up front, don't
    // open the file in three different ways until something works
    $info = @getimagesize($filename);
    if ($info !== false)
    {
      $type = $info[2];
      if ($type === IMAGETYPE_GIF)
      {
        $func = 'imagecreatefromgif';
      } 
      elseif ($type === IMAGETYPE_PNG)
      {
        $func = 'imagecreatefrompng';
      }
      elseif ($type === IMAGETYPE_JPEG)
      {
        $func = 'imagecreatefromjpeg';
      }
    }
    if (isset($func))
    {
      return @call_user_func($func, $filename);
    }
    
    // Fallback: types not enumerated. This is slower of course
    foreach (array('bmp', 'ico') as $type) 
    {
      $func = 'imagecreatefrom' . $type;
      if (is_callable($func)) 
      {
        $image = @call_user_func($func, $filename);
        if ($image) return $image;
      }
    }
    return false;
  }

  /**
   * Can this box handle pdf, png, jpeg (also acdepts jpg), gif, bmp, ico...
   * Mainly used to check for PDF support.
   * NOTE: this call is a performance hit, especially with netpbm and ghostscript available.
   * So we cache the result for 5 minutes. Keep that in mind if you make configuration changes, install
   * ghostscript, etc. and don't see an immediate difference.
   * @param mixed $extension
   * @return mixed
   */
  static public function supportsInput($extension)
  {
    $hint = aImageConverter::getHint("input:$extension");
    if (!is_null($hint))
    {
      return $hint;
    }
    
    $result = false;
    if (sfConfig::get('app_aimageconverter_netpbm', true))
    {
      if (aImageConverter::supportsInputNetpbm($extension))
      {
        $result = true;
      }
    }
    if (!$result)
    {
      $result = aImageConverter::supportsInputGd($extension);
    }
    aImageConverter::setHint("input:$extension", $result);
    return $result;
  }

  /**
   * DOCUMENT ME
   * @param mixed $extension
   * @return mixed
   */
  static public function supportsInputNetpbm($extension)
  {
    $types = array('gif' => 'gif', 'png' => 'png', 'jpg' => 'jpeg', 'jpeg' => 'jpeg', 'bmp' => 'bmp', 'ico' => 'ico');
    $path = sfConfig::get("app_aimageconverter_path", "");
    if (strlen($path)) {
      if (!preg_match("/\/$/", $path)) {
        $path .= "/";
      }
    }
    if ($extension === 'pdf')
    {
      // DEPRECATED. GhostScript just isn't reliable enough. It rejects too many valid
      // PDFs which is a much bigger issue than lack of preview. See #558
      if (sfConfig::get('app_a_pdf_preview', false))
      {
        $cmd = 'gs';
      }
      else
      {
        return false;
      }
    }
    elseif (!isset($types[$extension]))
    {
      if (!preg_match('/^\w+$/', $extension))
      {
        return false;
      }
      $cmd = $extension . 'topnm';
    }
    else
    {
      $cmd = $types[$extension] . 'topnm';
    }
    $in = popen("(PATH=$path:\$PATH; export PATH; which $cmd)", "r");
    $result = stream_get_contents($in);
    pclose($in);
    if (strlen($result))
    {
      return true;
    }
    return false;
  }

  /**
   * DOCUMENT ME
   * @param mixed $extension
   * @return mixed
   */
  static public function supportsInputGd($extension)
  {
    $types = array('gif' => 'gif', 'png' => 'png', 'jpg' => 'jpeg', 'jpeg' => 'jpeg', 'bmp' => 'bmp', 'ico' => 'ico');
    if (!isset($types[$extension]))
    {
      return false;
    }
    $f = 'imagecreatefrom' . $types[$extension];
    return is_callable($f);
  }

  /**
   * DOCUMENT ME
   * @param mixed $hint
   * @return mixed
   */
  static public function getHint($hint)
  {
    $cache = aImageConverter::getHintCache();
    $key = 'apostrophe:imageconverter:' . $hint;
    return $cache->get($key, null);
  }

  /**
   * DOCUMENT ME
   * @param mixed $hint
   * @param mixed $value
   */
  static public function setHint($hint, $value)
  {
    $cache = aImageConverter::getHintCache();
    // The lifetime should be short to avoid annoying developers who are
    // trying to fix their configuration and test with new possibilities
    $key = 'apostrophe:imageconverter:' . $hint;
    $cache->set($key, $value, 300);
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function getHintCache()
  {
    return aCacheTools::get('hint');
  }
}
