<?php
/**
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aTagForm extends TagForm
{

  /**
   * DOCUMENT ME
   */
  public function setup()
  {
    parent::setup();

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'Tag', 'column' => array('name')))
    );

    $this->useFields(array('name'));
  }

  /**
   * DOCUMENT ME
   * @param mixed $values
   */
  public function updateObject($values = null)
  {
    if (is_null($values))
    {
      $values = $this->getValues();
    }
    // Slashes break routes in most server configs. Do NOT force case of tags.
    
    $values['name'] = str_replace('/', '-', isset($values['name']) ? $values['name'] : '');
    parent::updateObject($values);
  }
}