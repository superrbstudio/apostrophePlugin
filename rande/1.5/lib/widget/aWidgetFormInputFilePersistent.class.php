<?php

// Copyright 2009 P'unk Ave, LLC. Released under the MIT license.

/**
 * aWidgetFormInputFilePersistent represents an upload HTML input tag
 * that doesn't lose its contents when the form is redisplayed due to 
 * a validation error in an unrelated field. Instead, the previously
 * submitted and successfully validated file is kept in a cache
 * managed on behalf of each user, and automatically reused if the
 * user doesn't choose to upload a new file but rather simply corrects
 * other fields and resubmits.
 */
class aWidgetFormInputFilePersistent extends sfWidgetForm
{
  
  /**
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormInput
   *
   *
   * In reality builds an array of two controls using the [] form field
   * name syntax
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->addOption('type', 'file');
    $this->addOption('existing-html', false);
    // Provides an inline preview. You can also call getPreviewUrl() to get the
    // current preview URL for the image if there is one, which allows you to
    // preview outside the widget
    $this->addOption('image-preview', null);
    $this->addOption('default-preview', null);
    $this->setOption('needs_multipart', true);
  }

  /**
   * @param  string $name        The element name
   * @param  string $value       The value displayed in this widget
   *                             (i.e. the browser-side filename submitted
   *                             on a previous partially successful
   *                             validation of this form)
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */

  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    list($exists, $persistid, $extension) = $this->getExistsPersistidAndExtension($value);
    $result = '';
    $preview = $this->hasOption('image-preview') ? $this->getOption('image-preview') : false;
    
    if ($exists)
    {
      $result .= $this->getOption('existing-html');
    }
    
    if ($preview)
    {
      if (isset($imagePreview['markup']))
      {
        $markup = $imagePreview['markup'];
      }
      else
      {
        $markup = '<img src="%s" />';
      }
      $previewUrl = $this->getPreviewUrl($value, $preview);
      if ($previewUrl !== false)
      {
        $result .= sprintf($markup, $previewUrl);
      }
    }

    return $result .
      $this->renderTag('input',
        array_merge(
          array(
            'type' => $this->getOption('type'),
            'name' => $name . '[newfile]'),
          $attributes)) .
      $this->renderTag('input',
        array(
          'type' => 'hidden',
          'name' => $name . '[persistid]',
          'value' => $persistid));
  }
  
  public function getFormat($value)
  {
    list($exists, $persistid, $extension) = $this->getExistsPersistidAndExtension($value);
    return $extension;
  }
  
  public function getPreviewUrl($value, $imagePreview = array())
  {
    list($exists, $persistid, $extension) = $this->getExistsPersistidAndExtension($value);
    
    // hasOption just verifies that the option is valid, it doesn't check what,
    // if anything, was passed. Thanks to Lucjan Wilczewski 
    $defaultPreview = $this->hasOption('default-preview') ? $this->getOption('default-preview') : false;
    if ($exists)
    {
      $defaultPreview = false;
    }
    if ($exists || $defaultPreview)
    {
      // Note change of key
      $urlStem = sfConfig::get('app_aPersistentFileUpload_preview_url', '/uploads/uploaded_image_preview');
      $urlStem = sfContext::getInstance()->getRequest()->getRelativeUrlRoot() . $urlStem;
      
      // This is the corresponding directory path. You have to override one
      // if you override the other. You override this one by setting
      // app_aToolkit_upload_uploaded_image_preview_dir
      $dir = aFiles::getUploadFolder("uploaded_image_preview");
      // While we're here age off stale previews
      aValidatorFilePersistent::removeOldFiles($dir);
      if ($exists)
      {
        $info = aValidatorFilePersistent::getFileInfo($persistid);
        $source = $info['tmp_name'];
      }
      else
      {
        $source = $defaultPreview;
      }
      $info = aImageConverter::getInfo($source, array('format-only' => true));
      $previewable = false;
      if ($info && in_array($info['format'], array('jpg', 'png', 'gif')))
      {
        $previewable = true;
        $info = aImageConverter::getInfo($source);
      }
      if ($previewable)
      {
        $iwidth = $info['width'];
        $iheight = $info['height'];
        // This is safe - based on sniffed file contents and not a user supplied extension
        $format = $info['format'];
        $dimensions = aDimensions::constrain($iwidth, $iheight, $format, $imagePreview);
        // A simple filename reveals less
        $imagename = "$persistid.$format";
        $url = "$urlStem/$imagename";
        $output = "$dir/$imagename";
        if ((isset($info['newfile']) && $info['newfile']) || (!file_exists($output)))
        {
          if ($imagePreview['resizeType'] === 'c')
          {
            $method = 'cropOriginal';
          }
          else
          {
            $method = 'scaleToFit';
          }
          sfContext::getInstance()->getLogger()->info("YY calling converter method $method width " . $dimensions['width'] . ' height ' . $dimensions['height']);
          aImageConverter::$method(
            $source,
            $output,
            $dimensions['width'],
            $dimensions['height']);
          sfContext::getInstance()->getLogger()->info("YY after converter");
        }
      }
      else
      {
        // Don't try to provide an icon alternative to the preview here,
        // it's better to do that at the project and/or apostrophePlugin level
        // where we can style it better... the less we fake templating inside
        // a widget the better. See getFormat
        $url = false;
      }
      return $url;
    }
    return false;
  }
  
  protected function getExistsPersistidAndExtension($value)
  {
    // TODO: should cache this
    $exists = false;
    $extension = false;
    if (isset($value['persistid']) && strlen($value['persistid']))
    {
      $persistid = $value['persistid'];
      $info = aValidatorFilePersistent::getFileInfo($persistid);
      if ($info)
      {
        $exists = true;
        if (isset($info['extension']))
        {
          $extension = $info['extension'];
        }
      }
    }
    else
    {
      // One implementation, not two (to inevitably drift apart)
      $persistid = aGuid::generate();
    }
    
    if (!$exists)
    {
      $defaultPreview = $this->hasOption('default-preview') ? $this->getOption('default-preview') : false;
      if ($defaultPreview)
      {
        $extension = pathinfo($defaultPreview, PATHINFO_EXTENSION);
      }
    }
    
    return array($exists, $persistid, $extension);
  }
}
