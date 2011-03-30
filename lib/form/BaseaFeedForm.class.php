<?php /**
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaFeedForm extends BaseForm
{
  // Ensures unique IDs throughout the page
  protected $id;

  /**
   * PARAMETERS ARE REQUIRED, no-parameters version is strictly to satisfy i18n-update
   * @param mixed $id
   * @param mixed $defaults
   */
  public function __construct($id = 1, $defaults = array())
  {
    $this->id = $id;
    parent::__construct();
    $this->setDefaults($defaults);
  }

  /**
   * DOCUMENT ME
   */
  public function configure()
  {
    $this->setWidgets(array('url' => new sfWidgetFormInputText(array('label' => 'RSS Feed URL'))));
  
    // "And" is correct, these are really progressive filters improving the URL
    
    $this->setValidators(array('url' => new sfValidatorAnd(array(
      // @foo => correct twitter RSS feed URL for that person (requires querying Twitter API)
      new sfValidatorCallback(array('callback' => array($this, 'validateTwitterHandle'))), 
      // www.foo.bar => http://www.foo.bar
      new sfValidatorCallback(array('callback' => array($this, 'validateLazyUrl'))), 
      // Must be a valid URL to go past this stage
      new sfValidatorUrl(array('required' => true, 'max_length' => 1024)), 
      // If the URL is a plain old page get the first RSS feed 'link'ed in it
      new sfValidatorCallback(array('callback' => array($this, 'validateFeed')))))));
    
    // Ensures unique IDs throughout the page
    $this->widgetSchema->setNameFormat('slot-form-' . $this->id . '[%s]');
    $this->widgetSchema->setFormFormatterName('aAdmin');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
  }

  /**
   * Convert Twitter handles to RSS feed URLs. Leave anything else alone
   * @param mixed $validator
   * @param mixed $value
   * @return mixed
   */
  public function validateTwitterHandle($validator, $value)
  {
    if (preg_match('/^@(\w+)$/', $value, $matches))
    {
      $handle = $matches[1];
      $info = json_decode(file_get_contents('http://api.twitter.com/1/users/show.json?' . http_build_query(array('screen_name' => $handle))), true);
      if (isset($info['id']))
      {
        return 'http://twitter.com/statuses/user_timeline/' . $info['id'] . '.rss';
      }
    }
    return $value;
  }

  /**
   * If it smells like HTML and contains a suitable link tag, extract the first feed URL,
   * which is probably what they meant. Otherwise leave it alone
   * @param mixed $validator
   * @param mixed $value
   * @return mixed
   */
  public function validateFeed($validator, $value)
  {
    $content = @file_get_contents($value);
    if ($content)
    {
      $html = new DOMDocument();
      // Incredibly noisy on typical markup
      @$html->loadHTML($content);
      $xpath = new DOMXPath($html);
      $arts = $xpath->query('//link[@rel="alternate" and @type="application/rss+xml"]');
      if (isset($arts->length) && $arts->length)
      {
        return $arts->item(0)->getAttribute('href');
      }
    }
    return $value;
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
}
