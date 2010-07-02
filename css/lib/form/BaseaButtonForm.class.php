<?php

class BaseaButtonForm extends BaseForm
{
  protected $id;
  // PARAMETERS ARE REQUIRED, no-parameters version is strictly to satisfy i18n-update
  public function __construct($id = 1)
  {
    $this->id = $id;
    parent::__construct();
  }
  
  public function configure()
  {
    $this->setWidgets(array(
      'url' => new sfWidgetFormInputText(array(), array('class' => 'aButtonSlot')),
      'title' => new sfWidgetFormInputText(array(), array('class' => 'aButtonSlot'))
    ));
    $this->setValidators(array(
      'url' => new sfValidatorCallback(array('callback' => array($this, 'validateUrl'))),
      'title' => new sfValidatorString(array('required' => false))
    ));
    $this->widgetSchema->setNameFormat('slotform-' . $this->id . '[%s]');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
  }
  
  public function validateUrl($validator, $value)
  {
    $url = $value;
    // sfValidatorUrl doesn't accept mailto, deal with local URLs at all, etc.
    // Let's take a stab at a more forgiving approach. Also, if the URL
    // begins with the site's prefix, turn it back into a local URL just before
    // save time for better data portability. TODO: let this stew a bit then
    // turn it into a validator and use a form here
    $prefix = sfContext::getInstance()->getRequest()->getUriPrefix();
    if (substr($url, 0, 1) === '/')
    {
      $url = "$prefix$url";
    }
    // Borrowed and extended from sfValidatorUrl
    if (!preg_match(  
      '~^
        (
          (https?|ftps?)://                       # http or ftp (+SSL)
          (
            [\w\-\.]+             # a domain name (tolerate intranet short names)
              |                                   #  or
            \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}    # a IP address
          )
          (:[0-9]+)?                              # a port (optional)
          (/?|/\S+)                               # a /, nothing or a / with something
          |
          mailto:\S+
        )
      $~ix', $url))
    {
      throw new sfValidatorError($validator, 'invalid', array('value' => $url));
    }
    else
    {
      // Convert URLs back to local if they have the site's prefix
      if (substr($url, 0, strlen($prefix)) === $prefix)
      {
        $url = substr($url, strlen($prefix));
      }
    }
    return $url;
  }
}
