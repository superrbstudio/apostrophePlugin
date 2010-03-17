<?php

class aMediaVideoEmbedForm extends aMediaVideoForm
{
  public function configure()
  {
    parent::configure();
    unset($this['service_url']);
    // TODO: custom validator looking for appropriate tags only
    $this->setValidator('embed',
      new sfValidatorCallback(
        array('required' => true, 'callback' => 'aMediaVideoEmbedForm::validateEmbed'),
        array('required' => "Not a valid embed code", 'invalid' => "Not a valid embed code")));
    $this->setWidget('thumbnail',
      new aWidgetFormInputFilePersistent());
    $this->setValidator('thumbnail',
      new aValidatorFilePersistent(array('mime_types' =>
        array('image/jpeg', 'image/png', 'image/gif'),
        "required" => (!$this->getObject()->getId()))));
    $this->widgetSchema->setFormFormatterName('aAdmin');  
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
  }
  static public function validateEmbed($validator, $value, $arguments)
  {
    // Don't let this become a way to embed arbitrary HTML
    $value = trim(strip_tags($value, "<embed><object><param><applet>"));
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
    return $value;
  }
  public function updateObject($values = null)
  {
    $object = parent::updateObject($values);
    // If possible, get the width and height from the embed tag rather
    // than from the thumbnail the user uploaded, which is likely to be
    // a mismatch quite often. If the embed tag has percentages we don't
    // want to match, just let the thumbnail dimensions win
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
