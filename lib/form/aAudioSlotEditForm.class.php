<?php /**
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aAudioSlotEditForm extends BaseForm
{
  // Ensures unique IDs throughout the page
  protected $id;

  /**
   * id is really required but forms must accept no args to be compatible with ai18nupdate
   * @param mixed $id
   * @param mixed $defaults
   * @param mixed $options
   * @param mixed $CSRFSecret
   */
  public function __construct($id = null, $defaults = array(), $options = array(), $CSRFSecret = null)
  {
    $this->id = $id;
    parent::__construct($defaults, $options, $CSRFSecret);
  }

  /**
   * DOCUMENT ME
   */
  public function configure()
  {
    // ADD YOUR FIELDS HERE
    
    // A simple example: a slot with a single 'text' field with a maximum length of 100 characters
    $this->setWidgets(array('text' => new sfWidgetFormTextarea()));
    $this->setValidators(array('text' => new sfValidatorString(array('required' => false, 'max_length' => 100))));
    
    // Ensures unique IDs throughout the page. Hyphen between slot and form to please our CSS
    $this->widgetSchema->setNameFormat('slot-form-' . $this->id . '[%s]');
    
    // You don't have to use our form formatter, but it makes things nice
    $this->widgetSchema->setFormFormatterName('aAdmin');
  }
}
