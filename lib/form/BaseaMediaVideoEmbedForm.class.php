<?php

// For embedded media for which we do *not* have an aEmbedService implementation

class BaseaMediaVideoEmbedForm extends aMediaVideoForm
{
  public function configure()
  {
    parent::configure();
    
    unset($this['service_url']);
    $this->setValidator('embed',
      new sfValidatorCallback(
        array('required' => true, 'callback' => 'aMediaVideoEmbedForm::validateEmbed'),
        array('required' => "Not a valid embed code", 'invalid' => "Not a valid embed code")));        
  }
  static public function validateEmbed($validator, $value, $arguments)
  {
    // If it is a URL recognized by one of the services we support, use that service
    $service = aMediaTools::getEmbedService($value);
    if ($service)
    {
      $id = $service->getIdForUrl($value);
      return $service->getEmbed($id);
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
      throw new sfValidatorError($validator, $validator->getMessage('invalid'), $arguments);
    }
    
    // If the width or height is not available, we can't process it correctly
    if ((!preg_match("/width\s*=\s*([\"'])(\d+)\\1/i", $value)) || (!preg_match("/height\s*=\s*([\"'])(\d+)\\1/i", $value, $matches)))
    {
      throw new sfValidatorError($validator, $validator->getmessage('invalid'), $arguments);
    }
    
    return $value;
  }
  
  public function updateObject($values = null)
  {
    if (is_null($values))
    {
      $values = $this->getValues();
    }
    $object = parent::updateObject($values);
    // Get the width and height from the embed code
    if (preg_match("/width\s*=\s*([\"'])(\d+)\\1/i", $object->embed, $matches))
    {
      $object->width = $matches[2];
    }
    if (preg_match("/height\s*=\s*([\"'])(\d+)\\1/i", $object->embed, $matches))
    {
      $object->height = $matches[2];
    }
    // Put placeholders in the embed/applet/object tags
    $object->embed = preg_replace(
      array(
        "/width\s*=\s*([\"'])\d+%?\\1/i",
        "/height\s*=\s*([\"'])\d+%?\\1/i",
        "/alt\s*\s*([\"']).*?\\1/i"),
      array(
        "width=\"_WIDTH_\"",
        "height=\"_HEIGHT_\"",
        "alt=\"_TITLE_\""),
      $object->embed);
    return $object;
  }
}
