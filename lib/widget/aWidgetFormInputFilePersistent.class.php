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
    $exists = false;
    if (isset($value['persistid']) && strlen($value['persistid']))
    {
      $persistid = $value['persistid'];
      $info = aValidatorFilePersistent::getFileInfo($persistid);
      if ($info)
      {
        $exists = true;
      }
    }
    else
    {
      // One implementation, not two (to inevitably drift apart)
      $persistid = aGuid::generate();
    }
    $result = '';
    // hasOption just verifies that the option is valid, it doesn't check what,
    // if anything, was passed. Thanks to Lucjan Wilczewski 
    $preview = $this->hasOption('image-preview') ? $this->getOption('image-preview') : false;
    $defaultPreview = $this->hasOption('default-preview') ? $this->getOption('default-preview') : false;
    if ($exists)
    {
      $defaultPreview = false;
    }
    if ($exists || $defaultPreview)
    {
      if ($preview)
      {
        // Note change of key
        $urlStem = sfConfig::get('app_aPersistentFileUpload_preview_url', '/uploads/uploaded_image_preview');
        // This is the corresponding directory path. You have to override one
        // if you override the other. You override this one by setting
        // app_aToolkit_upload_uploaded_image_preview_dir
        $dir = aFiles::getUploadFolder("uploaded_image_preview");
        // While we're here age off stale previews
        aValidatorFilePersistent::removeOldFiles($dir);
        $imagePreview = $this->getOption('image-preview');
        if ($exists)
        {
          $source = $info['tmp_name'];
        }
        else
        {
          $source = $defaultPreview;
        }
        $info = aImageConverter::getInfo($source);
        if ($info)
        {
          $iwidth = $info['width'];
          $height = $info['height'];
          // This is safe - based on sniffed file contents and not a user supplied extension
          $format = $info['format'];
          list($iwidth, $iheight) = getimagesize($source);
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
        if (isset($imagePreview['markup']))
        {
          $markup = $imagePreview['markup'];
        }
        else
        {
          $markup = '<img src="%s" />';
        }
        $result .= sprintf($markup, $url);
      }
      $result .= $this->getOption('existing-html');
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

}
