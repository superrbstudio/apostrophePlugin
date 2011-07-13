<?php
/**
 * The one and only video embed form class, handles both media we have a
 * service for and media we embed dumbly
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaMediaVideoForm extends aMediaItemForm
{
  protected $classifyEmbedResult;

  /**
   * DOCUMENT ME
   * @param mixed $item
   */
  public function __construct($item = null)
  {
    parent::__construct($item);
    // If the item already has an embed code classify it
    if ($item && strlen($item->embed))
    {
      $this->classifyEmbed($item->embed);
      // Never make anyone stare at an embed code when we natively support
      // a service. This is crucial in part because we can't give the false
      // impression that they can customize the YouTube embed code 
      // (since we really generate it on the fly).
      if (strlen($item->service_url))
      {
        $this->setDefault('embed', $item->service_url);
      }
    }
    elseif ($item && strlen($item->service_url))
    {
      // Why do we sometimes not have the embed field set at all?
      $this->setDefault('embed', $item->service_url);
    }
  }

  /**
   * Use this to i18n select choices that SHOULD be i18ned. It never gets called,
   * it's just here for our i18n-update task to sniff
   */
  private function i18nDummy()
  {
    __('Public', null, 'apostrophe');
    __('Hidden', null, 'apostrophe');
  }

  /**
   * DOCUMENT ME
   */
  public function configure()
  {
    // This call was missing, preventing easy extension of all media item edit forms at the project level
    parent::configure();
    
    unset($this['id'], $this['type'], $this['slug'], $this['width'], $this['height'], $this['format'], $this['service_url']);
    
    // Slideshare has very long default embed codes. We're not going to alter our database for them, but we do have to
    // let it be initially pasted so we can get past form validation and regenerate a shorter embed code
    $this->getValidator('embed')->setOption('max_length', 2000);
    $object = $this->getObject();
    $this->validatorSchema->setPostValidator(
      new sfValidatorCallback(
        array('required' => true, 'callback' => array($this, 'validateEmbed')),
        array('required' => "Not a valid embed code", 'invalid' => "Not a valid embed code")));        
  
    $this->setWidget('file', new aWidgetFormInputFilePersistent());

    $item = $this->getObject();
    if (!$item->isNew())
    {
      $this->getWidget('file')->setOption('default-preview', $item->getOriginalPath());
    }

    $this->setValidator('file', new aValidatorFilePersistent(array(
      'mime_types' => array('image/jpeg', 'image/png', 'image/gif'), 
      'required' => false
    ), array(
      'mime_types' => 'JPEG, PNG and GIF only.',
      'required' => 'Select a JPEG, PNG or GIF file as a thumbnail')
    ));
    
    $label = 'Replace Thumbnail';
    if (!$this['file']->getWidget()->getOption('default-preview'))
    {
      $label = 'Choose Thumbnail';
    }
    $this->widgetSchema->setLabel('file', $label);
    $this->widgetSchema->setLabel('embed', 'Embed Code or URL');
    $this->widgetSchema->setFormFormatterName('aAdmin');  
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
  }

  /**
   * 
   * preValidateEmbed
   * @param $value
   * @return array
   * @author Thomas Boutell
   * /
   */
  public function classifyEmbed($value)
  {
    // If it is a URL or embed code recognized by one of the services we support, use that service
    $service = aMediaTools::getEmbedService($value);
    if ($service)
    {
      $id = $service->getIdFromUrl($value);
      if (!$id)
      {
        // Not every service considers URLs and embed codes interchangeable
        $id = $service->getIdFromEmbed($value);
      }
      $serviceInfo = $service->getInfo($id);
      if (!$serviceInfo)
      {
        $this->classifyEmbedResult = array('ok' => false, 'error' => 'That video does not exist or you cannot access it.');
        return $this->classifyEmbedResult;
      }
      $thumbnail = $service->getThumbnail($id);
      $info = aImageConverter::getInfo($thumbnail);
      if (!isset($info['width']))
      {
        $this->classifyEmbedResult = array('ok' => false, 'error' => 'That video exists but the service provider does not allow you to embed it.');
        return $this->classifyEmbedResult;
      }
      $this->classifyEmbedResult = array('ok' => true, 'thumbnail' => $thumbnail, 'serviceInfo' => $serviceInfo, 'embed' => $service->embed($id, $info['width'], $info['height']), 'width' => $info['width'], 'height' => $info['height'], 'format' => $info['format'], 'serviceUrl' => $service->getUrlFromId($id));
      return $this->classifyEmbedResult;
    }
    // Don't let this become a way to embed arbitrary HTML
    $value = trim(strip_tags($value, "<embed><object><param><applet><iframe>"));
    // Kill any text outside of tags
    if (preg_match_all("/<.*?>/", $value, $matches))
    {
      $value = implode("", $matches[0]);
    }
    else
    {
      $value = '';
    }
    if (!strlen($value))
    {
      $this->classifyEmbedResult = array('ok' => false, 'error' => 'A valid embed code or recognized media service URL is required.');
      return $this->classifyEmbedResult;
    }
    
    // For existing objects the embed code will already be parameterized, in that situation
    // we don't try (and fail) to extract the dimensions again
    if (strpos($value, '_WIDTH_') === false)
    {
      // If the width or height is not available, we can't process it correctly
      if ((!preg_match("/width\s*=\s*([\"'])(\d+)\\1/i", $value)) || (!preg_match("/height\s*=\s*([\"'])(\d+)\\1/i", $value, $matches)))
      {
        $this->classifyEmbedResult = array('ok' => false, 'error' => 'No width and height in embed code');
        return $this->classifyEmbedResult;
      }
      if (preg_match("/width\s*=\s*([\"'])(\d+)\\1/i", $value, $matches))
      {
        $result['width'] = $matches[2];
      }
      if (preg_match("/height\s*=\s*([\"'])(\d+)\\1/i", $value, $matches))
      {
        $result['height'] = $matches[2];
      }
    }
    else
    {
      $result['width'] = $this->getObject()->width;
      $result['height'] = $this->getObject()->height;
    }
    
    // Put placeholders in the embed/applet/object tags
    $value = preg_replace(
      array(
        "/width\s*=\s*([\"'])\d+%?\\1/i",
        "/height\s*=\s*([\"'])\d+%?\\1/i",
        "/alt\s*\s*([\"']).*?\\1/i"),
      array(
        "width=\"_WIDTH_\"",
        "height=\"_HEIGHT_\"",
        "alt=\"_TITLE_\""),
      $value);
    
    $result['ok'] = true;
    $result['embed'] = $value;
    $this->classifyEmbedResult = $result;
    return $this->classifyEmbedResult;
  }

  /**
   * DOCUMENT ME
   * @param mixed $validator
   * @param mixed $values
   * @return mixed
   */
  public function validateEmbed($validator, $values)
  {
    // (Re)classify the (potentially new or modified) embed code or URL
    $this->classifyEmbed($values['embed']);
    if (!$this->classifyEmbedResult['ok'])
    {
      throw new sfValidatorErrorSchema($validator, array('embed' => new sfValidatorError($validator, $this->classifyEmbedResult['error'])));
    }
    $values['embed'] = $this->classifyEmbedResult['embed'];
    // We need to update the video-related fields even if other fields, like title,
    // don't validate yet so that we can display the correct preview
    $this->videoUpdateObject($values);
    return $values;
  }

  /**
   * DOCUMENT ME
   * @param mixed $values
   */
  public function updateObject($values = null)
  {
    $object = parent::updateObject($values);
    $this->videoUpdateObject($values);
  }

  /**
   * DOCUMENT ME
   * @param mixed $values
   * @return mixed
   */
  public function videoUpdateObject($values)
  {
    $object = $this->getObject();
    // TODO: scan types for services etc. don't assume.
    // But then we'd also have to prompt, if it's a nonnative embed
    $object->type = 'video';
    
    if (isset($this->classifyEmbedResult['serviceUrl']))
    {
      $object->service_url = $this->classifyEmbedResult['serviceUrl'];
      $object->width = $this->classifyEmbedResult['width'];
      $object->height = $this->classifyEmbedResult['height'];
      $object->format = $this->classifyEmbedResult['format'];
    }
    else
    {
      // Don't let a new nonnative embed code be overshadowed by
      // an old service URL
      $object->service_url = null;
      $object->embed = $this->classifyEmbedResult['embed'];
    }
    $object->width = $this->classifyEmbedResult['width'];
    $object->height = $this->classifyEmbedResult['height'];
    return $object;
  }
}
