<?php
/**
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaButtonForm extends BaseForm
{
  protected $id;
  protected $soptions;

  /**
   * PARAMETERS ARE REQUIRED, no-parameters version is strictly to satisfy i18n-update
   * @param mixed $id
   * @param mixed $soptions
   */
  public function __construct($id = 1, $soptions = array())
  {
    $this->id = $id;
    $this->soptions = $soptions;
    $soptions['class'] = 'aButtonSlot';
    $this->allowedTags = $this->consumeSlotOption('allowed-tags');
    $this->allowedAttributes = $this->consumeSlotOption('allowed-attributes');
    $this->allowedStyles = $this->consumeSlotOption('allowed-styles');
    parent::__construct();
  }

  /**
   * DOCUMENT ME
   * @param mixed $s
   * @return mixed
   */
  protected function consumeSlotOption($s)
  {
    if (isset($this->soptions[$s]))
    {
      $v = $this->soptions[$s];
      unset($this->soptions[$s]);
      return $v;
    }
    else
    {
      return null;
    }
  }

  /**
   * DOCUMENT ME
   */
  public function configure()
  {
    $widgetOptions = array();
     $widgetOptions['tool'] = 'Sidebar';

    $tool = $this->consumeSlotOption('tool');
    if (!is_null($tool))
    {
      $widgetOptions['tool'] = $tool;
    }
    
    $this->setWidgets(array(
      'description' => new aWidgetFormRichTextarea($widgetOptions, $this->soptions),
      'url' => new sfWidgetFormInputText(array(), array('class' => 'aButtonSlot')),
      'title' => new sfWidgetFormInputText(array(), array('class' => 'aButtonSlot'))  
    ));

    $this->setValidators(array(
      'description' => new sfValidatorHtml(array('required' => false, 'allowed_tags' => $this->allowedTags, 'allowed_attributes' => $this->allowedAttributes, 'allowed_styles' => $this->allowedStyles)),
      'url' => new sfValidatorAnd(array(
        // www.foo.bar => http://www.foo.bar
        new sfValidatorCallback(array('callback' => array($this, 'validateLazyUrl'))), 
        // Must be a valid URL to go past this stage
        new sfValidatorCallback(array('callback' => array($this, 'validateUrl'))),
      )),
      'title' => new sfValidatorString(array('required' => false)) 
    ));

    // Ensures unique IDs throughout the page. Hyphen between slot and form to please our CSS
    $this->widgetSchema->setNameFormat('slot-form-' . $this->id . '-%s');
    
    // You don't have to use our form formatter, but it makes things nice
    $this->widgetSchema->setFormFormatterName('aAdmin');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
  }

  /**
   * Add missing http:
   * @param mixed $validator
   * @param mixed $value
   * @return mixed
   */
  public function validateLazyUrl($validator, $value)
  {
    if (preg_match('/^[\w\+-]+\./', $value))
    {
      return 'http://' . $value;
    }
    return $value;
  }

  /**
   * DOCUMENT ME
   * @param mixed $validator
   * @param mixed $value
   * @return mixed
   */
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
