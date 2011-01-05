<?php

class BaseaSmartSlideshowForm extends BaseForm
{
  protected $id;
  public function __construct($id, $defaults = array(), $options = array(), $CSRFSecret = null)
  {
    $this->id = $id;
    parent::__construct($defaults, $options, $CSRFSecret);
  }
  public function configure()
  {
    $this->widgetSchema['count'] = new sfWidgetFormInput(array(), array('size' => 2, 'default' => 5));
    $this->validatorSchema['count'] = new sfValidatorNumber(array('min' => 1, 'max' => 100));
		$this->widgetSchema->setHelp('count', 'Set the number of images to display – 100 max.');
    if(!$this->hasDefault('count'))
		{
      $this->setDefault('count', 1);
    }

    $this->widgetSchema['categories_list'] =
      new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'aCategory'));
    $this->validatorSchema['categories_list'] =
      new sfValidatorDoctrineChoice(array('model' => 'aCategory', 'multiple' => true, 'required' => false));
		$this->widgetSchema->setHelp('categories_list', 'Filter Images by Category');

    $this->widgetSchema['tags_list']       = new sfWidgetFormInput(array(), array('class' => 'tag-input', 'autocomplete' => 'off'));
    $this->validatorSchema['tags_list']    = new sfValidatorString(array('required' => false));
		$this->widgetSchema->setHelp('tags_list','Filter Images by Tag');

    // Ensures unique IDs throughout the page
    $this->widgetSchema->setNameFormat('slot-form-' . $this->id . '[%s]');

    // You don't have to use our form formatter, but it makes things nice
    $this->widgetSchema->setFormFormatterName('aAdmin');
  }
}
