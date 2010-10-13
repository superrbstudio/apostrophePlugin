<?php

class BaseaSlideshowForm extends BaseForm
{
  protected $id;
  public function __construct($id, $defaults = array(), $options = array(), $CSRFSecret = null)
  {
    $this->id = $id;
    parent::__construct($defaults, $options, $CSRFSecret);
  }
  public function configure()
  {
    // ADD YOUR FIELDS HERE
    
    $types = array('selected' => 'Selected Images', 'tagged' => 'Images by Tag and Category');
    $this->widgetSchema['type'] = new sfWidgetFormChoice(array('choices' => $types, 'expanded' => true));
    $this->validatorSchema['type'] = new sfValidatorChoice(array('choices' => array_keys($types)));

    $this->widgetSchema['count'] = new sfWidgetFormInput(array(), array('size' => 2));
    $this->validatorSchema['count'] = new sfValidatorNumber(array('min' => 1, 'max' => 100));
		$this->widgetSchema->setHelp('count', '<span class="a-help-arrow"></span> Set the number of images to display – 100 max.');
    if(!$this->hasDefault('type'))
		{
      $this->setDefault('type', 'selected');
    }
    if(!$this->hasDefault('count'))
		{
      $this->setDefault('count', 1);
    }

    $this->widgetSchema['categories_list'] =
      new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'aCategory'));
    $this->validatorSchema['categories_list'] =
      new sfValidatorDoctrineChoice(array('model' => 'aCategory', 'multiple' => true, 'required' => false));
		$this->widgetSchema->setHelp('categories_list', '<span class="a-help-arrow"></span> Filter Images by Category');

    $this->widgetSchema['tags_list']       = new sfWidgetFormInput(array(), array('class' => 'tag-input', 'autocomplete' => 'off'));
    $this->validatorSchema['tags_list']    = new sfValidatorString(array('required' => false));
		$this->widgetSchema->setHelp('tags_list','<span class="a-help-arrow"></span> Filter Images by Tag');

    // Ensures unique IDs throughout the page
    $this->widgetSchema->setNameFormat('slot-form-' . $this->id . '[%s]');

    // You don't have to use our form formatter, but it makes things nice
    $this->widgetSchema->setFormFormatterName('aAdmin');
  }
}
