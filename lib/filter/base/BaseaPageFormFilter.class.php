<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/base/BaseFormFilterPropel.class.php');

/**
 * aPage filter form base class.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage filter
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfPropelFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BaseaPageFormFilter extends BaseFormFilterPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'tree_left'        => new sfWidgetFormFilterInput(),
      'tree_right'       => new sfWidgetFormFilterInput(),
      'slug'             => new sfWidgetFormFilterInput(),
      'template'         => new sfWidgetFormFilterInput(),
      'is_published'     => new sfWidgetFormChoice(array('choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no'))),
      'view_is_secure'   => new sfWidgetFormChoice(array('choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no'))),
      'view_credentials' => new sfWidgetFormFilterInput(),
      'edit_credentials' => new sfWidgetFormFilterInput(),
      'created_at'       => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => true)),
      'updated_at'       => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => true)),
    ));

    $this->setValidators(array(
      'tree_left'        => new sfValidatorInteger(array('required' => false)),
      'tree_right'       => new sfValidatorInteger(array('required' => false)),
      'slug'             => new sfValidatorPass(array('required' => false)),
      'template'         => new sfValidatorPass(array('required' => false)),
      'is_published'     => new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0))),
      'view_is_secure'   => new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0))),
      'view_credentials' => new sfValidatorPass(array('required' => false)),
      'edit_credentials' => new sfValidatorPass(array('required' => false)),
      'created_at'       => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
      'updated_at'       => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
    ));

    $this->widgetSchema->setNameFormat('a_page_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'aPage';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Text',
      'tree_left'        => 'Text',
      'tree_right'       => 'Text',
      'slug'             => 'Text',
      'template'         => 'Text',
      'is_published'     => 'Boolean',
      'view_is_secure'   => 'Boolean',
      'view_credentials' => 'Text',
      'edit_credentials' => 'Text',
      'version'          => 'Text',
      'created_at'       => 'Date',
      'updated_at'       => 'Date',
    );
  }
}
