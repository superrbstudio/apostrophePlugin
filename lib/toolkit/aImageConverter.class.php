<?php

/*
 *
 * Efficient image conversions using netpbm or (if netpbm is not available) gd.
 * For more information see the README file.
 *
 */ 

class aImageConverter 
{
  // Produces images suitable for intentional cropping by CSS.
  // Either the width or the height will match the request; the other
  // will EXCEED the request. Looks nicer than letterboxing in cases
  // where keeping the entire picture is not essential.

  static public function scaleToNarrowerAxis($fileIn, $fileOut, $width, $height, $quality = 75)
  {
    $width = ceil($width);
    $height = ceil($height);
    $quality = ceil($quality);
    list($iwidth, $iheight) = getimagesize($fileIn); 
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

  static public function scaleByFactor($fileIn, $fileOut, $factor, 
    $quality = 75)
  {
    $quality = ceil($quality);
    $scaleParameters = array('scale' => $factor + 0);  
    return self::scaleBody($fileIn, $fileOut, $scaleParameters, array(), $quality);
  }

  static public function cropOriginal($fileIn, $fileOut, $width, $height, $quality = 75)
  {
    $width = ceil($width);
    $height = ceil($height);
    $quality = ceil($quality);
    list($iwidth, $iheight) = getimagesize($fileIn); 
    if (!$iwidth) 
    {
      return false;
    }
    $iratio = $iwidth / $iheight;
    $ratio = $width / $height;

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

  // Change the format without cropping or scaling
  static public function convertFormat($fileIn, $fileOut, $quality = 75)
  {
    $quality = ceil($quality);
    return self::scaleBody($fileIn, $fileOut, false, false, $quality);
  }

  static private function scaleBody($fileIn, $fileOut, $scaleParameters = array(), $cropParameters = array(), $quality = 75) 
  {    
    if (sfConfig::get('app_aimageconverter_netpbm', true))
    {
      // Auto fallback to gd
      $result = self::scaleNetpbm($fileIn, $fileOut, $scaleParameters, $cropParameters, $quality);
      if (!$result)
      {
        sfContext::getInstance()->getLogger()->info("netpbm failed, not available? Trying gd");        
        return self::scaleGd($fileIn, $fileOut, $scaleParameters, $cropParameters, $quality);
      }
    }
    else
    {
      return self::scaleGd($fileIn, $fileOut, $scaleParameters, $cropParameters, $quality);
    }
  }
  
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
    
    $info = getimagesize($fileIn);
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
    
    $cmd = "(PATH=$path:\$PATH; export PATH; $input < " . escapeshellarg($fileIn) . " " . ($extraInputFilters ? "| $extraInputFilters" : "") . " " . ($scaleParameters ? "| pnmscale $scaleString " : "") . "| $filter " .
      "> " . escapeshellarg($fileOut) . " " .
      ") 2> /dev/null";
    sfContext::getInstance()->getLogger()->info("$cmd");
    system($cmd, $result);
    if ($result != 0) 
    {
      return false;
    }
    return true;
  }
  
  static private function scaleGd($fileIn, $fileOut, $scaleParameters = array(), $cropParameters = array(), $quality = 75)
  {
    // gd version for those who can't install netpbm, poor buggers
    // "handles" PDF by rendering a blank white image. We already superimpose a PDF icon,
    // so this should work well 
    
    // (if you can install ghostview, you can install netpbm too, so there's no middle case)
    
    if (preg_match('/\.pdf$/i', $fileIn))
    {
      $in = self::createTrueColorAlpha(100, 100);
      imagefilledrectangle($in, 0, 0, 100, 100, imagecolorallocate($in, 255, 255, 255));
    } 
    else
    {
      $in = self::imagecreatefromany($fileIn);
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
        if (($width / $height) > ($swidth / $sheight))
        {
          // Wider than the original. So it will be shorter than requested
          $height = ceil($width * ($sheight / $swidth));
        }
        else
        {
          // Taller than the original. So it will be narrower than requested
          $width = ceil($height * ($swidth / $sheight));
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

    if (preg_match("/\.(\w+)$/i", $fileOut, $matches))
    {
      $extension = $matches[1];
      $extension = strtolower($extension);
      if ($extension === 'gif')
      {
        imagegif($out, $fileOut);
      }
      elseif (($extension === 'jpg') || ($extension === 'jpeg'))
      {
        imagejpeg($out, $fileOut, $quality);
      }
      elseif ($extension === 'png')
      {
        imagepng($out, $fileOut);
      }
      else
      {
        return false;
      }
    }
    imagedestroy($out);
    $out = null;
    return true;
  }
  
  // Make sure the new image is capable of being saved with intact alpha channel;
  // don't composite alpha channel in gd. If a designer uploads an alpha channel image
  // they must have a reason for doing so
  static public function createTrueColorAlpha($width, $height)
  {
    $im = imagecreatetruecolor($width, $height);
    imagealphablending($im, false);
    imagesavealpha($im, true);
    return $im;
  }
  
  // Retrieves what you really want to know about an image file, PDFs included,
  // before making calls such as the above based on good information.
  
  // Returns as follows:
  
  // array('format' => 'file extension: gif, jpg, png or pdf', 'width' => width in pixels, 'height' => height in pixels);

  // $format is the recommended file extension based on the actual file type, not the user's (possibly totally false or absent)
  // claimed file extension.
  
  // If the file does not have a valid header identifying it as one of these types, false is returned.
  
  static public function getInfo($file)
  {
    $result = array();
    $in = fopen($file, "rb");
    $data = fread($in, 4);
    fclose($in);
    if ($data === '%PDF')
    {
      $result['format'] = 'pdf';
      $path = sfConfig::get("app_aimageconverter_path", "");
      if (strlen($path)) {
        if (!preg_match("/\/$/", $path)) {
          $path .= "/";
        }
      }
      // Bounding box goes to stderr, not stdout! Charming
      $cmd = "(PATH=$path:\$PATH; export PATH; gs -sDEVICE=bbox -dNOPAUSE -dFirstPage=1 -dLastPage=1 -r100 -q " . escapeshellarg($file) . " -c quit) 2>&1";
      sfContext::getInstance()->getLogger()->info("PDFINFO: $cmd");
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
      else
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
      $data = getimagesize($file);
      if (count($data) < 3)
      {
        return false;
      }
      if (!isset($formats[$data[2]]))
      {
        return false;
      }
      $format = $formats[$data[2]];
      $result['width'] = $data[0];
      $result['height'] = $data[1];
      $result['format'] = $format;
      return $result;
    }
  }

  // Odds and ends missing from gd
  
  // As commonly found on the Internets

  static private function imagecreatefromany($filename) 
  {
    foreach (array('png', 'jpeg', 'gif', 'bmp', 'ico') as $type) 
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
}
