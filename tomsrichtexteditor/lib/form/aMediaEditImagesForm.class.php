<?php

class aMediaEditImagesForm extends BaseForm
{
  private $active = array();
  
  // PARAMETER IS REQUIRED, accepting null is strictly a workaround so that
  // i18n-update can extract labels from the form
  public function __construct($active = null)
  {
    if (is_null($active))
    {
      $active = array('1');
    }
    $this->active = array_flip($active);
    parent::__construct();
  }
  
  public function configure()
  {
    for ($i = 0; ($i < aMediaTools::getOption('batch_max')); $i++)
    {
      if (isset($this->active[$i]))
      {
        $this->embedForm("item-$i", new aMediaImageForm());
      }
    }
    
    $this->widgetSchema->setNameFormat('a_media_items[%s]'); 
    // $this->widgetSchema->setFormFormatterName('aAdmin');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
  }
  
  // We don't include the form class in the token because we intentionally
  // switch form classes in midstream. You can't learn the session ID from
  // the cookie on your local box, so this is sufficient
  public function getCSRFToken($secret = null)
  {
    if (null === $secret)
    {
      $secret = self::$CSRFSecret;
    }

    return md5($secret.session_id());
  }    
  
}
