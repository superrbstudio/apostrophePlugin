<?php
/**
 * 
 * PluginaCategory form.
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfDoctrineFormPluginTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
abstract class PluginaCategoryForm extends BaseaCategoryForm
{

  /**
   * DOCUMENT ME
   * @return mixed
   */
  protected function getUseFields()
  {
    $useFields = array('name');
    $useFields[] = 'groups_list';
    $useFields[] = 'users_list';
    return $useFields;
  }

  /**
   * DOCUMENT ME
   */
  public function setup()
  {
    parent::setup();    
    $this->useFields($this->getUseFields());
    $q = Doctrine::getTable('sfGuardUser')->createQuery();
    // You must be a member of the editor group to potentially post in a category.
    // Listing all users produces an unmanageable dropdown
    $candidateGroup = sfConfig::get('app_a_edit_candidate_group', false);
    if ($candidateGroup)
    {
      $q->innerJoin('sfGuardUser.Groups g')->addWhere('g.name = ?', array($candidateGroup));
    }
    $this->setWidget('users_list',
      new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardUser', 'query' => $q)));
    $this->setValidator('users_list',
      new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardUser', 'query' => $q, 'required' => false)));
  }
}
